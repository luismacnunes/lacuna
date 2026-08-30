<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\AskController;

Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::get('/', [AskController::class, 'index'])->name('ask.index');
Route::post('/ask', [AskController::class, 'store'])->name('ask.store');