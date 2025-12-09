<?php

namespace App\Http\Resources\User;

use Date;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'role' => $this->role->name,
            'profilePhotoPath' => $this->profile_photo_path,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'deletedAt' => $this->when($this->deleted_at !== null, $this->deleted_at)
        ];
    }
}