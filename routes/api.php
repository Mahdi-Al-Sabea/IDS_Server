<?php

use App\Http\Controllers\ActionItemController;
use App\Http\Controllers\AttachmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MinutesOfMeetingController;
use App\Http\Controllers\NotificationController;





/* Route::middleware(['auth:sanctum','checkadmin'])->group(function () {});


Route::middleware(['auth:sanctum','checkemployee'])->group(function () {}); */


Route::middleware(['auth:sanctum','checkrole:Admin'])->group(function () {});

Route::middleware(['auth:sanctum','checkrole:Employee'])->group(function () {});

Route::middleware(['auth:sanctum','checkrole:Guest'])->group(function () {});

Route::middleware(['auth:sanctum','checkrole:Employee,Admin'])->group(function () {});


Route::middleware(['auth:sanctum'])->group(function () {

    Route::get("Room", [RoomController::class, "index"]);
    Route::get("Room/{id}", [RoomController::class, "show"]);
    Route::post("Room", [RoomController::class, "store"]);
    Route::put("Room/{id}", [RoomController::class, "update"]);
    Route::delete("Room/{id}", [RoomController::class, "destroy"]);


    Route::post("Feature", [FeatureController::class, "store"]);
    Route::get("Feature", [FeatureController::class, "index"]);
    Route::get("Feature/{id}", [FeatureController::class, "show"]);
    Route::put("Feature/{id}", [FeatureController::class, "update"]);
    Route::delete("Feature/{id}", [FeatureController::class, "destroy"]);



    Route::post("Meeting", [MeetingController::class, "store"]);
    Route::get("Meeting", [MeetingController::class, "index"]);
    Route::get("Meeting/{id}", [MeetingController::class, "show"]);
    Route::put("Meeting/{id}", [MeetingController::class, "update"]);
    Route::delete("Meeting/{id}", [MeetingController::class, "destroy"]);

    Route::get("User/Profile", [UserController::class, "getUserProfile"]);
    Route::post("User", [UserController::class, "store"]);
    Route::get("User", [UserController::class, "index"]);
    Route::get("UserNotPaginated", [UserController::class, "indexNotPaginated"]);
    Route::get('/User/{id}/meetings', [UserController::class, 'getMyMeetings']);
    Route::get("User/{id}", [UserController::class, "show"]);
    Route::put("User/{id}", [UserController::class, "update"]);
    Route::delete("User/{id}", [UserController::class, "destroy"]);

    
    Route::get("Room", [RoomController::class, "index"]);
    Route::get("Room/getAvailableRoomsForNextHour", [RoomController::class, "getAvailableRoomsForNextHour"]);
    Route::get("Room/{id}", [RoomController::class, "show"]);
    Route::get("Notification", [NotificationController::class, "index"]);
    Route::get("Notification/{id}", [NotificationController::class, "show"]);
    Route::delete("Notification/{id}", [NotificationController::class, "destroy"]);
    Route::get("Notification/User/{userId}", [NotificationController::class, "showByUserId"]);


    Route::get("Minutes", [MinutesOfMeetingController::class, "index"]);
    Route::post("Minutes", [MinutesOfMeetingController::class, "store"]);
    Route::get("Minutes/{id}", [MinutesOfMeetingController::class, "show"]);
    Route::put("Minutes/{id}", [MinutesOfMeetingController::class, "update"]);
    Route::delete("Minutes/{id}", [MinutesOfMeetingController::class, "destroy"]);
    

    Route::get("Attachment", [AttachmentController::class, "index"]);
    Route::post("Attachment", [AttachmentController::class, "store"]);
    Route::get("Attachment/{id}", [AttachmentController::class, "show"]);
    Route::put("Attachment/{id}", [AttachmentController::class, "update"]);
    Route::delete("Attachment/{id}", [AttachmentController::class, "destroy"]);


    Route::get('ActionItem', [ActionItemController::class, 'index']);
    Route::post('ActionItem', [ActionItemController::class, 'store']);
    Route::get('ActionItem/{id}', [ActionItemController::class, 'show']);
    Route::put('ActionItem/{id}', [ActionItemController::class, 'update']);
    Route::delete('ActionItem/{id}', [ActionItemController::class, 'destroy']);
    Route::get('User/{id}/ActionItems', [ActionItemController::class, 'getActionItemsByUser']);
    Route::put('/ActionItem/{id}/toggle', [ActionItemController::class, 'toggleStatus']);

});



Route::post("login", [AuthController::class, "login"]);
