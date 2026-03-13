<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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
            'activity_type_id' => $this->activity_type_id,
            'activity_type' => new ActivityTypeResource($this->whenLoaded('activityType')),
            'title' => $this->title,
            'description' => $this->description,
            'activity_date' => $this->activity_date->format('Y-m-d'),
            'attendances_count' => $this->whenCounted('attendances'),
            'present_count' => $this->when(isset($this->present_count), $this->present_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
