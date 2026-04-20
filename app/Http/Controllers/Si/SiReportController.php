<?php

namespace App\Http\Controllers\Si;

use App\Enums\SiActivityAssessment;
use App\Enums\SiSpiritualAssessment;
use App\Http\Controllers\Controller;
use App\Models\SiActivityCategory;
use App\Models\SiMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiReportController extends Controller
{
    public function index(Request $request): Response
    {
        $fromMonth = $request->query('from_month');
        $toMonth = $request->query('to_month');

        $from = $fromMonth ? Carbon::createFromFormat('Y-m', $fromMonth)->startOfMonth() : null;
        $to = $toMonth ? Carbon::createFromFormat('Y-m', $toMonth)->endOfMonth() : null;

        $categories = SiActivityCategory::where('is_active', true)->get();

        $members = SiMember::query()
            ->with('caregiver:id,first_name,last_name')
            ->orderBy('name')
            ->get()
            ->map(fn (SiMember $member) => [
                'id' => $member->id,
                'name' => $member->name,
                'status' => $member->status->value,
                'caregiver' => $member->caregiver?->full_name,
                'category_scores' => $categories->mapWithKeys(fn ($cat) => [
                    $cat->id => round($member->categoryScore($cat->id, $from, $to), 4),
                ]),
                'overall_percentage' => round($member->overallPercentage($from, $to), 4),
                'star_rating' => $member->starRating($from, $to),
                'spiritual_assessments' => array_map(fn ($a) => [
                    'value' => $a->value,
                    'label' => $a->label(),
                    'color' => $a->color(),
                ], $member->spiritualAssessments()),
                'activity_assessments' => array_map(fn ($a) => [
                    'value' => $a->value,
                    'label' => $a->label(),
                    'color' => $a->color(),
                ], $member->activityAssessments()),
                'spiritual_assessment' => [
                    'label' => $member->spiritualAssessment()->label(),
                    'color' => $member->spiritualAssessment()->color(),
                ],
                'activity_assessment' => [
                    'label' => $member->activityAssessment()->label(),
                    'color' => $member->activityAssessment()->color(),
                ],
            ])
            ->sortByDesc('overall_percentage')
            ->values();

        return Inertia::render('si/reports/Index', [
            'categories' => $categories->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'weight' => $cat->weight,
            ]),
            'members' => $members,
            'filters' => [
                'from_month' => $fromMonth,
                'to_month' => $toMonth,
            ],
            'spiritualAssessmentOptions' => collect(SiSpiritualAssessment::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
                'color' => $s->color(),
            ]),
            'activityAssessmentOptions' => collect(SiActivityAssessment::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
                'color' => $s->color(),
            ]),
        ]);
    }
}
