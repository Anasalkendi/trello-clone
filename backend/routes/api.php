<?php

use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\BoardListController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\CardPositionController;
use App\Http\Controllers\Api\ImportCardController;
use App\Http\Controllers\Api\ProjectActivityController;
use App\Http\Controllers\Api\ProjectBoardPositionController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectMemberController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::apiResource('projects', ProjectController::class);
    Route::get('projects/{project}/activity', ProjectActivityController::class);

    Route::post('projects/{project}/members', [ProjectMemberController::class, 'store']);
    Route::patch('projects/{project}/members/{user}', [ProjectMemberController::class, 'update']);
    Route::delete('projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy']);

    Route::post('projects/{project}/boards/reorder', ProjectBoardPositionController::class);
    Route::apiResource('projects.boards', BoardController::class);

    Route::post('boards/{board}/lists/reorder', [CardPositionController::class, 'reorderLists']);
    Route::apiResource('boards.lists', BoardListController::class)->parameters([
        'lists' => 'board_list',
    ]);

    Route::post('lists/{board_list}/cards/reorder', [CardPositionController::class, 'reorderCards']);
    Route::apiResource('lists.cards', CardController::class)->parameters([
        'lists' => 'board_list',
    ]);

    Route::post('lists/{board_list}/cards/import', ImportCardController::class);

    Route::post('cards/{card}/attachments', [AttachmentController::class, 'store']);
    Route::delete('cards/{card}/attachments/{attachment}', [AttachmentController::class, 'destroy']);
});
