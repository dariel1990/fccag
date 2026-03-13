<?php

namespace App\Http\Controllers;

use App\Services\SpiritualLevelService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(private SpiritualLevelService $spiritualLevelService) {}

    /**
     * Display the quarterly report.
     */
    public function quarterlyReport(Request $request): Response
    {
        $year = (int) $request->input('year', now()->year);
        $quarter = (int) $request->input('quarter', ceil(now()->month / 3));

        $report = $this->spiritualLevelService->generateQuarterlyReport($year, $quarter);
        $summary = $this->spiritualLevelService->getQuarterlySummary($year, $quarter);

        return Inertia::render('reports/QuarterlyReport', [
            'report' => $report,
            'summary' => $summary,
            'filters' => [
                'year' => $year,
                'quarter' => $quarter,
            ],
            'availableYears' => range(now()->year - 2, now()->year + 1),
        ]);
    }
}
