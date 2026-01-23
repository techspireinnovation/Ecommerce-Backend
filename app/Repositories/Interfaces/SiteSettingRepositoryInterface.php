<?php

namespace App\Repositories\Interfaces;

interface SiteSettingRepositoryInterface
{
    public function get();
    public function storeOrUpdate(array $data);
}
