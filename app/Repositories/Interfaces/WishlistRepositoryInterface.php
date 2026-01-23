<?php

namespace App\Repositories\Interfaces;

interface WishlistRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function store(array $data);
    public function toggle(int $id);
    public function delete(int $id);
}
