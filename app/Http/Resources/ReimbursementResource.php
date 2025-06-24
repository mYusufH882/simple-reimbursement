<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReimbursementResource extends JsonResource
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
            'amount' => $this->amount,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at->format('Y-m-d H:i:s'),
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'proofs' => ProofResource::collection($this->whenLoaded('proofs')),
            'log_activities' => LogActivityResource::collection($this->whenLoaded('logActivities')),
        ];
    }
}
