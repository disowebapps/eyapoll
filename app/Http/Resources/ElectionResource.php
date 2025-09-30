<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectionResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->isActive(),
            'is_accepting_candidates' => $this->isAcceptingCandidates(),
            'total_positions' => $this->positions()->count(),
            'total_candidates' => $this->positions()->with('candidates')->get()->sum(function ($position) {
                return $position->candidates->count();
            }),
            'eligibility_criteria' => $this->eligibility_criteria,
            'settings' => $this->settings,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'positions' => PositionResource::collection($this->whenLoaded('positions')),
        ];
    }
}