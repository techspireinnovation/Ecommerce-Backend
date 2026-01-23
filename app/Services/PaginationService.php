<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaginationService
{
    public function format(LengthAwarePaginator $paginator): array
    {
        return [
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
            ],
        ];
    }
}
