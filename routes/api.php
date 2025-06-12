<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::get('jwt_generate', [\App\Http\Controllers\Api\JwtManagementController::class, 'generate']);
Route::get('jwt_refresh', [\App\Http\Controllers\Api\JwtManagementController::class, 'refresh']);
Route::get('jwt_destroy', [\App\Http\Controllers\Api\JwtManagementController::class, 'destroy']);

?>
