<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::group(['middleware' => 'auth'], function () 
{

	Route::get('/', function () 
	{
	    return view('dashboard');
	});

	Route::get('profile', function () 
	{
		
	    return view('users/profile');
	});

	// Route::get('login', 'Auth\AuthController@getLogin');
	// Route::post('login', 'Auth\AuthController@postLogin');
	// Route::get('logout', 'Auth\AuthController@getLogout');

	Route::get('profile', 'UserController@profile');

	// // Registration Routes...
	// Route::get('register', 'Auth\AuthController@getRegister');
	// Route::post('register', 'Auth\AuthController@postRegister');

	Route::get('branches/switch/{id}', 'BranchController@switch');
	Route::resource('branches', 'BranchController');

	Route::get('settings/grades', 'SettingController@grades');
	Route::resource('settings', 'SettingController');

	Route::resource('rooms', 'RoomController');
	Route::resource('roles', 'RoleController');
	Route::get('classes/{id}/subjects', 'ClassController@subjects');
	Route::get('classes/{id}/subjects/{subject_id}', 'ClassController@teacher');
	Route::resource('classes', 'ClassController');
	Route::resource('subjects', 'SubjectController');
	Route::resource('programs', 'ProgramController');
	Route::get('users/search', 'UserController@search');
	Route::get('users/{id}/remove-family-member/{member_id}', 'UserController@removeMember');

	Route::resource('users', 'UserController');
	Route::resource('media', 'MediaController');
	Route::resource('schedules', 'ScheduleController');
	Route::resource('attendances', 'AttendanceController');
	Route::resource('grades', 'GradeController');
	Route::resource('transports', 'TransportController');
});

Route::get('/logout', 'Auth\LoginController@logout');

Auth::routes();