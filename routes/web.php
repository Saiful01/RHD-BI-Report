<?php

use App\Http\Controllers\Admin\TenderController;
use App\Http\Controllers\Admin\TenderItemController;



Route::redirect('/', '/login');
Route::get('/home', function () {
    if (session('status')) {
        return redirect()->route('admin.home')->with('status', session('status'));
    }

    return redirect()->route('admin.home');
});

Auth::routes(['register' => false]);

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth']], function () {
    Route::get('/', 'HomeController@index')->name('home');
    // Permissions
    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');
    Route::resource('permissions', 'PermissionsController');

    // Roles
    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');
    Route::resource('roles', 'RolesController');

    // Users
    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');
    Route::resource('users', 'UsersController');

    // Station
    Route::delete('stations/destroy', 'StationController@massDestroy')->name('stations.massDestroy');
    Route::resource('stations', 'StationController');

    // Daily Weather
    Route::delete('daily-weathers/destroy', 'DailyWeatherController@massDestroy')->name('daily-weathers.massDestroy');
    Route::resource('daily-weathers', 'DailyWeatherController');

    // Weather Report
    Route::delete('weather-reports/destroy', 'WeatherReportController@massDestroy')->name('weather-reports.massDestroy');
    Route::resource('weather-reports', 'WeatherReportController');


    Route::resource('tender', TenderController::class);

    Route::get('/tender/{tender}/items', [TenderController::class, 'viewItems'])->name('tender.viewItems');

    Route::get('tender-select-search', [TenderController::class, 'selectSearch'])->name('tender.selectSearch');

    Route::get('tender-item-select-search', [TenderItemController::class, 'tenderSearch'])->name('tender.item.selectSearch');
    Route::get('supplier-search', [TenderItemController::class, 'supplierSearch'])->name('supplier-search');
    Route::get('item-code-search', [TenderItemController::class, 'itemCodeSearch'])->name('item-code-search');
    Route::get('item-name-search', [TenderItemController::class, 'itemNameSearch'])->name('item-name-search');

    Route::resource('tender-item', TenderItemController::class);


    Route::get('tender-item-summary', [TenderItemController::class, 'summeryReport'])->name('tender-item.summeryReport');

});


Route::group(['prefix' => 'profile', 'as' => 'profile.', 'namespace' => 'Auth', 'middleware' => ['auth']], function () {
    // Change password
    if (file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php'))) {
        Route::get('password', 'ChangePasswordController@edit')->name('password.edit');
        Route::post('password', 'ChangePasswordController@update')->name('password.update');
        Route::post('profile', 'ChangePasswordController@updateProfile')->name('password.updateProfile');
        Route::post('profile/destroy', 'ChangePasswordController@destroy')->name('password.destroyProfile');
    }
});
