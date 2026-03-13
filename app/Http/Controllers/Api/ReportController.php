<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SpiritualLevelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private SpiritualLevelService $spiritualLevelService) {}

    /**
     * Display the quarterly report.
     */
    public function quarterlyReport(Request $request): JsonResponse
    {
        $year = (int) $request->input('year', now()->year);
        $quarter = (int) $request->input('quarter', ceil(now()->month / 3));

        $report = $this->spiritualLevelService->generateQuarterlyReport($year, $quarter);
        $summary = $this->spiritualLevelService->getQuarterlySummary($year, $quarter);

        return response()->json([
            'report' => $report,
            'summary' => $summary,
            'filters' => [
                'year' => $year,
                'quarter' => $quarter,
            ],
            'available_years' => range(now()->year - 2, now()->year + 1),
        ]);
    }
}
