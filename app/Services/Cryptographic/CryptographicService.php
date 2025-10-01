<?php

namespace App\Services\Cryptographic;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

const JSON_SORT_KEYS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

class CryptographicService
{
    private string $hashAlgorithm;
    private string $idPepper;
    private bool $chainEnabled;
    private string $genesisHash;

    public function __construct()
    {
        $this->hashAlgorithm = config('cryptographic.hash_algorithm', 'sha256');
        $this->idPepper = config('cryptographic.id_hashing.pepper', config('app.key'));
        $this->chainEnabled = config('cryptographic.vote_chain.enabled', true);
        $this->genesisHash = config('cryptographic.vote_chain.genesis_hash', 'ayapoll_genesis_block');
    }

    /**
     * Hash an ID number with salt and pepper for secure storage
     */
    public function hashIdNumber(string $idNumber, string $salt): string
    {
        $cost = config('cryptographic.id_hashing.cost', 12);
        
        // Combine ID number with salt and pepper
        $combined = $idNumber . $salt . $this->idPepper;
        
        // Use PHP's optimized password_hash for better performance
        return password_hash($combined, PASSWORD_ARGON2ID, ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 3]);
    }

    /**
     * Generate a secure random salt
     */
    public function generateSalt(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Generate a vote token for one-time use
     */
    public function generateVoteToken(int $userId, int $electionId): string
    {
        $length = config('cryptographic.tokens.vote_token_length', 64);
        $timestamp = now()->timestamp;
        
        // Create a unique string combining user, election, and timestamp
        $randomBytes = bin2hex(random_bytes(16));
        $unique = $userId . $electionId . $timestamp . $randomBytes;
        
        // Hash it to create the token
        $token = hash($this->hashAlgorithm, $unique);
        
        return substr($token, 0, $length);
    }

    /**
     * Generate a verification code for MFA
     */
    public function generateVerificationCode(int $length = 6, string $type = 'numeric'): string
    {
        return match($type) {
            'numeric' => str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT),
            'alphanumeric' => Str::upper(Str::random($length)),
            'alpha' => substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/26))), 0, $length),
            default => str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT),
        };
    }

    /**
     * Generate a receipt hash for vote verification
     */
    public function generateReceiptHash(array $voteData, string $chainHash): string
    {
        $includeTimestamp = config('cryptographic.receipts.include_timestamp', true);
        $includeElectionInfo = config('cryptographic.receipts.include_election_info', true);
        
        $receiptData = [
            'vote_hash' => $voteData['vote_hash'],
            'chain_hash' => $chainHash,
        ];
        
        if ($includeTimestamp) {
            $receiptData['timestamp'] = $voteData['cast_at'] ?? now()->toISOString();
        }
        
        if ($includeElectionInfo) {
            $receiptData['election_id'] = $voteData['election_id'];
            $receiptData['position_id'] = $voteData['position_id'];
        }
        
        $receiptString = json_encode($receiptData, JSON_SORT_KEYS);
        $hash = hash($this->hashAlgorithm, $receiptString);
        
        $format = config('cryptographic.receipts.format', 'base64');
        
        return match($format) {
            'base64' => base64_encode(hex2bin($hash)),
            'hex' => $hash,
            default => $hash,
        };
    }

    /**
     * Generate chain hash for vote integrity
     */
    public function generateChainHash(array $voteData, ?string $previousHash = null): string
    {
        if (!$this->chainEnabled) {
            return '';
        }
        
        $previousHash = $previousHash ?? $this->genesisHash;
        
        $chainData = [
            'previous_hash' => $previousHash,
            'vote_hash' => $voteData['vote_hash'],
            'election_id' => $voteData['election_id'],
            'position_id' => $voteData['position_id'],
        ];
        
        if (config('cryptographic.vote_chain.include_timestamp', true)) {
            $chainData['timestamp'] = $voteData['cast_at'] ?? now()->toISOString();
        }
        
        $chainString = json_encode($chainData, JSON_SORT_KEYS);
        
        return hash($this->hashAlgorithm, $chainString);
    }

    /**
     * Generate vote hash (anonymous ballot identifier)
     */
    public function generateVoteHash(array $ballotData, string $voteToken): string
    {
        // Create anonymous vote hash without any user identifiers
        $voteData = [
            'ballot_data' => $ballotData,
            'vote_token' => $voteToken,
            'timestamp' => now()->toISOString(),
            'random' => bin2hex(random_bytes(32)),
        ];
        
        $voteString = json_encode($voteData, JSON_SORT_KEYS);
        
        return hash($this->hashAlgorithm, $voteString);
    }

    /**
     * Generate audit log integrity hash
     */
    public function generateAuditHash(array $logData, ?string $previousHash = null): string
    {
        if (!config('cryptographic.audit_integrity.enabled', true)) {
            return '';
        }
        
        $auditData = [
            'previous_hash' => $previousHash ?? '',
            'user_id' => $logData['user_id'] ?? null,
            'action' => $logData['action'],
            'entity_type' => $logData['entity_type'] ?? null,
            'entity_id' => $logData['entity_id'] ?? null,
            'timestamp' => $logData['created_at'] ?? now()->toISOString(),
            'ip_address' => $logData['ip_address'],
        ];
        
        $auditString = json_encode($auditData, JSON_SORT_KEYS);
        
        return hash($this->hashAlgorithm, $auditString);
    }

    /**
     * Encrypt sensitive data
     */
    public function encryptData(mixed $data): string
    {
        try {
            return Crypt::encrypt($data);
        } catch (\Exception $e) {
            Log::error('Encryption failed', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Data encryption failed');
        }
    }

    /**
     * Decrypt sensitive data
     */
    public function decryptData(string $encryptedData): mixed
    {
        try {
            return Crypt::decrypt($encryptedData);
        } catch (\Exception $e) {
            Log::error('Decryption failed', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Data decryption failed');
        }
    }

    /**
     * Hash IP address for audit logs
     */
    public function hashIpAddress(string $ipAddress): string
    {
        $salt = config('app.key');
        return hash($this->hashAlgorithm, $ipAddress . $salt);
    }

    /**
     * Verify chain integrity
     */
    public function verifyChainIntegrity(array $votes): bool
    {
        if (!$this->chainEnabled || empty($votes)) {
            return true;
        }
        
        $previousHash = $this->genesisHash;
        
        foreach ($votes as $vote) {
            $expectedHash = $this->generateChainHash([
                'vote_hash' => $vote['vote_hash'],
                'election_id' => $vote['election_id'],
                'position_id' => $vote['position_id'],
                'cast_at' => $vote['cast_at'],
            ], $previousHash);
            
            if ($vote['chain_hash'] !== $expectedHash) {
                Log::warning('Chain integrity violation detected', [
                    'vote_id' => $vote['id'],
                    'expected_hash' => $expectedHash,
                    'actual_hash' => $vote['chain_hash'],
                ]);
                return false;
            }
            
            $previousHash = $vote['chain_hash'];
        }
        
        return true;
    }

    /**
     * Generate digital signature for data
     */
    public function signData(array $data): string
    {
        if (!config('cryptographic.signatures.enabled', true)) {
            return '';
        }
        
        $dataString = json_encode($data, JSON_SORT_KEYS);
        $privateKeyPath = config('cryptographic.signatures.private_key_path');
        
        if (!$privateKeyPath || !$this->isValidKeyPath($privateKeyPath) || !file_exists($privateKeyPath)) {
            Log::warning('Private key not found for signing', ['path' => basename($privateKeyPath ?? 'null')]);
            return '';
        }
        
        $privateKey = file_get_contents($privateKeyPath);
        $signature = '';
        
        if (openssl_sign($dataString, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            return base64_encode($signature);
        }
        
        Log::error('Failed to sign data');
        return '';
    }

    /**
     * Verify digital signature
     */
    public function verifySignature(array $data, string $signature): bool
    {
        if (!config('cryptographic.signatures.enabled', true)) {
            return true;
        }
        
        $dataString = json_encode($data, JSON_SORT_KEYS);
        $publicKeyPath = config('cryptographic.signatures.public_key_path');
        
        if (!$publicKeyPath || !$this->isValidKeyPath($publicKeyPath) || !file_exists($publicKeyPath)) {
            Log::warning('Public key not found for verification', ['path' => basename($publicKeyPath ?? 'null')]);
            return false;
        }
        
        $publicKey = file_get_contents($publicKeyPath);
        $binarySignature = base64_decode($signature);
        
        $result = openssl_verify($dataString, $binarySignature, $publicKey, OPENSSL_ALGO_SHA256);
        
        return $result === 1;
    }

    /**
     * Generate key pair for digital signatures
     */
    public function generateKeyPair(): array
    {
        $keySize = config('cryptographic.signatures.key_size', 2048);
        
        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => $keySize,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
        
        $resource = openssl_pkey_new($config);
        
        if (!$resource) {
            throw new \RuntimeException('Failed to generate key pair');
        }
        
        openssl_pkey_export($resource, $privateKey);
        $publicKey = openssl_pkey_get_details($resource)['key'];
        
        return [
            'private_key' => $privateKey,
            'public_key' => $publicKey,
        ];
    }

    /**
     * Validate key file path to prevent path traversal
     */
    private function isValidKeyPath(string $path): bool
    {
        $validator = app(\App\Services\Security\FilePathValidator::class);
        return $validator->validatePath($path);
    }
}