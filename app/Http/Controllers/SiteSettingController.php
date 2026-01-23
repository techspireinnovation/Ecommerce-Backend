<?php

namespace App\Http\Controllers;

use App\Http\Requests\SiteSetting\StoreOrUpdateRequest;
use App\Http\Resources\SiteSettingResource;
use App\Repositories\Interfaces\SiteSettingRepositoryInterface;
use Illuminate\Http\JsonResponse;

class SiteSettingController extends Controller
{
    private SiteSettingRepositoryInterface $siteSettingRepository;

    public function __construct(SiteSettingRepositoryInterface $siteSettingRepository)
    {
        $this->siteSettingRepository = $siteSettingRepository;
    }

    /**
     * GET /api/site-settings
     */
    public function show(): JsonResponse
    {
        $siteSetting = $this->siteSettingRepository->get();

        return response()->json([
            'success' => true,
            'data' => new SiteSettingResource($siteSetting),
        ]);
    }

    /**
     * POST /api/admin/site-settings
     */
    public function storeOrUpdate(StoreOrUpdateRequest $request): JsonResponse
    {
        $siteSetting = $this->siteSettingRepository->storeOrUpdate($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Site settings saved successfully',
            'data' => new SiteSettingResource($siteSetting),
        ]);
    }

}
