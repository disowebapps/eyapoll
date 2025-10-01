<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;
use App\Models\Voting\VoteToken;
use App\Models\User;
use App\Models\Election\Election;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use App\Services\Monitoring\MetricsService;

class VoterAccreditation extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    protected MetricsService $metrics;

    public $search = '';
    public $selectedElection = '';
    public $selectedToken = null;
    public $newUserId = '';
    public $confirmingRevoke = false;
    public $confirmingReissue = false;
    public $confirmingReassign = false;
    public $selectedUsers = [];
    public $selectAll = false;

    protected $listeners = ['refreshTokens' => '$refresh'];

    public function mount()
    {
        $this->selectedElection = request()->get('election_id', '');
        $this->metrics = new MetricsService();
    }

    public function getUsersProperty()
    {
        return User::query()
            ->when($this->search, fn($query) => 
                $query->where(function($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                })
            )
            ->whereDoesntHave('voteTokens', function($query) {
                $query->where('election_id', $this->selectedElection)
                      ->where(function($q) {
                          $q->where('is_used', false)
                            ->where('is_revoked', false);
                      });
            })
            ->with(['voteTokens' => function($query) {
                $query->where('election_id', $this->selectedElection);
            }])
            ->paginate(10);
    }

    public function confirmRevoke($tokenId)
    {
        $this->selectedToken = VoteToken::findOrFail($tokenId);
        $this->confirmingRevoke = true;
    }

    public function revokeToken()
    {
        if (!$this->selectedToken) return;

        $this->selectedToken->update([
            'is_revoked' => true,
            'revoked_at' => now(),
            'revoked_by' => auth()->id()
        ]);

        // Record metrics and security event
        $this->metrics->recordMetric('token_revocations', 1, null, [
            'election_id' => $this->selectedToken->election_id,
            'user_id' => $this->selectedToken->user_id
        ]);
        
        $this->metrics->logSecurityEvent(
            'token_revocation',
            'info',
            'Vote token revoked for user ' . $this->selectedToken->user_id,
            ['election_id' => $this->selectedToken->election_id]
        );

        $this->confirmingRevoke = false;
        $this->selectedToken = null;
        $this->emit('refreshTokens');
        session()->flash('message', 'Token has been revoked successfully.');
    }

    public function confirmReissue($tokenId)
    {
        $this->selectedToken = VoteToken::findOrFail($tokenId);
        $this->confirmingReissue = true;
    }

    public function reissueToken()
    {
        if (!$this->selectedToken) return;

        // Create new token
        VoteToken::create([
            'user_id' => $this->selectedToken->user_id,
            'election_id' => $this->selectedToken->election_id,
            'token_hash' => Str::random(64),
            'issued_by' => auth()->id(),
            'issued_at' => now()
        ]);

        // Revoke old token
        $this->selectedToken->update([
            'is_revoked' => true,
            'revoked_at' => now(),
            'revoked_by' => auth()->id()
        ]);

        $this->confirmingReissue = false;
        $this->selectedToken = null;
        $this->emit('refreshTokens');
        session()->flash('message', 'Token has been reissued successfully.');
    }

    public function confirmReassign($tokenId)
    {
        $this->selectedToken = VoteToken::findOrFail($tokenId);
        $this->confirmingReassign = true;
    }

    public function reassignToken()
    {
        if (!$this->selectedToken || !$this->newUserId) return;

        $newUser = User::findOrFail($this->newUserId);

        $this->selectedToken->update([
            'user_id' => $newUser->id,
            'reassigned_at' => now(),
            'reassigned_by' => auth()->id()
        ]);

        $this->confirmingReassign = false;
        $this->selectedToken = null;
        $this->newUserId = '';
        $this->emit('refreshTokens');
        session()->flash('message', 'Token has been reassigned successfully.');
    }

    public function getElectionsProperty()
    {
        return Election::where('status', 'active')->get();
    }

    public function accreditSingleUser($userId)
    {
        $user = User::findOrFail($userId);
        $this->issueToken($user);
        session()->flash('message', "Token issued successfully for {$user->full_name}");
    }

    public function bulkAccreditUsers()
    {
        if (empty($this->selectedUsers)) {
            session()->flash('error', 'No users selected');
            return;
        }

        $users = User::whereIn('id', $this->selectedUsers)->get();
        foreach ($users as $user) {
            $this->issueToken($user);
        }

        $this->selectedUsers = [];
        $this->selectAll = false;
        session()->flash('message', count($users) . ' users accredited successfully');
    }

    protected function issueToken($user)
    {
        VoteToken::create([
            'user_id' => $user->id,
            'election_id' => $this->selectedElection,
            'token_hash' => Str::random(64),
            'issued_by' => auth()->id(),
            'issued_at' => now()
        ]);

        // Record metrics and security event
        $this->metrics->recordMetric('token_issuances', 1, null, [
            'election_id' => $this->selectedElection,
            'user_id' => $user->id
        ]);

        $this->metrics->logSecurityEvent(
            'token_issuance',
            'info',
            'Vote token issued for user ' . $user->id,
            ['election_id' => $this->selectedElection]
        );
    }

    public function resetUsedToken($tokenId)
    {
        $token = VoteToken::findOrFail($tokenId);
        $token->update([
            'is_used' => false,
            'used_at' => null,
            'vote_receipt_hash' => null
        ]);

        session()->flash('message', 'Token reset successfully');
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedUsers = $this->users->pluck('id')->map(fn($id) => (string) $id);
        } else {
            $this->selectedUsers = [];
        }
    }

    public function getStatsProperty()
    {
        if (!$this->selectedElection) {
            return [
                'total_approved' => 0,
                'total_accredited' => 0,
                'eligible_users' => 0,
                'tokens_for_election' => 0,
                'metrics' => []
            ];
        }

        $election = Election::find($this->selectedElection);
        
        // Get token metrics for today
        $dailyIssuances = $this->metrics->getDailyAverage('token_issuances', now());
        $dailyRevocations = $this->metrics->getDailyAverage('token_revocations', now());

        $baseStats = [
            'total_approved' => User::count(),
            'total_accredited' => VoteToken::where('election_id', $this->selectedElection)->count(),
            'eligible_users' => $election ? $election->eligible_voters_count : 0,
            'tokens_for_election' => VoteToken::where('election_id', $this->selectedElection)
                ->where('is_used', false)
                ->where('is_revoked', false)
                ->count()
        ];

        // Add detailed metrics
        return array_merge($baseStats, [
            'metrics' => [
                'daily_issuances' => $dailyIssuances ?? 0,
                'daily_revocations' => $dailyRevocations ?? 0,
                'active_tokens_percent' => $baseStats['eligible_users'] > 0 
                    ? round(($baseStats['tokens_for_election'] / $baseStats['eligible_users']) * 100, 2)
                    : 0,
                'accreditation_rate' => $baseStats['eligible_users'] > 0
                    ? round(($baseStats['total_accredited'] / $baseStats['eligible_users']) * 100, 2)
                    : 0
            ]
        ]);
    }

    public function render()
    {
        return view('livewire.admin.voter-accreditation', [
            'users' => $this->users,
            'elections' => $this->elections,
            'stats' => $this->stats
        ]);
    }
}