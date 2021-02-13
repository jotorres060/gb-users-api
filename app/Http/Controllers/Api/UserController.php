<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderByDesc('id')->get();
        return response([
            "users" => new UserCollection($users)
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response([
                'code' => 404,
                'errors' => [
                    'message' => 'User not found.'
                ]
            ], 404);
        }

        return response([
            'user' => new UserResource($user)
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 200);
        }

        try {
            DB::beginTransaction();

            $user = User::create([
                "name"     => $request->input("name"),
                "email"    => $request->input("email"),
                "password" => Hash::make($request->input("password")),
                "age"      => $request->input("age"),
                "gender"   => $request->input("gender"),
                "address"  => $request->input("address")
            ]);

            DB::commit();

            return response([
                'user' => new UserResource($user)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'code' => 500,
                'errors' => [
                    'message' => 'Internal Server Error.'
                ]
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 200);
        }

        $user = User::find($id);
        if (!$user) {
            return response([
                'code' => 404,
                'errors' => [
                    'message' => 'User not found.'
                ]
            ], 404);
        }

        try {
            DB::beginTransaction();

            $user->update([
                "name"     => $request->input("name"),
                "email"    => $request->input("email"),
                "password" => Hash::make($request->input("password")),
                "age"      => $request->input("age"),
                "gender"   => $request->input("gender"),
                "address"  => $request->input("address")
            ]);

            DB::commit();

            return response([
                'user' => new UserResource($user)
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'code' => 500,
                'errors' => [
                    'message' => 'Internal Server Error.'
                ]
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response([
                'code' => 404,
                'errors' => [
                    'message' => 'User not found.'
                ]
            ], 404);
        }

        try {
            DB::beginTransaction();
            $user->delete();
            DB::commit();

            return response([
                'user' => new UserResource($user)
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'code' => 500,
                'errors' => [
                    'message' => 'Internal Server Error.'
                ]
            ], 500);
        }
    }

    /**
     * Validate the request.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateRequest(Request $request)
    {
        $data = $request->all();
        return Validator::make($data, [
            'name'     => 'required|string|max:191',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'age'      => 'required|numeric',
            'gender'   => 'required|in:Female,Male',
            'address'  => 'required|string'
        ]);
    }
}
