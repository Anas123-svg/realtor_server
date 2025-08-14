<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SiteViewrController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\HeroSectionFeaturedPropertyController;
use App\Http\Controllers\AllProperties;


Route::get('/properties', [PropertyController::class, 'index']);
Route::post('/properties', [PropertyController::class, 'store']);
Route::get('/properties/all', [AllProperties::class, 'search2']);

Route::post('/contact/send-email', [ContactController::class, 'sendAgentEmail']);
Route::post('/contact-us', [ContactController::class, 'sendContactEmail']);
Route::post('/property/eval', [ContactController::class, 'propertyEvalEmail']);

Route::middleware('auth:sanctum')->get('/test-auth', function (Request $request) {
    return response()->json(['admin' => $request->user()]);
});
Route::get('properties/hero-section', [PropertyController::class, 'heroSection']);

Route::post('/properties', [PropertyController::class, 'store']);
Route::get('/properties/{id}', [PropertyController::class, 'show']);
Route::put('/properties/{id}', [PropertyController::class, 'update']);
Route::delete('/properties/{id}', [PropertyController::class, 'destroy']);
Route::get('/search/properties', [PropertyController::class, 'search']);
Route::patch('/properties/{id}/increment-views', [PropertyController::class, 'incrementViews']);
Route::patch('/properties/{id}/increment-likes', [PropertyController::class, 'incrementLikes']);
Route::put('/admin/update-password', [AdminController::class, 'updatePassword']);
Route::get('most-viewed/properties', [PropertyController::class, 'getFilteredProperties']);

Route::post('/increment-site-views', [SiteViewrController::class, 'incrementSiteViews']);
Route::get('/dashboard', [AdminController::class, 'dashboard']);
Route::prefix('admin')->group(function () {
    Route::post('/register', [AdminController::class, 'register']);
    Route::post('/login', [AdminController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/by-token', [AdminController::class, 'getAdminByToken']); //get admin by token
        Route::get('/', [AdminController::class, 'getAdmins']);//   get all admins
        Route::get('/{id}', [AdminController::class, 'getAdmin']); //get admin by id
        Route::put('/{id}', [AdminController::class, 'updateAdmin']); //update admin by id
        Route::delete('/{id}', [AdminController::class, 'deleteAdmin']);//delete admin by id
        Route::put('/update-password', [AdminController::class, 'updatePassword']);// update password
    });
});








Route::prefix('projects')->group(function(){
    Route::get('/', [ProjectController::class, 'index']); //get all projects
    Route::post('/', [ProjectController::class, 'store']); //create project
    Route::put('/{id}', [ProjectController::class, 'update']); //update project
    Route::delete('/{id}', [ProjectController::class, 'destroy']); //delete project
    Route::get('/{id}', [ProjectController::class, 'show']); //get project by id
    Route::get('/search/properties', [ProjectController::class, 'searchProjectProperties']);

});


Route::prefix('hero-property')->group(function () {
    Route::post('add/{id}', [HeroSectionFeaturedPropertyController::class, 'add']); // add hero property
    Route::delete('remove/{id}', [HeroSectionFeaturedPropertyController::class, 'remove']); // remove hero property
    Route::get('index', [HeroSectionFeaturedPropertyController::class, 'index']); // get all hero properties
    Route::get('list', [PropertyController::class, 'heroAndNonHeroProperties']); // get hero and non hero properties
});

Route::get('/search/projects', [ProjectController::class, 'search']); //get project by i


































































