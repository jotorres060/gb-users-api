<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Http\Resources\UserCollection;
use App\Repositories\IUserRepository;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    private $repository;

    public function __construct(IUserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = $this->repository->all();
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
    public function find(int $id)
    {
        try {
            $user = $this->repository->find($id);

            return response([
                'user' => new UserResource($user)
            ], 200);
        } catch(ModelNotFoundException $e) {
            return response([
                'code' => 404,
                'errors' => [
                    'message' => $e->getMessage()
                ]
            ], 404);
        }
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
            $user = $this->repository->store($request->except('password_confirmation'));
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
    public function update(Request $request, int $id)
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 200);
        }

        try {
            DB::beginTransaction();
            $user = $this->repository->update($request->except('password_confirmation'), $id);
            DB::commit();

            return response([
                'user' => new UserResource($user)
            ], 200);
        } catch(ModelNotFoundException $e) {
            return response([
                'code' => 404,
                'errors' => [
                    'message' => $e->getMessage()
                ]
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'code' => 500,
                'errors' => [
                    'message' => 'Internal Server Error.' . $e->getMessage()
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
    public function destroy(int $id)
    {
        try {
            DB::beginTransaction();
            $user = $this->repository->delete($id);
            DB::commit();

            return response([
                'user' => new UserResource($user)
            ], 200);
        } catch(ModelNotFoundException $e) {
            return response([
                'code' => 404,
                'errors' => [
                    'message' => $e->getMessage()
                ]
            ], 404);
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
            'email'    => 'required|email',
            'password' => 'required|string|min:6|confirmed',
            'age'      => 'required|numeric',
            'gender'   => 'required|in:Female,Male',
            'address'  => 'required|string'
        ]);
    }
}
