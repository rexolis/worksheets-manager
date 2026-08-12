<?php

use App\Http\Controllers\SectionController;
use App\Http\Controllers\WorksheetController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('worksheets', [WorksheetController::class, 'index'])
        ->name('worksheets');
    Route::get('worksheets/{worksheetClass}', [WorksheetController::class, 'showClass'])
        ->name('worksheets.show-class');
    Route::get('worksheets/{worksheetClass}/{subject}', [WorksheetController::class, 'subject'])
        ->name('worksheets.subject');

    Route::get('sections', [SectionController::class, 'index'])
        ->name('sections');
    Route::post('sections', [SectionController::class, 'store'])
        ->name('sections.store');
    Route::put('sections/{section}', [SectionController::class, 'update'])
        ->name('sections.update');
    Route::post('sections/{section}/archive', [SectionController::class, 'archive'])
        ->name('sections.archive');
    Route::delete('sections/{section}', [SectionController::class, 'destroy'])
        ->name('sections.destroy');
    Route::get('sections/{worksheetClass}', [SectionController::class, 'showClass'])
        ->name('sections.show-class');
    Route::get('sections/{worksheetClass}/{section}', [SectionController::class, 'show'])
        ->name('sections.show');
});

require __DIR__.'/settings.php';
