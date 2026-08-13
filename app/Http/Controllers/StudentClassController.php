<?php

namespace App\Http\Controllers;

use App\Enums\SectionStatusId;
use App\Enums\StudentClassStatus;
use App\Http\Requests\EnrollStudentClassRequest;
use App\Models\Section;
use App\Models\StudentClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentClassController extends Controller
{
    /**
     * Display the authenticated user's pending and approved class enrollments.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', StudentClass::class);

        $enrollments = StudentClass::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', [
                StudentClassStatus::Pending,
                StudentClassStatus::Approved,
            ])
            ->with([
                'section' => fn ($query) => $query->notDeleted()->with('worksheetClass:id,name,slug'),
            ])
            ->get()
            ->filter(fn (StudentClass $enrollment) => $enrollment->section !== null)
            ->groupBy(fn (StudentClass $enrollment) => $enrollment->section->worksheet_class_id)
            ->map(function ($groupedEnrollments) {
                /** @var StudentClass $first */
                $first = $groupedEnrollments->first();
                $worksheetClass = $first->section->worksheetClass;

                return [
                    'id' => $worksheetClass->id,
                    'name' => $worksheetClass->name,
                    'slug' => $worksheetClass->slug,
                    'sections' => $groupedEnrollments
                        ->sortBy(fn (StudentClass $enrollment) => $enrollment->section->name)
                        ->values()
                        ->map(fn (StudentClass $enrollment) => [
                            'id' => $enrollment->section->id,
                            'name' => $enrollment->section->name,
                            'section_type' => $enrollment->section->section_type,
                            'class_code' => $enrollment->section->class_code,
                            'date_start' => $enrollment->section->date_start->toDateString(),
                            'date_end' => $enrollment->section->date_end->toDateString(),
                            'status' => $enrollment->status->value,
                        ])
                        ->all(),
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();

        return Inertia::render('Classes/Index', [
            'classes' => $enrollments,
        ]);
    }

    /**
     * Enroll the authenticated user in a section using its class code.
     */
    public function store(EnrollStudentClassRequest $request): RedirectResponse
    {
        $section = Section::query()
            ->where('class_code', $request->validated('class_code'))
            ->where('status', SectionStatusId::Active)
            ->firstOrFail();

        StudentClass::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'section_id' => $section->id,
            ],
            [
                'status' => StudentClassStatus::Pending,
            ],
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Enrollment request submitted.'),
        ]);

        return to_route('classes');
    }
}
