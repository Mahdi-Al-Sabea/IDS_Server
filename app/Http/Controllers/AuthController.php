<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse; // Assuming you have this trait for API responses


class AuthController extends Controller
{
        use ApiResponse;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->sendError("Failure", $errors);
        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();
            $token =  $user->createToken('MyApp')->plainTextToken;
            $success['token']= $token;
            $success['name'] =  $user->name;
            $success['role'] =  $user->role;
            return $this->sendResponse("Connected",$success);
        }
        else{
            return $this->sendError('Unauthorised.',[]);
        }

    }
}
