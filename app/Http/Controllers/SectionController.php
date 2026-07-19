<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\User;
use App\Models\WorksheetClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
            ->with('worksheetClass:id,name,slug')
            ->orderBy('date_start')
            ->orderBy('name')
            ->get()
            ->map(fn (Section $section) => $this->sectionPayload($section));

        return Inertia::render('Sections/Index', [
            'sections' => $sections,
        ]);
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
            ->with('worksheetClass:id,name,slug')
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
        ]);
    }

    /**
     * Determine what sections the user can view.
     * An admin can view all sections, a teacher can view only their assigned sections.
     * @return Builder<Section>|BelongsToMany<Section, User>
     */
    private function sectionsFor(Request $request): Builder|BelongsToMany
    {
        $user = $request->user();

        return $user->isAdmin()
            ? Section::query()
            : $user->sections();
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     class_code: string,
     *     date_start: string,
     *     date_end: string,
     *     worksheet_class: array{id: int, name: string, slug: string}
     * }
     */
    private function sectionPayload(Section $section): array
    {
        return [
            'id' => $section->id,
            'name' => $section->name,
            'class_code' => $section->class_code,
            'date_start' => $section->date_start->toDateString(),
            'date_end' => $section->date_end->toDateString(),
            'worksheet_class' => [
                'id' => $section->worksheetClass->id,
                'name' => $section->worksheetClass->name,
                'slug' => $section->worksheetClass->slug,
            ],
        ];
    }
}
