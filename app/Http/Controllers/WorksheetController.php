<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorksheetRequest;
use App\Http\Requests\UpdateWorksheetRequest;
use App\Models\Subject;
use App\Models\Worksheet;
use App\Models\WorksheetClass;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WorksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Worksheet::class);

        $classes = WorksheetClass::query()
            ->with(['subjects' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->map(fn (WorksheetClass $class) => [
                'id' => $class->id,
                'name' => $class->name,
                'slug' => Str::slug($class->name),
                'subjects' => $class->subjects->map(fn ($subject) => [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'slug' => Str::slug($subject->name),
                ])->values(),
            ]);

        return Inertia::render('Worksheets/Index', [
            'classes' => $classes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorksheetRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Worksheet $worksheet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Worksheet $worksheet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWorksheetRequest $request, Worksheet $worksheet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Worksheet $worksheet)
    {
        //
    }

    public function subject($worksheetClass, $subject)
    {
        $this->authorize('viewAny', Worksheet::class);

        $class = WorksheetClass::query()
            ->get()
            ->first(fn (WorksheetClass $class) => Str::slug($class->name) === $worksheetClass);

        $subjectModel = Subject::query()
            ->get()
            ->first(fn (Subject $s) => Str::slug($s->name) === $subject);

        if ($class === null || $subjectModel === null) {
            throw new NotFoundHttpException;
        }
        $worksheets = Worksheet::query()
            ->whereBelongsTo($class)
            ->whereBelongsTo($subjectModel)
            ->orderBy('number')
            ->get(['id', 'number', 'title', 'subtopic']);

        return Inertia::render('Worksheets/Subject', [
            'worksheetClass' => [
                'id' => $class->id,
                'name' => $class->name,
                'slug' => Str::slug($class->name),
            ],
            'subject' => [
                'id' => $subjectModel->id,
                'name' => $subjectModel->name,
                'slug' => Str::slug($subjectModel->name),
            ],
            'worksheets' => $worksheets,
        ]);
    }
}
