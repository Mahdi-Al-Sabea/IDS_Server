<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 
use Illuminate\Support\Facades\Validator; 
use \App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{

    use ApiResponse; 


    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role') && $request->role !== null) {
            $query->where('role', $request->role);
        }

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        $perPage = $request->input('per_page', 5);
        $users = $query->paginate($perPage);

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
            return $this->sendError("Validation Error",$errors);
        }


        $data = $request->only(['name', 'email', 'role', 'password']);

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $newname = time() . '-' . $file->getClientOriginalName();
            $file->storeAs('ProfileImages', $newname, 'public');
            $data['profile_picture'] = 'storage/ProfileImages/' . $newname;
        }else{
            $data['profile_picture'] = 'storage/ProfileImages/default.png';
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
        $userId = $request->user()->id; // Get the authenticated user's ID
        $userRole = $request->user()->role; // Get the authenticated user's role

        if ($userRole !== 'Admin' && $userId != $id) {
            return $this->sendError('Unauthorized action.', [], 403);
        }

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

        if ($request->filled('password')) {
            // Only hash the password if it is provided
            $data['password'] = bcrypt($data['password']);
        } else {
            // If password is not provided, keep the existing password
            unset($data['password']);
        }
        

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


    public function getUserProfile(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->sendError('User not authenticated.', [], 401);
        }
        return $this->sendResponse('User profile retrieved successfully.', $user);
    }

    public function getMyMeetings()
    {
        $user = User::find(2);

        if (!$user) {
            return $this->sendError('User not authenticated.', [], 401);
        }

        $meetings = $user->meetings()->with(['agendas', 'room', 'attendees'])->get();

        return $this->sendResponse('User meetings retrieved successfully.', $meetings);
    }

    
}
