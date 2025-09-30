<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\Voting\VotingService;
use App\Repositories\VoteRepository;
use App\Repositories\VoteTallyRepository;
use App\Models\Voting\VoteAuthorization;
use App\Models\Voting\VoteRecord;
use App\Exceptions\VotingException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class VotingServiceTest extends TestCase
{
    use RefreshDatabase;

    private VotingService $service;
    private VoteRepository $voteRepository;
    private VoteTallyRepository $tallyRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->voteRepository = Mockery::mock(VoteRepository::class);
        $this->tallyRepository = Mockery::mock(VoteTallyRepository::class);

        $this->service = new VotingService(
            $this->voteRepository,
            $this->tallyRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_cast_vote_successfully()
    {
        $authorization = VoteAuthorization::factory()->create([
            'voter_hash' => 'test_hash',
            'election_id' => 1,
            'is_used' => false
        ]);

        $selections = [
            1 => [1, 2], // position 1, candidates 1 and 2
            2 => [3]     // position 2, candidate 3
        ];

        // Mock repository calls
        $this->voteRepository->shouldReceive('hasUserVotedInElection')
            ->with('test_hash', 1)
            ->andReturn(false);

        $this->voteRepository->shouldReceive('create')
            ->once()
            ->andReturn(new VoteRecord(['cast_at' => now()]));

        $this->tallyRepository->shouldReceive('incrementTally')
            ->times(3); // 3 total votes (2 + 1)

        $result = $this->service->castVote($authorization, $selections);

        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('receipt', $result);
        $this->assertArrayHasKey('receipt_hash', $result['receipt']);
        $this->assertArrayHasKey('cast_at', $result['receipt']);
    }

    public function test_throws_exception_when_user_already_voted()
    {
        $this->expectException(VotingException::class);
        $this->expectExceptionMessage('Vote has already been cast for this election');

        $authorization = VoteAuthorization::factory()->create([
            'voter_hash' => 'test_hash',
            'election_id' => 1
        ]);

        $this->voteRepository->shouldReceive('hasUserVotedInElection')
            ->with('test_hash', 1)
            ->andReturn(true);

        $this->service->castVote($authorization, [1 => [1]]);
    }

    public function test_get_voter_receipt_returns_correct_data()
    {
        $voterHash = 'test_voter_hash';
        $electionId = 1;

        $voteRecord = new VoteRecord([
            'id' => 123,
            'voter_hash' => $voterHash,
            'election_id' => $electionId,
            'receipt_hash' => 'receipt_123',
            'cast_at' => now()
        ]);

        $this->voteRepository->shouldReceive('getUserVoteForElection')
            ->with($voterHash, $electionId)
            ->andReturn($voteRecord);

        // Mock election model
        $election = Mockery::mock();
        $election->shouldReceive('getAttribute')->with('id')->andReturn($electionId);
        $election->shouldReceive('getAttribute')->with('title')->andReturn('Test Election');

        $result = $this->service->getVoterReceipt($voterHash, $election);

        $this->assertNotNull($result);
        $this->assertEquals('receipt_123', $result['receipt_hash']);
        $this->assertEquals('Test Election', $result['election_title']);
        $this->assertEquals('verified', $result['status']);
    }

    public function test_get_voter_receipt_returns_null_when_no_vote_found()
    {
        $this->voteRepository->shouldReceive('getUserVoteForElection')
            ->andReturn(null);

        $result = $this->service->getVoterReceipt('nonexistent', 1);

        $this->assertNull($result);
    }

    public function test_verify_receipt_returns_correct_data()
    {
        $receiptHash = 'test_receipt_hash';

        $voteRecord = new VoteRecord([
            'id' => 123,
            'voter_hash' => 'voter_hash',
            'election_id' => 1,
            'receipt_hash' => $receiptHash,
            'cast_at' => now()
        ]);

        $this->voteRepository->shouldReceive('findByReceiptHash')
            ->with($receiptHash)
            ->andReturn($voteRecord);

        // Mock election model
        $election = Mockery::mock();
        $election->shouldReceive('getAttribute')->with('title')->andReturn('Test Election');

        $result = $this->service->verifyReceipt($receiptHash);

        $this->assertNotNull($result);
        $this->assertEquals($receiptHash, $result['receipt_hash']);
        $this->assertEquals('Test Election', $result['election_title']);
        $this->assertTrue($result['valid']);
        $this->assertEquals('Receipt verified successfully', $result['message']);
    }

    public function test_verify_receipt_returns_null_for_invalid_receipt()
    {
        $this->voteRepository->shouldReceive('findByReceiptHash')
            ->with('invalid_receipt')
            ->andReturn(null);

        $result = $this->service->verifyReceipt('invalid_receipt');

        $this->assertNull($result);
    }

    public function test_generate_receipt_hash_is_unique()
    {
        $selections1 = [1 => [1]];
        $selections2 = [1 => [1]];

        $hash1 = $this->invokePrivateMethod($this->service, 'generateReceiptHash', [$selections1]);
        $hash2 = $this->invokePrivateMethod($this->service, 'generateReceiptHash', [$selections2]);

        // Hashes should be different due to timestamp and random bytes
        $this->assertNotEquals($hash1, $hash2);
    }

    public function test_generate_receipt_hash_is_sha256()
    {
        $selections = [1 => [1, 2]];
        $hash = $this->invokePrivateMethod($this->service, 'generateReceiptHash', [$selections]);

        $this->assertEquals(64, strlen($hash)); // SHA256 is 64 characters
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash);
    }

    /**
     * Helper method to invoke private methods for testing
     */
    private function invokePrivateMethod($object, string $method, array $parameters = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}