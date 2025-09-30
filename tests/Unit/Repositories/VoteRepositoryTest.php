<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\VoteRepository;
use App\Models\Voting\VoteRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VoteRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private VoteRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new VoteRepository(new VoteRecord());
    }

    public function test_can_create_vote_record()
    {
        $data = [
            'voter_hash' => 'test_hash_123',
            'election_id' => 1,
            'encrypted_selections' => encrypt(['position_1' => [1]]),
            'receipt_hash' => 'receipt_123',
            'cast_at' => now()
        ];

        $vote = $this->repository->create($data);

        $this->assertInstanceOf(VoteRecord::class, $vote);
        $this->assertEquals('test_hash_123', $vote->voter_hash);
        $this->assertEquals(1, $vote->election_id);
    }

    public function test_can_find_vote_by_receipt_hash()
    {
        $vote = VoteRecord::factory()->create([
            'receipt_hash' => 'unique_receipt_hash'
        ]);

        $found = $this->repository->findByReceiptHash('unique_receipt_hash');

        $this->assertNotNull($found);
        $this->assertEquals($vote->id, $found->id);
    }

    public function test_returns_null_for_nonexistent_receipt_hash()
    {
        $found = $this->repository->findByReceiptHash('nonexistent');

        $this->assertNull($found);
    }

    public function test_can_check_if_user_voted_in_election()
    {
        $voterHash = 'user_hash_123';
        $electionId = 1;

        // User hasn't voted
        $this->assertFalse($this->repository->hasUserVotedInElection($voterHash, $electionId));

        // Create vote
        VoteRecord::factory()->create([
            'voter_hash' => $voterHash,
            'election_id' => $electionId
        ]);

        // User has voted
        $this->assertTrue($this->repository->hasUserVotedInElection($voterHash, $electionId));
    }

    public function test_can_get_vote_stats()
    {
        VoteRecord::factory()->count(3)->create([
            'election_id' => 1,
            'voter_hash' => 'hash1'
        ]);

        VoteRecord::factory()->count(2)->create([
            'election_id' => 1,
            'voter_hash' => 'hash2'
        ]);

        $stats = $this->repository->getVoteStats(1);

        $this->assertEquals(5, $stats['total_votes']);
        $this->assertEquals(2, $stats['unique_voters']);
        $this->assertGreaterThanOrEqual(0, $stats['votes_today']);
        $this->assertGreaterThanOrEqual(0, $stats['votes_this_week']);
    }

    public function test_can_find_votes_by_voter_hash()
    {
        $voterHash = 'voter_123';

        VoteRecord::factory()->count(2)->create([
            'voter_hash' => $voterHash,
            'election_id' => 1
        ]);

        VoteRecord::factory()->create([
            'voter_hash' => 'different_hash',
            'election_id' => 1
        ]);

        $votes = $this->repository->findByVoterHash($voterHash);

        $this->assertCount(2, $votes);
        $this->assertEquals($voterHash, $votes->first()->voter_hash);
    }

    public function test_can_count_votes_by_election()
    {
        VoteRecord::factory()->count(5)->create(['election_id' => 1]);
        VoteRecord::factory()->count(3)->create(['election_id' => 2]);

        $this->assertEquals(5, $this->repository->countByElection(1));
        $this->assertEquals(3, $this->repository->countByElection(2));
        $this->assertEquals(0, $this->repository->countByElection(999));
    }
}