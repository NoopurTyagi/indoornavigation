<?php

use App\Http\Controllers\WaypointController;
use Illuminate\Support\Facades\Route;

Route::get('/heel', function () {
    return view('hell1');
})->name("landings");

Route::get('waypoints', [WaypointController::class, 'index']);
Route::get('waypoints/{id}', [WaypointController::class, 'show']);
Route::post('waypoints', [WaypointController::class, 'store']);
Route::put('waypoints/{id}', [WaypointController::class, 'update']);
Route::delete('waypoints/{id}', [WaypointController::class, 'delete']);
Route::get('/path', [WaypointController::class, 'getPath']);
Route::get('/path1', [WaypointController::class, 'convert']);