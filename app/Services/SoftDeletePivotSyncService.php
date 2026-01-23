<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class SoftDeletePivotSyncService
{
    /**
     * Sync a soft-deletable pivot table safely.
     *
     * @param  string  $pivotModel
     * @param  int     $parentId
     * @param  string  $parentKey
     * @param  string  $relatedKey
     * @param  array   $newIds
     */
    public function sync(
        string $pivotModel,
        int $parentId,
        string $parentKey,
        string $relatedKey,
        array $newIds
    ): void {
        $newIds = collect($newIds)->map(fn ($id) => (int) $id);

        $pivotModel::withTrashed()
            ->where($parentKey, $parentId)
            ->whereIn($relatedKey, $newIds)
            ->restore();

        $activeIds = $pivotModel::where($parentKey, $parentId)
            ->whereNull('deleted_at')
            ->pluck($relatedKey);

        $pivotModel::where($parentKey, $parentId)
            ->whereNotIn($relatedKey, $newIds)
            ->whereNull('deleted_at')
            ->delete();

        $toAttach = $newIds->diff($activeIds);

        foreach ($toAttach as $id) {
            $pivotModel::create([
                $parentKey => $parentId,
                $relatedKey => $id,
            ]);
        }
    }
}
