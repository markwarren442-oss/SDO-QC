<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ExportController;

// --- AUTH ---
Route::get('/', function () {
    return redirect()->route('login');
});
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// --- ADMIN ROUTES ---
Route::group(['prefix' => 'admin', 'middleware' => ['auth.session']], function () {
    // GET: Pages
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/index', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/absent', [AdminController::class, 'absent'])->name('admin.absent');
    Route::get('/calendar', [AdminController::class, 'calendar'])->name('admin.calendar');
    Route::get('/forms', [AdminController::class, 'forms'])->name('admin.forms');
    Route::get('/audit', [AdminController::class, 'audit'])->name('admin.audit');
    Route::get('/profile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::get('/manual', [AdminController::class, 'manual'])->name('admin.manual');
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');

    // POST: Employee CRUD (from index.php)
    Route::post('/employee/add', [AdminController::class, 'addEmployee'])->name('admin.employee.add');
    Route::post('/employee/import', [AdminController::class, 'importExcel'])->name('admin.employee.import');
    Route::post('/employee/preview-excel', [AdminController::class, 'previewExcel'])->name('admin.employee.previewExcel');
    Route::get('/employee/template', [AdminController::class, 'downloadTemplate'])->name('admin.employee.template');
    Route::post('/employee/update', [AdminController::class, 'updateEmployee'])->name('admin.employee.update');
    Route::post('/employee/delete', [AdminController::class, 'deleteEmployee'])->name('admin.employee.delete');
    Route::post('/employee/late', [AdminController::class, 'saveLateMinutes'])->name('admin.employee.late');
    Route::post('/employee/undertime', [AdminController::class, 'saveUndertimeMinutes'])->name('admin.employee.undertime');
    Route::post('/employee/absent', [AdminController::class, 'saveAbsentFromIndex'])->name('admin.employee.absent');
    Route::post('/employee/bulk-update', [AdminController::class, 'bulkUpdateEmployees'])->name('admin.employee.bulkUpdate');
    Route::post('/employee/present-all', [AdminController::class, 'markPresentAll'])->name('admin.employee.presentAll');
    Route::post('/employee/clear-all', [AdminController::class, 'clearAllEmployees'])->name('admin.employee.clearAll');
    Route::post('/employee/attendance-actions', [AdminController::class, 'saveAttendanceActions'])->name('admin.employee.attendanceActions');
    Route::post('/employee/late/delete', [AdminController::class, 'deleteLateRecord'])->name('admin.employee.late.delete');
    Route::post('/employee/undertime/delete', [AdminController::class, 'deleteUndertimeRecord'])->name('admin.employee.undertime.delete');
    Route::post('/employee/absent/delete', [AdminController::class, 'deleteAbsentRecord'])->name('admin.employee.absent.delete');
    Route::post('/employee/remarks', [AdminController::class, 'saveRemarks'])->name('admin.employee.remarks');
    Route::post('/employee/remarks/done', [AdminController::class, 'markRemarkDone'])->name('admin.employee.remarks.done');
    Route::get('/employee/remarks/history', [AdminController::class, 'getRemarksHistory'])->name('admin.employee.remarks.history');

    // POST: Calendar / Attendance (from calendar.php)
    Route::post('/calendar/autosave', [AdminController::class, 'calendarAutosave'])->name('admin.calendar.autosave');
    Route::post('/calendar/absence-reason', [AdminController::class, 'saveAbsenceReason'])->name('admin.calendar.absenceReason');
    Route::post('/calendar/save-batch', [AdminController::class, 'saveBatch'])->name('admin.calendar.saveBatch');
    Route::post('/calendar/quick-absent', [AdminController::class, 'quickMarkAbsent'])->name('admin.calendar.quickAbsent');
    Route::post('/calendar/add-holiday', [AdminController::class, 'addHoliday'])->name('admin.calendar.addHoliday');
    Route::post('/calendar/clear-month', [AdminController::class, 'clearMonthData'])->name('admin.calendar.clearMonth');
    Route::post('/calendar/save-merge', [AdminController::class, 'saveCellMerge'])->name('admin.calendar.saveMerge');
    Route::post('/calendar/delete-merge', [AdminController::class, 'deleteCellMerge'])->name('admin.calendar.deleteMerge');

    // POST: Forms (from forms.php)
    Route::post('/forms/upload', [AdminController::class, 'uploadForm'])->name('admin.forms.upload');
    Route::post('/forms/delete', [AdminController::class, 'deleteForm'])->name('admin.forms.delete');
    Route::post('/forms/save-layout', [AdminController::class, 'saveFormLayout'])->name('admin.forms.saveLayout');
    Route::get('/forms/download/{id}', [AdminController::class, 'downloadForm'])->name('admin.forms.download');
    Route::post('/forms/folder/create', [AdminController::class, 'createFolder'])->name('admin.forms.folder.create');
    Route::post('/forms/folder/delete', [AdminController::class, 'deleteFolder'])->name('admin.forms.folder.delete');
    Route::post('/forms/move', [AdminController::class, 'moveForm'])->name('admin.forms.move');
    Route::get('/forms/folders/list', [AdminController::class, 'listFolders'])->name('admin.forms.folders.list');

    // POST: Profile / Users (from profile.php)
    Route::post('/profile/update', [AdminController::class, 'updateProfile'])->name('admin.profile.update');
    Route::post('/user/add', [AdminController::class, 'addUser'])->name('admin.user.add');
    Route::post('/user/delete', [AdminController::class, 'deleteUser'])->name('admin.user.delete');

    // Export
    Route::get('/export/weekly', [ExportController::class, 'weeklyReport'])->name('admin.export.weekly');
    Route::get('/export/attendance', [ExportController::class, 'attendanceReport'])->name('admin.export.attendance');
    Route::get('/export/yearly', [ExportController::class, 'yearlyReport'])->name('admin.export.yearly');
    Route::get('/individual/summary', [AdminController::class, 'getIndividualSummary'])->name('api.individual.summary');
});

// --- STAFF ROUTES ---
Route::group(['prefix' => 'staff', 'middleware' => ['auth.session']], function () {
    Route::get('/employeeview', [StaffController::class, 'employeeview'])->name('staff.employeeview');
    Route::post('/profile/update', [StaffController::class, 'updateProfile'])->name('staff.profile.update');
});

// --- API ROUTES (AJAX) ---
Route::group(['prefix' => 'api', 'middleware' => ['auth.session']], function () {
    Route::get('/dashboard-data', [ApiController::class, 'dashboardData'])->name('api.dashboardData');
    Route::get('/search-employees', [ApiController::class, 'searchEmployees'])->name('api.searchEmployees');
    Route::post('/ai-query', [ApiController::class, 'aiQuery'])->name('api.aiQuery');
});
