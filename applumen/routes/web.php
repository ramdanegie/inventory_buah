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
		/** AgamaM */
		$app->get('agama/get-agama', 'ExampleController@getAgama');

		/** Alamat*/
		$app->get('alamat/get', 'Master\AlamatController@getAlamat');
		$app->post('alamat/save', 'Master\AlamatController@saveAlamat');
		$app->post('alamat/delete', 'Master\AlamatController@deleteAlamat');

		/** Customer*/
		$app->get('customer/get', 'Master\CustomerController@get');
		$app->post('customer/save', 'Master\CustomerController@save');
		$app->post('customer/delete', 'Master\CustomerController@delete');


		/** Kelompok User*/
		$app->get('kelompokuser/get-all', 'Master\KelompokUserController@getAll');
		$app->post('kelompokuser/save-kelompokuser', 'Master\KelompokUserController@saveKelompokUser');
		$app->post('kelompokuser/delete-kelompokuser', 'Master\KelompokUserController@deleteKelompokUser');

		/** PegawaiM*/
		$app->get('pegawai/get-pegawai-by-nama/{nama}', 'Master\PegawaiController@getPegawaiByNama');

		/** PegawaiM*/
		$app->get('loginuser/get-daftar-login-user', 'Master\LoginUserController@getDaftarLoginUser');
		$app->post('loginuser/save-login-user', 'Master\LoginUserController@saveLoginUser');
		$app->post('loginuser/delete-login-user', 'Master\LoginUserController@deleteLoginUser');

		/** Master*/
		$app->get('pegawai/get-daftar-pegawai', 'Master\MasterController@getDaftarPegawai');
		$app->post('pegawai/save-pegawai', 'Master\MasterController@savePegawai');
		$app->get('get-combo', 'Master\MasterController@getCombo');
		$app->post('pegawai/delete-pegawai', 'Master\MasterController@deletePegawai');


	});

	$app->group(['prefix' => 'transaksi/'/*, 'middleware' => 'auth2'*/], function ($app) {
		/** Penerimaan Barang */
		$app->get('penerimaan/get-list-data', 'Transaksi\PenerimaanBarangController@getListCombo');
		$app->post('penerimaan/save-penerimaan', 'Transaksi\PenerimaanBarangController@savePenerimaan');
		$app->get('penerimaan/get-daftar-penerimaan', 'Transaksi\PenerimaanBarangController@getDaftarPenerimaanSuplier');
		$app->post('penerimaan/delete-penerimaan', 'Transaksi\PenerimaanBarangController@hapusPenerimaan');
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

