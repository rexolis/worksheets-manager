<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Section::class);

        $user = $request->user();

        $sectionsQuery = $user->isAdmin()
            ? Section::query()
            : $user->sections();

        $sections = $sectionsQuery
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
