<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/setup/year', [SettingsController::class, 'firstYear'])->name('setup.year');
    Route::post('/setup/year', [SettingsController::class, 'storeFirstYear'])->name('setup.year.store');
    Route::get('/settings/school', [SettingsController::class, 'school'])->name('settings.school');
    Route::post('/settings/school', [SettingsController::class, 'updateSchool'])->name('settings.school.update');
    Route::post('/settings/year/close', [SettingsController::class, 'closeYear'])->name('settings.year.close');
    Route::post('/settings/year/open', [SettingsController::class, 'newYear'])->name('settings.year.open');

    Route::resource('users', UserController::class)->except(['show']);
    Route::post('/classes/wizard/class', [ClassController::class, 'storeClassStep'])->name('classes.wizard.class');
    Route::get('/classes/wizard/method', [ClassController::class, 'method'])->name('classes.wizard.method');
    Route::post('/classes/wizard/method', [ClassController::class, 'storeMethodStep'])->name('classes.wizard.method.store');
    Route::get('/classes/wizard/pronote', [ClassController::class, 'pronote'])->name('classes.wizard.pronote');
    Route::post('/classes/wizard/pronote', [ClassController::class, 'storePronoteStep'])->name('classes.wizard.pronote.store');
    Route::get('/classes/wizard/manual', [ClassController::class, 'manual'])->name('classes.wizard.manual');
    Route::post('/classes/wizard/manual', [ClassController::class, 'storeManualStep'])->name('classes.wizard.manual.store');
    Route::get('/classes/preview', [ClassController::class, 'preview'])->name('classes.preview');
    Route::post('/classes/confirm', [ClassController::class, 'confirm'])->name('classes.confirm');
    Route::resource('classes', ClassController::class)->parameters(['classes' => 'class'])->except(['edit', 'update', 'destroy']);
    Route::resource('students', StudentController::class)->except(['show']);
    Route::post('/exams/wizard/class', [ExamController::class, 'storeClassStep'])->name('exams.wizard.class');
    Route::get('/exams/wizard/type', [ExamController::class, 'type'])->name('exams.wizard.type');
    Route::post('/exams/wizard/type', [ExamController::class, 'storeTypeStep'])->name('exams.wizard.type.store');
    Route::get('/exams/wizard/schedule', [ExamController::class, 'schedule'])->name('exams.wizard.schedule');
    Route::post('/exams/wizard/schedule', [ExamController::class, 'storeScheduleStep'])->name('exams.wizard.schedule.store');
    Route::get('/exams/wizard/students', [ExamController::class, 'students'])->name('exams.wizard.students');
    Route::post('/exams/wizard/confirm', [ExamController::class, 'confirm'])->name('exams.confirm');
    Route::post('/exams/preview', [ExamController::class, 'preview'])->name('exams.preview');
    Route::resource('exams', ExamController::class)->except(['edit', 'update', 'destroy']);
    Route::post('/exams/{exam}/catchup', [ExamController::class, 'catchup'])->name('exams.catchup');

    Route::get('/grades/exam/{exam}', [GradeController::class, 'edit'])->name('grades.edit');
    Route::post('/grades/exam/{exam}', [GradeController::class, 'update'])->name('grades.update');
    Route::get('/grades/summary', [GradeController::class, 'summary'])->name('grades.summary');
    Route::get('/grades/final', [GradeController::class, 'final'])->name('grades.final');

    Route::get('/documents/exam/{exam}/convocations', [DocumentController::class, 'convocations'])->name('documents.convocations');
    Route::get('/documents/exam/{exam}/attendance', [DocumentController::class, 'attendance'])->name('documents.attendance');
    Route::get('/documents/exam/{exam}/oral-list', [DocumentController::class, 'oralList'])->name('documents.oral-list');
    Route::get('/documents/grades', [DocumentController::class, 'grades'])->name('documents.grades');
    Route::get('/documents/final-grades', [DocumentController::class, 'finalGrades'])->name('documents.final-grades');
});
