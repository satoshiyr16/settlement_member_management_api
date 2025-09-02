<?php

namespace App\Http\Resources\Entity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>|null
     */
    public function toArray(Request $request): array|null
    {
        if (!$this->resource) {
            return null;
        }

        return [
            'member_id' => $this->id,
            'nickname' => $this->nickname,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date ? $this->birth_date->format('Y-m-d') : null,
            'enrollment_date' => $this->enrollment_date ? $this->enrollment_date->format('Y-m-d') : null,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
