<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 
use Illuminate\Support\Facades\Validator; 
use \App\Traits\ApiResponse; 
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{

    use ApiResponse; 


    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        $users = $query->get();

        return $this->sendResponse('User list retrieved successfully.', $users);
    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(),[
           'name'=>"required",
            'profile_picture'=>"nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
            'role'=>"required|in:Admin,Employee,Guest",
            'email'=>"required|email|unique:users,email",
            'password'=>"required|min:6|confirmed",
        ]);
        if($validator->fails()){
            $errors = $validator->errors();
            return $this->sendError("Failure",$errors);
        }


        $data = $request->only(['name', 'email', 'role', 'password']);

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $newname = time() . '-' . $file->getClientOriginalName();
            $file->storeAs('ProfileImages', $newname, 'public');
            $data['profile_picture'] = 'storage/ProfileImages/' . $newname;
        }

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        return $this->sendResponse('User created successfully.', $user, 201);
    }


    public function show(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }
        return $this->sendResponse('User retrieved successfully.', $user);
    }


    public function update(Request $request, string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'role' => 'required|in:Admin,Employee,Guest',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->sendError("Validation Error", $errors);
        }

                $data = $request->only(['name', 'email', 'role', 'password']);

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $newname = time() . '-' . $file->getClientOriginalName();
            $file->storeAs('ProfileImages', $newname, 'public');
            $data['profile_picture'] = 'storage/ProfileImages/' . $newname;
        }

        $data['password'] = bcrypt($data['password']);

        $user->update($data);
        return $this->sendResponse('User updated successfully.', $user);
    }


    public function destroy(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        $user->delete();
        return $this->sendResponse('User deleted successfully.', null, 204);
    }
}
