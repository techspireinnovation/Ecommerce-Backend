<?php

namespace App\Repositories\Interfaces;

interface ProductRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function store(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function activeList();
    public function storeSeo(int $productId, array $seoData);
    public function UpdateSeo(int $productId, array $seoData);

}
