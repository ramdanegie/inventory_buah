<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


//header('Access-Control-Allow-Origin: *');
//header('Access-Control-Allow-Methods: *');
//header('Access-Control-Max-Age: 3600');


$router->get('/versi', function () use ($router) {
    return $router->app->version();
});

$router->get('/', function () use ($router) {
	return 'Uhuy Berhasil';
});
$router->group(['prefix' => 'service'/*, 'middleware' => 'auth'*/], function ($app) {

	/* Example */
	$app->group(['prefix' => 'master/'/*, 'middleware' => 'auth2'*/], function ($app) {
		/** Agama */
		$app->get('agama/get-agama', 'ExampleController@getAgama');

		/** Kelompok User*/
		$app->get('kelompokuser/get-all', 'Master\KelompokUserController@getAll');

		/** Pegawai*/
		$app->get('pegawai/get-pegawai-by-nama/{nama}', 'Master\PegawaiController@getPegawaiByNama');

		/** Pegawai*/
		$app->get('loginuser/get-daftar-login-user', 'Master\LoginUserController@getDaftarLoginUser');
		$app->post('loginuser/save-login-user', 'Master\LoginUserController@saveLoginUser');
		$app->post('loginuser/delete-login-user', 'Master\LoginUserController@deleteLoginUser');

		/** Master*/
		$app->get('pegawai/get-daftar-pegawai', 'Master\MasterController@getDaftarPegawai');
		$app->post('pegawai/save-pegawai', 'Master\MasterController@savePegawai');
		$app->get('get-combo', 'Master\MasterController@getCombo');
		$app->post('pegawai/delete-pegawai', 'Master\MasterController@deletePegawai');


	});

	/* Master */
	$app->group(['prefix' => 'setting/','middleware' => 'auth2'], function ($app) {
		$app->get('menu','Core\MenuController@getMenu');
		$app->get('profile/{KdProfile}','Core\MenuController@profile');
	});

    /* Auth */
    $app->group(['prefix' => 'auth/'], function ($app) {
        $app->post('sign-in', 'Core\LoginController@signIn');
	    $app->post('sign-out', 'Core\LoginController@signOut');
	    $app->get('change-password', 'Core\LoginController@changePassword');
    });

});

