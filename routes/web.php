<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
Route::view('/','welcome')->name('welcome');
Route::view('/about','about')->name('about');
Route::get('/profile',[ProfileController::class,'show'])->name('profile.show');
Route::get('/tasks',[TaskController::class,'index'])->name('tasks.index');

Route::middleware('guest')->group(function(){Route::get('/login',[AuthController::class,'loginForm'])->name('login');Route::post('/login',[AuthController::class,'login']);Route::get('/register',[AuthController::class,'registerForm'])->name('register');Route::post('/register',[AuthController::class,'register']);});
Route::middleware('auth')->group(function(){Route::post('/logout',[AuthController::class,'logout'])->name('logout');Route::patch('/profile',[ProfileController::class,'update'])->name('profile.update');Route::get('/tasks/create',[TaskController::class,'create'])->name('tasks.create');Route::post('/tasks',[TaskController::class,'store'])->name('tasks.store');Route::get('/tasks/{task}/edit',[TaskController::class,'edit'])->name('tasks.edit');Route::patch('/tasks/{task}',[TaskController::class,'update'])->name('tasks.update');Route::delete('/tasks/{task}',[TaskController::class,'destroy'])->name('tasks.destroy');});

Route::get('/tasks/{task}',[TaskController::class,'show'])->whereNumber('task')->name('tasks.show');
