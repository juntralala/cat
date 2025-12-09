<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserCreateRequest;
use App\Http\Requests\User\UserEditRequest;
use App\Http\Resources\User\UserResource;
use App\Dto\User\UserCreateRequest as UserCreateDto;
use App\Dto\User\UserEditRequest as UserEditDto;
use App\Service\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{

    public function __construct(
        private UserService $userService
    ) {

    }

    public function index() {
        return Inertia::render('User');
    }

    public function getUsers(Request $request)
    {
        $page = $request->integer('page', 1);
        $perPage = $request->integer('per_page', 5);

        return UserResource::collection($this->userService->getUsers($page, $perPage));
    }

    public function addUser(UserCreateRequest $request)
    {
        $safe = $request->safe();
        return response()->json($this->userService->addUsers(
            new UserCreateDto(
                $safe->name,
                $safe->username,
                $safe->password,
                $safe->role
            ),

        ), 201);
    }

    public function deleteUser(Request $request, string $id)
    {
        $deletingUser = $this->userService->getUserById($id);
        if (!$deletingUser) {
            throw new HttpResponseException(response()->json(['message' => 'user not found'], 404));
        }
        $canDelete = $this->userService->isCanDelete($request->user(), $deletingUser);
        if (!$canDelete) {
            throw new AuthorizationException('You have no permission to perform this action');
        }
        $deleted = $this->userService->deleteById($id);
        if (!$deleted) {
            throw new ModelNotFoundException('User not found');
        }
        return response(status: 200);
    }


    public function editUser(UserEditRequest $request, string $id)
    {
        $safe = $request->validated();

        $profilePhotoPath = null;
        if ($request->hasFile('profilePhoto')) {
            $file = $request->file('profilePhoto');
            $profilePhotoPath = $file->store('profile-photos', 'public');
        }
        
        $data = new UserEditDto(
            $safe['name'],
            $safe['username'],
            $safe['password'] ?? null,
            $safe['role'],
            "/storage/$profilePhotoPath",
        );
        
        $edited = $this->userService->editById($id, $data);
        if (!$edited) {
            abort(response()->json(['message' => 'user not found'], 404));
        }
        
        if($request->acceptsJson()) {
            return $edited;
        }
        return redirect()->route('account.profile');
    }
}
