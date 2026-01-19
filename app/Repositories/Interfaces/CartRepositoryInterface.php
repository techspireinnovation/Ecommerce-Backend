<?php

namespace App\Repositories\Interfaces;

interface CartRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function store(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
