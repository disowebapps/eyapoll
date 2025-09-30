<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PositionResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'max_candidates' => $this->max_candidates,
            'voting_type' => $this->voting_type,
            'current_candidates_count' => $this->candidates()->count(),
            'is_full' => $this->candidates()->count() >= $this->max_candidates,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'candidates' => CandidateResource::collection($this->whenLoaded('candidates')),
        ];
    }
}