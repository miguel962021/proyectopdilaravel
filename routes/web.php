<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizInvitationController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SurveyAccessController;
use App\Http\Controllers\SurveyResponseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('quizzes', QuizController::class);
    Route::post('quizzes/{quiz}/publish', [QuizController::class, 'publish'])->name('quizzes.publish');
    Route::post('quizzes/{quiz}/close', [QuizController::class, 'close'])->name('quizzes.close');
    Route::post('quizzes/{quiz}/analysis', [QuizController::class, 'analyze'])->name('quizzes.analyze');
    Route::get('quizzes/{quiz}/analysis', [QuizController::class, 'analysis'])->name('quizzes.analysis.show');
    Route::get('quizzes/{quiz}/analysis/export', [QuizController::class, 'exportAnalysis'])->name('quizzes.analysis.export');
    Route::resource('quizzes.questions', QuestionController::class)->except(['index', 'show']);
    Route::resource('quizzes.invitations', QuizInvitationController::class)->only(['store', 'update', 'destroy']);

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('summary', [ReportsController::class, 'summary'])->name('summary');
        Route::get('students', [ReportsController::class, 'students'])->name('students');
        Route::get('surveys', [ReportsController::class, 'surveys'])->name('surveys');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('web')->group(function () {
    Route::get('ingresar-codigo', [SurveyAccessController::class, 'showLinkForm'])->name('surveys.access.form');
    Route::post('ingresar-codigo', [SurveyAccessController::class, 'verifyCode'])->name('surveys.access.verify');
    Route::get('responder/{code}', [SurveyResponseController::class, 'showSurvey'])->name('surveys.respond.show');
    Route::post('responder/{code}', [SurveyResponseController::class, 'submitSurvey'])->name('surveys.respond.submit');
});

require __DIR__.'/auth.php';
