<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\NotificationController;






/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); */


    Route::get("Notification", [NotificationController::class, "index"]);
    /* Route::post("Notification/{subject}/{content}/{user_id}", [NotificationController::class, "store"]); */
    Route::get("Notification/{id}", [NotificationController::class, "show"]);
    Route::delete("Notification/{id}", [NotificationController::class, "destroy"]);
    Route::get("Notification/User/{userId}", [NotificationController::class, "showByUserId"]);




Route::middleware(['auth:sanctum','checkadmin'])->group(function () {

    Route::post("Room", [RoomController::class, "store"]);
    Route::put("Room/{id}", [RoomController::class, "update"]);
    Route::delete("Room/{id}", [RoomController::class, "destroy"]);

    Route::post("Feature", [FeatureController::class, "store"]);
    Route::get("Feature", [FeatureController::class, "index"]);
    Route::get("Feature/{id}", [FeatureController::class, "show"]);
    Route::put("Feature/{id}", [FeatureController::class, "update"]);
    Route::delete("Feature/{id}", [FeatureController::class, "destroy"]);

});




Route::middleware(['auth:sanctum'])->group(function () {

    Route::post("Meeting", [MeetingController::class, "store"]);
    Route::get("Meeting", [MeetingController::class, "index"]);
    Route::get("Meeting/{id}", [MeetingController::class, "show"]);
    Route::put("Meeting/{id}", [MeetingController::class, "update"]);
    Route::delete("Meeting/{id}", [MeetingController::class, "destroy"]);

    Route::post("User", [UserController::class, "store"]);
    Route::get("User", [UserController::class, "index"]);
    Route::get("User/{id}", [UserController::class, "show"]);
    Route::put("User/{id}", [UserController::class, "update"]);
    Route::delete("User/{id}", [UserController::class, "destroy"]);

    
    Route::get("Room", [RoomController::class, "index"]);
    Route::get("Room/{id}", [RoomController::class, "show"]);

});



Route::post("login", [AuthController::class, "login"]);
