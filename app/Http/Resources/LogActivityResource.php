<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogActivityResource extends JsonResource
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
            'action' => $this->action,
            'type' => $this->type,
            'old_value' => $this->old_value,
            'new_value' => $this->new_value,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'reimbursement' => new ReimbursementResource($this->whenLoaded('reimbursement')),
        ];
    }
}
