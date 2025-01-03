<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Redis;



Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('BatchUpload');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/upload-excel', function () {

    // $redisData = Redis::lrange('excel_batches', 0, -1); // Fetch all data from the Redis list
    // $decodedData = array_map('json_decode', $redisData);
    // dd($decodedData);

    return Inertia::render('UploadExcel');
})->middleware(['auth', 'verified'])->name('upload-excel');
Route::get('/get-plots', [FileController::class, 'getPlots']);

Route::post('/upload-to-redis', [FileController::class, 'uploadToRedis']);
Route::get('/check-redis-data', [FileController::class, 'checkRedisData']);
Route::post('/store-into-mysql', [FileController::class, 'storeIntoMysql']);


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
