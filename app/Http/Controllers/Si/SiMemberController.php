<?php

namespace App\Http\Controllers\Si;

use App\Enums\Gender;
use App\Enums\SiActivityAssessment;
use App\Enums\SiSpiritualAssessment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Si\StoreSiMemberRequest;
use App\Http\Requests\Si\UpdateSiMemberRequest;
use App\Models\Participant;
use App\Models\SiMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SiMemberController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('si/members/Index', [
            'caregivers' => Participant::query()
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name']),
            'members' => SiMember::query()
                ->with('caregiver:id,first_name,last_name')
                ->orderBy('name')
                ->get()
                ->map(fn (SiMember $member) => [
                    'id' => $member->id,
                    'caregiver_id' => $member->caregiver_id,
                    'name' => $member->name,
                    'sex' => $member->sex->value,
                    'sex_label' => $member->sex->label(),
                    'ph_id' => $member->ph_id,
                    'address' => $member->address,
                    'status' => $member->status->value,
                    'status_label' => $member->status->label(),
                    'status_color' => $member->status->color(),
                    'enrolled_at' => $member->enrolled_at->toDateString(),
                    'exited_at' => $member->exited_at?->toDateString(),
                    'caregiver' => $member->caregiver
                        ? ['id' => $member->caregiver->id, 'name' => $member->caregiver->full_name]
                        : null,
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('si/members/Create', [
            'caregivers' => Participant::query()
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name']),
        ]);
    }

    public function store(StoreSiMemberRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            if (empty($validated['caregiver_id']) && ! empty($validated['caregiver'])) {
                $siClassificationId = \App\Models\Classification::where('name', 'SI')->value('id');

                $caregiver = Participant::create([
                    'first_name' => $validated['caregiver']['first_name'],
                    'last_name' => $validated['caregiver']['last_name'],
                    'gender' => Gender::Female,
                    'contact_number' => $validated['caregiver']['contact_number'] ?? null,
                    'address' => $validated['caregiver']['address'] ?? null,
                    'date_joined' => now(),
                    'is_active' => true,
                    'classification_id' => $siClassificationId,
                ]);
                $validated['caregiver_id'] = $caregiver->id;
            }

            unset($validated['caregiver']);

            SiMember::create($validated);
        });

        return to_route('si.members.index');
    }

    public function details(SiMember $member): JsonResponse
    {
        $member->load('caregiver:id,first_name,last_name');

        $categories = \App\Models\SiActivityCategory::where('is_active', true)->get();

        $categoryScores = $categories->map(fn ($cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
            'weight' => $cat->weight,
            'score' => $member->categoryScore($cat->id),
            'weighted_score' => $member->categoryScore($cat->id) * $cat->weight,
        ]);

        $overallPct = $member->overallPercentage();

        $activities = $member->siActivities()
            ->with('category:id,name')
            ->orderByDesc('conducted_at')
            ->get()
            ->map(function (\App\Models\SiActivity $activity) use ($member) {
                $attendance = \App\Models\SiAttendance::where('si_activity_id', $activity->id)
                    ->where('si_member_id', $member->id)
                    ->first();

                return [
                    'id' => $activity->id,
                    'title' => $activity->title,
                    'conducted_at' => $activity->conducted_at->toDateString(),
                    'category_id' => $activity->si_activity_category_id,
                    'category' => $activity->category?->name,
                    'speaker' => $activity->speaker,
                    'topic' => $activity->topic,
                    'status' => $attendance?->status->value,
                    'status_label' => $attendance?->status->label(),
                    'status_color' => $attendance?->status->color(),
                    'remarks' => $attendance?->remarks,
                    'recommendation' => $attendance?->recommendation,
                ];
            });

        return response()->json([
            'member' => [
                'id' => $member->id,
                'name' => $member->name,
                'sex' => $member->sex->label(),
                'ph_id' => $member->ph_id,
                'address' => $member->address,
                'status' => $member->status->value,
                'status_label' => $member->status->label(),
                'status_color' => $member->status->color(),
                'enrolled_at' => $member->enrolled_at->toDateString(),
                'exited_at' => $member->exited_at?->toDateString(),
                'caregiver' => $member->caregiver
                    ? ['id' => $member->caregiver->id, 'name' => $member->caregiver->full_name]
                    : null,
            ],
            'category_scores' => $categoryScores,
            'overall_percentage' => $overallPct,
            'star_rating' => $member->starRating(),
            'spiritual_assessment' => [
                'value' => $member->spiritualAssessment()->value,
                'label' => $member->spiritualAssessment()->label(),
                'color' => $member->spiritualAssessment()->color(),
            ],
            'activity_assessment' => [
                'value' => $member->activityAssessment()->value,
                'label' => $member->activityAssessment()->label(),
                'color' => $member->activityAssessment()->color(),
            ],
            'activities' => $activities,
            'categories' => $categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name]),
        ]);
    }

    public function edit(SiMember $member): Response
    {
        return Inertia::render('si/members/Edit', [
            'member' => [
                'id' => $member->id,
                'caregiver_id' => $member->caregiver_id,
                'name' => $member->name,
                'sex' => $member->sex->value,
                'ph_id' => $member->ph_id,
                'address' => $member->address,
                'status' => $member->status->value,
                'enrolled_at' => $member->enrolled_at->toDateString(),
                'exited_at' => $member->exited_at?->toDateString(),
            ],
            'caregivers' => Participant::query()
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name']),
        ]);
    }

    public function update(UpdateSiMemberRequest $request, SiMember $member): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $member) {
            if (empty($validated['caregiver_id']) && ! empty($validated['caregiver'])) {
                $siClassificationId = \App\Models\Classification::where('name', 'SI')->value('id');

                $caregiver = Participant::create([
                    'first_name' => $validated['caregiver']['first_name'],
                    'last_name' => $validated['caregiver']['last_name'],
                    'gender' => Gender::Female,
                    'contact_number' => $validated['caregiver']['contact_number'] ?? null,
                    'address' => $validated['caregiver']['address'] ?? null,
                    'date_joined' => now(),
                    'is_active' => true,
                    'classification_id' => $siClassificationId,
                ]);
                $validated['caregiver_id'] = $caregiver->id;
            }

            unset($validated['caregiver']);
            $member->update($validated);
        });

        return to_route('si.members.index');
    }

    public function updateAssessments(Request $request, SiMember $member): RedirectResponse
    {
        $validated = $request->validate([
            'spiritual_assessments' => ['nullable', 'array'],
            'spiritual_assessments.*' => ['string', 'in:'.implode(',', array_column(SiSpiritualAssessment::cases(), 'value'))],
            'activity_assessments' => ['nullable', 'array'],
            'activity_assessments.*' => ['string', 'in:'.implode(',', array_column(SiActivityAssessment::cases(), 'value'))],
        ]);

        $member->update([
            'spiritual_assessments' => $validated['spiritual_assessments'] ?? [],
            'activity_assessments' => $validated['activity_assessments'] ?? [],
        ]);

        return back();
    }

    public function destroy(SiMember $member): RedirectResponse
    {
        $member->delete();

        return to_route('si.members.index');
    }
}
