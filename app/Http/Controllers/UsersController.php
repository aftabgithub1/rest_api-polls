<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index(){
        $users = app('db')->table('users')->get();
        return response()->json($users);
    }
    
    // Database insert
    public function create(Request $request){

        // validation 
        try {
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }

        // insert
        try {
            $id = app('db')->table('users')->insertGetId([
                'name' => $request->name,
                'email' => strtolower($request->email),
                'password' => app('hash')->make($request->password),
                'created_at' => Carbon::now(),
            ]);
            $user = app('db')->table('users')->where('id', $id)->first();
            return response()->json([
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->password,
                'created_at' => $user->created_at
            ], 201);

        } catch (PDOExeption $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }


    }

    // Login
    public function authenticate(Request $request){
        // validation 
        try {
            $this->validate($request, [
                'email' => 'required|email',
                'password' => 'required'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
        
        $tocken = app('auth')->attempt($request->only('email', 'password'));

        if($tocken){
            return response()->json([
                'success' => true,
                'message' => "User Authenticated",
                'tocken' => $tocken
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Invalid Credentials"
        ]);
    }

    // Me
    public function me(){
        $user = app('auth')->user();

        if($user){
            return response()->json([
                'success' => true,
                'message' => "User profile found",
                'user' => $user
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "User not found"
        ], 404);
    }
} 
