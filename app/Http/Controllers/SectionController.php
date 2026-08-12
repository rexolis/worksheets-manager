<?php

namespace App\Http\Controllers;

use App\Enums\SectionStatusId;
use App\Http\Requests\StoreSectionRequest;
use App\Http\Requests\UpdateSectionRequest;
use App\Models\Section;
use App\Models\User;
use App\Models\WorksheetClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Section::class);

        $sections = $this->sectionsFor($request)
            ->with([
                'worksheetClass:id,name,slug',
                'teachers:id',
            ])
            ->orderBy('date_start')
            ->orderBy('name')
            ->get()
            ->map(fn (Section $section) => $this->sectionPayload($section));

        return Inertia::render('Sections/Index', [
            'sections' => $sections,
            'teachers' => $request->user()->isAdmin()
                ? $this->reviewMasters()
                : [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSectionRequest $request): RedirectResponse
    {
        $validated = $request->safe()->except(['teacher_ids']);
        $teacherIds = $request->validated('teacher_ids') ?? [];

        $section = Section::query()->create($validated);
        $section->teachers()->attach($teacherIds);

        $section->load('worksheetClass:id,slug');

        return to_route('sections.show-class', $section->worksheetClass->slug);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSectionRequest $request, Section $section): RedirectResponse
    {
        if ($section->isDeleted()) {
            throw new NotFoundHttpException;
        }

        $validated = $request->safe()->except(['teacher_ids']);
        $teacherIds = $request->validated('teacher_ids') ?? [];

        $section->update($validated);
        $section->teachers()->sync($teacherIds);

        $section->load('worksheetClass:id,slug');

        return to_route('sections.show-class', $section->worksheetClass->slug);
    }

    /**
     * Archive the specified section.
     */
    public function archive(Request $request, Section $section): RedirectResponse
    {
        $this->authorize('archive', $section);

        $section->update([
            'status' => SectionStatusId::Archived,
        ]);

        $section->load('worksheetClass:id,slug');

        return to_route('sections.show-class', $section->worksheetClass->slug);
    }

    /**
     * Soft-delete the specified section.
     */
    public function destroy(Section $section): RedirectResponse
    {
        $this->authorize('delete', $section);

        $section->update([
            'status' => SectionStatusId::Deleted,
        ]);

        $section->load('worksheetClass:id,slug');

        return to_route('sections.show-class', $section->worksheetClass->slug);
    }

    /**
     * Display sections for the given worksheet class.
     */
    public function showClass(Request $request, string $worksheetClass): Response
    {
        $this->authorize('viewAny', Section::class);

        $class = WorksheetClass::query()
            ->where('slug', $worksheetClass)
            ->first();

        if ($class === null) {
            throw new NotFoundHttpException;
        }

        $sections = $this->sectionsFor($request)
            ->whereBelongsTo($class)
            ->with([
                'worksheetClass:id,name,slug',
                'teachers:id',
            ])
            ->orderBy('date_start')
            ->orderBy('name')
            ->get()
            ->map(fn (Section $section) => $this->sectionPayload($section));

        return Inertia::render('Sections/Class', [
            'worksheetClass' => [
                'id' => $class->id,
                'name' => $class->name,
                'slug' => $class->slug,
            ],
            'sections' => $sections,
            'teachers' => $request->user()->isAdmin()
                ? $this->reviewMasters()
                : [],
        ]);
    }

    /**
     * Display the given section.
     */
    public function show(Request $request, string $worksheetClass, string $section): Response
    {
        $class = WorksheetClass::query()
            ->where('slug', $worksheetClass)
            ->first();

        if ($class === null) {
            throw new NotFoundHttpException;
        }

        $sectionModel = $this->sectionsFor($request)
            ->whereBelongsTo($class)
            ->where('class_code', $section)
            ->with([
                'worksheetClass:id,name,slug',
                'teachers:id',
            ])
            ->first();

        if ($sectionModel === null) {
            throw new NotFoundHttpException;
        }

        $this->authorize('view', $sectionModel);

        return Inertia::render('Sections/Show', [
            'worksheetClass' => [
                'id' => $class->id,
                'name' => $class->name,
                'slug' => $class->slug,
            ],
            'section' => $this->sectionPayload($sectionModel),
            'students' => [],
        ]);
    }

    /**
     * @return list<array{id: int, name: string, email: string}>
     */
    private function reviewMasters(): array
    {
        return User::query()
            ->select(['users.id', 'users.name', 'users.email'])
            ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('roles.slug', 'teacher')
            ->orderBy('users.name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])
            ->all();
    }

    /**
     * Determine what sections the user can view.
     * An admin can view all sections, a teacher can view only their assigned sections.
     * Soft-deleted sections are excluded; archived sections remain visible.
     *
     * @return Builder<Section>|BelongsToMany<Section, User>
     */
    private function sectionsFor(Request $request): Builder|BelongsToMany
    {
        $user = $request->user();

        $query = $user->isAdmin()
            ? Section::query()
            : $user->sections();

        return $query->notDeleted();
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     section_type: string,
     *     class_code: string,
     *     date_start: string,
     *     date_end: string,
     *     status: string,
     *     teacher_ids: list<int>,
     *     worksheet_class: array{id: int, name: string, slug: string}
     * }
     */
    private function sectionPayload(Section $section): array
    {
        return [
            'id' => $section->id,
            'name' => $section->name,
            'section_type' => $section->section_type,
            'class_code' => $section->class_code,
            'date_start' => $section->date_start->toDateString(),
            'date_end' => $section->date_end->toDateString(),
            'status' => strtolower($section->status->name),
            'teacher_ids' => $section->relationLoaded('teachers')
                ? $section->teachers->pluck('id')->all()
                : [],
            'worksheet_class' => [
                'id' => $section->worksheetClass->id,
                'name' => $section->worksheetClass->name,
                'slug' => $section->worksheetClass->slug,
            ],
        ];
    }
}
