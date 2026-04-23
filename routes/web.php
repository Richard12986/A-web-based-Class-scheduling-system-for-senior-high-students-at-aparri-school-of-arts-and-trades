<?php

use App\Http\Controllers\AcademicSetupController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SchedulingController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'welcome'])->name('welcome');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'admin.only'])->group(function () {

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
    });

    Route::prefix('academic-setup')->name('academic-setup.')->group(function () {
        Route::get('/', [AcademicSetupController::class, 'index'])->name('index');

        Route::post('/sections', [AcademicSetupController::class, 'storeSection'])->name('sections.store');
        Route::put('/sections/{section}', [AcademicSetupController::class, 'updateSection'])->name('sections.update');
        Route::delete('/sections/{section}', [AcademicSetupController::class, 'destroySection'])->name('sections.destroy');

        Route::post('/rooms', [AcademicSetupController::class, 'storeRoom'])->name('rooms.store');
        Route::put('/rooms/{room}', [AcademicSetupController::class, 'updateRoom'])->name('rooms.update');
        Route::delete('/rooms/{room}', [AcademicSetupController::class, 'destroyRoom'])->name('rooms.destroy');

        Route::post('/subjects', [AcademicSetupController::class, 'storeSubject'])->name('subjects.store');
        Route::put('/subjects/{subject}', [AcademicSetupController::class, 'updateSubject'])->name('subjects.update');
        Route::delete('/subjects/{subject}', [AcademicSetupController::class, 'destroySubject'])->name('subjects.destroy');

        Route::post('/time-slots', [AcademicSetupController::class, 'storeTimeSlot'])->name('time-slots.store');
        Route::put('/time-slots/{timeSlot}', [AcademicSetupController::class, 'updateTimeSlot'])->name('time-slots.update');
        Route::delete('/time-slots/{timeSlot}', [AcademicSetupController::class, 'destroyTimeSlot'])->name('time-slots.destroy');
    });

    Route::prefix('teachers')->name('teachers.')->group(function () {
        Route::get('/', [TeacherController::class, 'index'])->name('index');

        Route::post('/records', [TeacherController::class, 'storeTeacher'])->name('records.store');
        Route::put('/records/{teacher}', [TeacherController::class, 'updateTeacher'])->name('records.update');
        Route::delete('/records/{teacher}', [TeacherController::class, 'destroyTeacher'])->name('records.destroy');

        Route::post('/subject-assignments', [TeacherController::class, 'storeTeacherSubject'])->name('subject-assignments.store');
        Route::delete('/subject-assignments/{teacherSubject}', [TeacherController::class, 'destroyTeacherSubject'])->name('subject-assignments.destroy');

        Route::post('/loans', [TeacherController::class, 'storeLoan'])->name('loans.store');
        Route::put('/loans/{teacherLoan}', [TeacherController::class, 'updateLoan'])->name('loans.update');
        Route::delete('/loans/{teacherLoan}', [TeacherController::class, 'destroyLoan'])->name('loans.destroy');

        Route::post('/loan-payments', [TeacherController::class, 'storeLoanPayment'])->name('loan-payments.store');
        Route::put('/loan-payments/{teacherLoanPayment}', [TeacherController::class, 'updateLoanPayment'])->name('loan-payments.update');
        Route::delete('/loan-payments/{teacherLoanPayment}', [TeacherController::class, 'destroyLoanPayment'])->name('loan-payments.destroy');
    });

    Route::prefix('scheduling')->name('scheduling.')->group(function () {
        Route::get('/', [SchedulingController::class, 'index'])->name('index');
        Route::post('/entries', [SchedulingController::class, 'store'])->name('entries.store');
        Route::put('/entries/{schedule}', [SchedulingController::class, 'update'])->name('entries.update');
        Route::delete('/entries/{schedule}', [SchedulingController::class, 'destroy'])->name('entries.destroy');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/print', [ReportsController::class, 'print'])->name('print');
        Route::get('/export/pdf', [ReportsController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/export/excel', [ReportsController::class, 'exportExcel'])->name('export.excel');
    });

});