<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Election\Election;
use App\Models\Voting\VoteToken;
use App\Enums\Auth\UserStatus;
use App\Enums\Election\ElectionStatus;
use App\Services\TokenManagement\TokenManagementService;
use App\Exceptions\TokenValidationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class VoterAccreditation extends Component
{
    use WithPagination;

    public $selectedElection = null;
    public $search = '';
    public $statusFilter = 'all';
    public $selectedUsers = [];
    public $selectAll = false;
    public $showReassignModal = false;
    public $reassignTokenId = null;
    public $newUserId = '';
    public $showIssueModal = false;
    public $selectedUserId = null;
    public $viewMode = 'all'; // all, approved, accredited, eligible, tokens
    
    protected $queryString = ['search', 'statusFilter', 'viewMode'];
    
    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
        $this->resetPage();
    }

    protected $rules = [
        'selectedElection' => 'required|exists:elections,id'
    ];

    public function mount()
    {
        $this->selectedElection = Election::where('status', ElectionStatus::UPCOMING->value)->first()?->id;
    }

    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedUsers = $this->getEligibleUsers()->pluck('id')->toArray();
        } else {
            $this->selectedUsers = [];
        }
    }

    public function openIssueModal($userId)
    {
        $this->selectedUserId = $userId;
        $this->showIssueModal = true;
    }

    public function accreditSingleUser()
    {
        try {
            if (!$this->selectedElection) {
                session()->flash('error', 'Please select an election first.');
                return;
            }

            $user = User::findOrFail($this->selectedUserId);
            $election = Election::findOrFail($this->selectedElection);
            
            $result = app(TokenManagementService::class)->issueToken(
                $user, 
                $election, 
                auth('admin')->user()
            );
            
            $this->showIssueModal = false;
            session()->flash('success', "Token issued successfully for {$user->full_name}.");
            
        } catch (TokenValidationException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Token issuance failed', ['error' => $e->getMessage(), 'user_id' => $this->selectedUserId]);
            session()->flash('error', 'System error occurred. Please try again.');
        }
    }
    
    public function revokeToken($tokenId, $reason = 'Administrative action')
    {
        try {
            $token = VoteToken::findOrFail($tokenId);
            
            app(TokenManagementService::class)->revokeToken(
                $token,
                auth('admin')->user(),
                $reason
            );
            
            session()->flash('success', 'Token revoked successfully.');
            
        } catch (TokenValidationException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Token revocation failed', ['error' => $e->getMessage(), 'token_id' => $tokenId]);
            session()->flash('error', 'System error occurred. Please try again.');
        }
    }
    
    public function reissueToken($tokenId)
    {
        try {
            $token = VoteToken::findOrFail($tokenId);
            
            $token->update([
                'token_hash' => VoteToken::generateSecureTokenHash($token->user, $token->election),
                'is_used' => false
            ]);
            
            session()->flash('success', 'Token reissued successfully.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to reissue token: ' . $e->getMessage());
        }
    }
    
    public function resetUsedToken($tokenId)
    {
        try {
            $token = VoteToken::findOrFail($tokenId);
            
            $token->update(['is_used' => false]);
            
            session()->flash('success', 'Token reset successfully.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to reset token: ' . $e->getMessage());
        }
    }
    
    public function reassignToken($tokenId, $newUserId, $reason = 'Administrative reassignment')
    {
        try {
            $token = VoteToken::findOrFail($tokenId);
            $newUser = User::findOrFail($newUserId);
            
            app(TokenManagementService::class)->reassignToken(
                $token,
                $newUser,
                auth('admin')->user(),
                $reason
            );
            
            session()->flash('success', 'Token reassigned successfully.');
            
        } catch (TokenValidationException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Token reassignment failed', ['error' => $e->getMessage(), 'token_id' => $tokenId]);
            session()->flash('error', 'System error occurred. Please try again.');
        }
    }

    public function bulkAccreditUsers()
    {
        if (empty($this->selectedUsers)) {
            session()->flash('error', 'Please select users to accredit.');
            return;
        }

        if (!$this->selectedElection) {
            session()->flash('error', 'Please select an election first.');
            return;
        }

        try {
            $election = Election::findOrFail($this->selectedElection);
            
            app(TokenManagementService::class)->bulkIssueTokens(
                $this->selectedUsers,
                $election,
                auth('admin')->user()
            );
            
            session()->flash('success', 'Bulk token issuance queued successfully. You will be notified when complete.');
            $this->selectedUsers = [];
            $this->selectAll = false;
            
        } catch (\Exception $e) {
            Log::error('Bulk token issuance failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to queue bulk operation. Please try again.');
        }
    }

    private function canAccreditUser(User $user, Election $election): bool
    {
        \Log::info('Checking user eligibility', [
            'user_status' => $user->status,
            'user_status_value' => $user->status->value,
            'expected_status' => UserStatus::APPROVED,
            'expected_status_value' => UserStatus::APPROVED->value,
            'status_match' => $user->status === UserStatus::APPROVED,
            'has_verified_docs' => $user->hasVerifiedDocuments(),
            'election_status' => $election->status,
            'election_status_value' => $election->status->value,
            'expected_election_status' => ElectionStatus::UPCOMING->value,
            'election_status_match' => $election->status === ElectionStatus::UPCOMING->value
        ]);
        
        // Must be approved with verified KYC
        if ($user->status !== UserStatus::APPROVED || !$user->hasVerifiedDocuments()) {
            \Log::warning('User failed status/docs check');
            return false;
        }

        // Election must be upcoming (not started/ended)
        if ($election->status !== ElectionStatus::UPCOMING) {
            \Log::warning('Election status check failed');
            return false;
        }

        \Log::info('User is eligible');
        return true;
    }

    private function getEligibleUsers()
    {
        $query = User::with(['voteTokens' => function($q) {
                $q->where('election_id', $this->selectedElection);
            }]);

        // Filter by view mode
        switch ($this->viewMode) {
            case 'approved':
                $query->where('status', UserStatus::APPROVED);
                break;
            case 'accredited':
                $query->where('status', UserStatus::ACCREDITED);
                break;
            case 'eligible':
                $query->whereIn('status', [UserStatus::APPROVED, UserStatus::ACCREDITED])
                      ->whereHas('idDocuments', function($q) {
                          $q->where('status', 'approved');
                      })
                      ->whereDoesntHave('voteTokens', function($q) {
                          $q->where('election_id', $this->selectedElection);
                      });
                break;
            case 'tokens':
                $query->whereHas('voteTokens', function($q) {
                    $q->where('election_id', $this->selectedElection);
                });
                break;
            default:
                $query->whereIn('status', [UserStatus::APPROVED, UserStatus::ACCREDITED]);
        }

        // Add document verification for non-token views
        if ($this->viewMode !== 'tokens') {
            $query->whereHas('idDocuments', function($q) {
                $q->where('status', 'approved');
            });
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('first_name')->paginate(25);
    }

    private function calculateMetrics()
    {
        if (!$this->selectedElection) {
            return [
                'daily_issuances' => 0,
                'daily_revocations' => 0,
                'active_tokens_percent' => 0,
                'accreditation_rate' => 0
            ];
        }

        $totalEligible = User::whereIn('status', [UserStatus::APPROVED, UserStatus::ACCREDITED])
            ->whereHas('idDocuments', function($q) {
                $q->where('status', 'approved');
            })->count();

        $totalTokens = VoteToken::where('election_id', $this->selectedElection)->count();
        $activeTokens = VoteToken::where('election_id', $this->selectedElection)
            ->where('is_used', false)->count();

        $dailyIssuances = VoteToken::where('election_id', $this->selectedElection)
            ->whereDate('created_at', today())->count();

        $dailyRevocations = VoteToken::where('election_id', $this->selectedElection)
            ->whereDate('updated_at', today())
            ->where('is_used', true)->count();

        return [
            'daily_issuances' => $dailyIssuances,
            'daily_revocations' => $dailyRevocations,
            'active_tokens_percent' => $totalEligible > 0 ? round(($activeTokens / $totalEligible) * 100, 1) : 0,
            'accreditation_rate' => $totalEligible > 0 ? round(($totalTokens / $totalEligible) * 100, 1) : 0
        ];
    }

    public function render()
    {
        $elections = Election::where('status', ElectionStatus::UPCOMING->value)->get();
        $users = $this->getEligibleUsers();
        

        $stats = [
            'total_approved' => User::where('status', UserStatus::APPROVED)
                ->whereHas('idDocuments', function($q) {
                    $q->where('status', 'approved');
                })->count(),
            'total_accredited' => User::where('status', UserStatus::ACCREDITED)
                ->whereHas('idDocuments', function($q) {
                    $q->where('status', 'approved');
                })->count(),
            'tokens_for_election' => $this->selectedElection ? 
                VoteToken::where('election_id', $this->selectedElection)->count() : 0,
            'eligible_users' => $this->selectedElection ? 
                User::whereIn('status', [UserStatus::APPROVED, UserStatus::ACCREDITED])
                    ->whereHas('idDocuments', function($q) {
                        $q->where('status', 'approved');
                    })
                    ->whereDoesntHave('voteTokens', function($q) {
                        $q->where('election_id', $this->selectedElection);
                    })->count() : 0,
            'metrics' => $this->selectedElection ? $this->calculateMetrics() : []
        ];

        return view('livewire.admin.voter-accreditation', compact('elections', 'users', 'stats'));
    }
}