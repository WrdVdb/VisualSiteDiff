<?php
  
use Illuminate\Support\Facades\Route;
  
use App\Http\Controllers\DiffController;
Route::view('/', 'welcome');
Route::get('{config}', [DiffController::class,'index'])->name('diff.index');
Route::post('{config}/add', [DiffController::class,'add'])->name('diff.add');
Route::get('{config}/create/all', [DiffController::class,'createAll'])->name('diff.createall');
Route::post('{config}/create', [DiffController::class,'create'])->name('diff.createpost');
Route::get('{config}/create/{index}', [DiffController::class,'create'])->name('diff.create');
Route::get('{config}/compare/{index}', [DiffController::class,'compare'])->name('diff.compare');