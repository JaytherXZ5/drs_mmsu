<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminOfficeController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DegreeController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FileFolderController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SurveyInstrumentController;
use App\Http\Controllers\SurveyTypeController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\UserTypeController;
use App\Models\SurveyInstrument;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/folder/create', [FolderController::class, 'create_folder']);
Route::get('get-folders', [FolderController::class, 'get_folders']);
Route::get('/folder/{id}', [FolderController::class, 'get_folder']);
Route::get('/folder/{id}/files', [FileController::class, 'getFiles']);
Route::get('/delete-folder/{id}', [FolderController::class, 'deleteFolder']);

Route::post('/file/upload/{id}', [FileController::class, 'upload']);
Route::delete('/delete-file/{id}', [FileController::class, 'deleteFile']);
Route::post('/uploadFiles/{id}', [FileController::class, 'uploadSurveyFiles']);
Route::get('/getFileUser/{id}',[FileController::class, 'getFileUser']);
Route::put('/rename-file/{id}', [FileController::class, 'renameFile']);
Route::get('/file/download/{id}', [FileController::class, 'download']);


Route::get('/archive-files', [ArchiveController::class, 'getArchiveFiles']);

Route::get('/admin-office', [AdminOfficeController::class, 'getAdminOffice']);
Route::get('/roles', [RoleController::class, 'getRoles']);
Route::get('/user_role/{id}' , [RoleController::class, 'getUserRole']);

Route::get('check-token', [AuthController::class, 'checkToken']);
Route::middleware(['auth', 'verified'])->get('isAdmin', [AuthController::class, 'isAdmin']);
Route::post('login',[AuthController::class, 'login']);
Route::post('register',[AuthController::class, 'register']);
Route::post('logout',[AuthController::class, 'logout']);

//areas
Route::get('/areas', [AreaController::class, 'get_area']);
Route::post('/create_area', [AreaController::class, 'create_area']);
Route::get('/areas/total-count', [AreaController::class, 'getTotalAreasCount']);
Route::put('/update_area/{id}', [AreaController::class, 'update']);
Route::delete('/delete_area/{id}', [AreaController::class, 'destroy']);
Route::post('/updateAreaStatus/{id}', [AreaController::class, 'updateAreaStatus']);
//psv_areas
Route::get('/psv_areas', [AreaController::class, 'get_psv_areas']);
Route::post('/create_psv_area', [AreaController::class, 'create_psv_area']);
Route::get('/psv_areas/total-count', [AreaController::class, 'getTotalPsvAreasCount']);
Route::put('/update_psv_area/{id}', [AreaController::class, 'update_psv_area']);
Route::delete('/delete_psv_area/{id}', [AreaController::class, 'destroy_psv_area']);
Route::post('/updatePsvAreaStatus/{id}', [AreaController::class, 'updatePsvAreaStatus']);

//ia_areas
Route::get('/ia_areas', [AreaController::class, 'get_ia_areas']);
Route::post('/create_ia_area', [AreaController::class, 'create_ia_area']);
Route::get('/ia_areas/total-count', [AreaController::class, 'getTotalIaAreasCount']);
Route::put('/update_ia_area/{id}', [AreaController::class, 'update_ia_area']);
Route::delete('/delete_ia_area/{id}', [AreaController::class, 'destroy_ia_area']);
Route::post('/updateIaAreaStatus/{id}', [AreaController::class, 'updateIaAreaStatus']);


Route::post('/create_ia_area', [AreaController::class, 'create_ia_area']);

Route::get('/users', [AccountController::class, 'getUsers']);
Route::get('get-user/{id}', [AccountController::class, 'get_user']);
Route::delete('/delete-user/{id}', [AccountController::class, 'destroy']);
Route::get('/institutions', [InstitutionController::class, 'getInstitutions']);
Route::get('/degrees', [DegreeController::class, 'getDegrees']);
Route::get('/get-user-office' ,[OfficeController::class, 'getCurrentUserOffice']);

Route::get('/survey-types', [SurveyInstrumentController::class, 'getSurveyTypes']);
Route::get('/survey-indicator-types', [SurveyInstrumentController::class, 'getSurveyIndicatorTypes']);
Route::get('/psv-parameters', [SurveyInstrumentController::class, 'getPsvParameters']);
Route::get('/survey-instruments' ,[SurveyInstrumentController::class, 'getSurveyInstruments']);
Route::get('/compliance-reports', [SurveyInstrumentController::class, 'getComplianceReports']);