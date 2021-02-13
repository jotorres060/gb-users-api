<?php

namespace App\Repositories;

use App\Repositories\IUserRepository;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserRepository implements IUserRepository
{
    private $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function all()
    {
        return $this->model->orderByDesc('id')->get();
    }

    public function find(int $id)
    {
        $user = $this->model->find($id);
        if (is_null($user)) {
            throw new ModelNotFoundException("User not found.");
        }
        return $user;
    }

    public function store(array $data)
    {
        return $this->model::create($data);
    }

    public function update(array $data, int $id)
    {
        $user = $this->find($id);
        $user->update($data);
        return $user;
    }

    public function delete(int $id)
    {
        $user = $this->find($id);
        $user->delete();
        return $user;
    }
}
