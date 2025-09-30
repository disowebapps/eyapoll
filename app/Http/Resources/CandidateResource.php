<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateResource extends JsonResource
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
            'user_id' => $this->user_id,
            'election_id' => $this->election_id,
            'position_id' => $this->position_id,
            'manifesto' => $this->manifesto,
            'qualifications' => $this->qualifications,
            'experience' => $this->experience,
            'platform' => $this->platform,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'votes_count' => $this->votes_count ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'position' => new PositionResource($this->whenLoaded('position')),
        ];
    }
}