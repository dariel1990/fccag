<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParticipantResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'gender' => $this->gender->value,
            'birthday' => $this->birthday?->format('Y-m-d'),
            'contact_number' => $this->contact_number,
            'address' => $this->address,
            'cell_group_id' => $this->cell_group_id,
            'cell_group' => $this->whenLoaded('cellGroup', fn () => $this->cellGroup?->name),
            'classification_id' => $this->classification_id,
            'classification' => $this->whenLoaded('classification', fn () => $this->classification?->name),
            'department_id' => $this->department_id,
            'department' => $this->whenLoaded('department', fn () => $this->department?->name),
            'ministry_ids' => $this->whenLoaded('ministries', fn () => $this->ministries->pluck('id')),
            'ministries' => $this->whenLoaded('ministries', fn () => $this->ministries->pluck('name')),
            'date_joined' => $this->date_joined->format('Y-m-d'),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
