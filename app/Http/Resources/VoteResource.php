<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'election_id' => $this->election_id,
            'receipt_hash' => $this->receipt_hash,
            'short_receipt_hash' => substr($this->receipt_hash, 0, 8),
            'verification_code' => substr($this->receipt_hash, -6),
            'cast_at' => $this->cast_at,
            'status' => 'verified',
            'is_verifiable' => true,
            'election' => new ElectionResource($this->whenLoaded('election')),
        ];
    }
}