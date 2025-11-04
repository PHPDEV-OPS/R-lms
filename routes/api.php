<?php

use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Students API
Route::get('/students', [StudentController::class, 'index']);
Route::get('/students/{id}', [StudentController::class, 'show']);

// Enrollments API
Route::post('/enrollments', [EnrollmentController::class, 'store']);

// Authenticated routes
Route::middleware('auth:sanctum')->get('/my-courses', [StudentController::class, 'myCourses']);