<?php

namespace App\Dto\User;

use Illuminate\Http\UploadedFile;

class UserEditRequest
{
    public function __construct(
        public string $name,
        public string $username,
        public ?string $password,
        public string $role,
        public ?string $profilePhotoPath,
    )
    {}
}
