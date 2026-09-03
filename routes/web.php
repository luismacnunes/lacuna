<?php

use App\Http\Controllers\AskController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\CurationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\MapController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', fn () => redirect()->route('ask.index'))->name('dashboard');
Route::get('/', [AskController::class, 'index'])->name('ask.index');
Route::post('/ask', [AskController::class, 'store'])->name('ask.store');
Route::get('/search', [SearchController::class, 'index'])->name('search.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');

    Route::get('/queue', [QueueController::class, 'index'])->name('queue.index');

    Route::get('/curate/{pending}', [CurationController::class, 'edit'])->name('curate.edit');
    Route::put('/curate/{pending}', [CurationController::class, 'update'])->name('curate.update');
    Route::delete('/curate/{pending}', [CurationController::class, 'dismiss'])->name('curate.dismiss');

    Route::get('/review', [ReviewController::class, 'index'])->name('review.index');
    Route::post('/review/{curatedAnswer}/confirm', [ReviewController::class, 'confirm'])->name('review.confirm');
    Route::put('/review/{curatedAnswer}', [ReviewController::class, 'update'])->name('review.update');
    Route::delete('/review/{curatedAnswer}', [ReviewController::class, 'destroy'])->name('review.destroy');

    Route::get('/metrics', [MetricsController::class, 'index'])->name('metrics.index');

    Route::get('/map', [MapController::class, 'index'])->name('map.index');

    Route::get('/review/{curatedAnswer}/edit', [ReviewController::class, 'edit'])->name('review.edit');
});

require __DIR__.'/auth.php';