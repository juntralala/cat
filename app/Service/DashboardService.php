<?php

namespace App\Service;

use App\Repository\DashboardRepository;
use Closure;
use DateTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use function PHPUnit\Framework\callback;

class DashboardService
{
    public function __construct(
        private DashboardRepository $repository
    ) {
    }

    public function getExpendituresPerSKU(?string $search, ?Carbon $start, ?Carbon $end, $page = 1)
    {
        if ($start == null) {
            $start = Date::now()->subMonth();
        }
        if ($end == null) {
            $end = Date::now();
        }
        return $this->repository->getExpendituresPerSKU($search, $start, $end, $page);
    }

 
    /**
     * 
     * @param  callable(\Illuminate\Support\Collection<int, TValue>, int): mixed  $callback
     */
    public function exportXlsx(callable $callback, ?Carbon $start, ?Carbon $end)
    {
        if ($start == null) {
            $start = Date::now()->subMonth();
        }
        if ($end == null) {
            $end = Date::now();
        }
        return $this->repository->exportXlsx($callback, $start, $end);
    }
}
