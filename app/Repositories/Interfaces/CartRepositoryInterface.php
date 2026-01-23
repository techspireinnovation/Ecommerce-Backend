<?php

namespace App\Repositories\Interfaces;

interface CartRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function store(array $data);
    public function toggle(int $id);
    public function delete(int $id);
}
