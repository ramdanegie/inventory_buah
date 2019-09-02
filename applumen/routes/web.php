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
	$app->get('dashboard/count', 'Transaksi\DashboardController@countData');
	$app->get('dashboard/get-trend-pendapatan', 'Transaksi\DashboardController@trend');

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

		/** Detail Jenis Produk*/
		$app->get('detailjenisproduk/get', 'Master\DetailJenisProdukController@get');
		$app->post('detailjenisproduk/save', 'Master\DetailJenisProdukController@save');
		$app->post('detailjenisproduk/delete', 'Master\DetailJenisProdukController@delete');

		/** Jenis Kelamin*/
		$app->get('jeniskelamin/get', 'Master\JenisKelaminController@get');
		$app->post('jeniskelamin/save', 'Master\JenisKelaminController@save');
		$app->post('jeniskelamin/delete', 'Master\JenisKelaminController@delete');

		/**  Jenis Produk*/
		$app->get('jenisproduk/get', 'Master\JenisProdukController@get');
		$app->post('jenisproduk/save', 'Master\JenisProdukController@save');
		$app->post('jenisproduk/delete', 'Master\JenisProdukController@delete');

		/**  Jenis Transaksi*/
		$app->get('jenistransaksi/get', 'Master\JenisTransaksiController@get');
		$app->post('jenistransaksi/save', 'Master\JenisTransaksiController@save');
		$app->post('jenistransaksi/delete', 'Master\JenisTransaksiController@delete');

		/**  Kelompok Produk*/
		$app->get('kelompokproduk/get', 'Master\KelompokProdukController@get');
		$app->post('kelompokproduk/save', 'Master\KelompokProdukController@save');
		$app->post('kelompokproduk/delete', 'Master\KelompokProdukController@delete');

		/**  Kode Generatye */
		$app->get('kodegenerate/get', 'Master\KodeGenerateController@get');
		$app->post('kodegenerate/save', 'Master\KodeGenerateController@save');
		$app->post('kodegenerate/delete', 'Master\KodeGenerateController@delete');

		/** Kelompok User*/
		$app->get('kelompokuser/get-all', 'Master\KelompokUserController@getAll');
		$app->post('kelompokuser/save-kelompokuser', 'Master\KelompokUserController@saveKelompokUser');
		$app->post('kelompokuser/delete-kelompokuser', 'Master\KelompokUserController@deleteKelompokUser');

		/** Map Satuan*/
		$app->get('mapproduktosatuan/get', 'Master\MapProdukToSatuanController@getMapSatuan');
		$app->post('mapproduktosatuan/save', 'Master\MapProdukToSatuanController@saveMapSatuan');
		$app->post('mapproduktosatuan/delete', 'Master\MapProdukToSatuanController@deleteMapping');

		/** PegawaiM*/
		$app->get('pegawai/get-pegawai-by-nama/{nama}', 'Master\PegawaiController@getPegawaiByNama');

		/** PegawaiM*/
		$app->get('loginuser/get-daftar-login-user', 'Master\LoginUserController@getDaftarLoginUser');
		$app->post('loginuser/save-login-user', 'Master\LoginUserController@saveLoginUser');
		$app->post('loginuser/delete-login-user', 'Master\LoginUserController@deleteLoginUser');

        /** Produk*/
        $app->get('produk/get-master-produk', 'Master\MasterController@getMasterProduk');
        $app->post('produk/save-master-produk', 'Master\MasterController@saveMasterProduk');
        $app->post('produk/delete-master-produk', 'Master\MasterController@deleteProduk');

		/** Master*/
		$app->get('pegawai/get-daftar-pegawai', 'Master\MasterController@getDaftarPegawai');
		$app->post('pegawai/save-pegawai', 'Master\MasterController@savePegawai');
		$app->get('get-combo', 'Master\MasterController@getCombo');
		$app->post('pegawai/delete-pegawai', 'Master\MasterController@deletePegawai');

		/**  Satuan*/
		$app->get('satuanstandar/get', 'Master\SatuanStandarController@get');
		$app->post('satuanstandar/save', 'Master\SatuanStandarController@save');
		$app->post('satuanstandar/delete', 'Master\SatuanStandarController@delete');

		/**  Supplier*/
		$app->get('supplier/get', 'Master\SupplierController@get');
		$app->post('supplier/save', 'Master\SupplierController@save');
		$app->post('supplier/delete', 'Master\SupplierController@delete');

		/**  Toko*/
		$app->get('toko/get', 'Master\TokoController@get');
		$app->post('toko/save', 'Master\TokoController@save');
		$app->post('toko/delete', 'Master\TokoController@delete');

		$app->get('print/tes', 'Master\PrintController@print');
		$app->get('print/tes2', 'Master\PrintController@displayReport');
		$app->get('print/tes3', 'Master\PrintController@pdf2');
		$app->get('print/tes4', 'Master\PrintController@pdf3');
	});

	$app->group(['prefix' => 'transaksi/'/*, 'middleware' => 'authentication']*/],function ($app) {
		/** Penerimaan Barang */
		$app->get('penerimaan/get-list-data', 'Transaksi\PenerimaanBarangController@getListCombo');
		$app->post('penerimaan/save-penerimaan', 'Transaksi\PenerimaanBarangController@savePenerimaan');
		$app->get('penerimaan/get-daftar-penerimaan', 'Transaksi\PenerimaanBarangController@getDaftarPenerimaanSuplier');
		$app->post('penerimaan/delete-penerimaan', 'Transaksi\PenerimaanBarangController@hapusPenerimaan');
		$app->get('penerimaan/get-penerimaan-ada-stok', 'Transaksi\PenerimaanBarangController@getPenerimaanAvailableStok');
        $app->get('penerimaan/get-ttl-penerimaan', 'Transaksi\PenerimaanBarangController@getTtlTransaksi');

		/** Penjualan*/
		$app->get('penjualan/get-stok-produk', 'Transaksi\PenjualanController@getStokProduk');
		$app->post('penjualan/save-penjualan', 'Transaksi\PenjualanController@savePenjualan');
		$app->get('penjualan/get-penjualan', 'Transaksi\PenjualanController@getDaftarPenjualan');
		$app->post('penjualan/delete-penjualan', 'Transaksi\PenjualanController@hapusPenjualan');
		$app->get('penjualan/get-stok-produk-by-noterima', 'Transaksi\PenjualanController@getStokByNoterima');

		/** Stok Produk*/
		$app->get('stokproduk/get-combo', 'Transaksi\StokProdukController@getCombo');
		$app->get('stokproduk/get-stok', 'Transaksi\StokProdukController@getStokProduk');
		$app->post('stokproduk/update-harga', 'Transaksi\StokProdukController@updateHarga');

        /** Penerimaan Debit Kredit*/
        $app->get('penerimaandebitkredit/get-combo', 'Transaksi\SetoranDebitKreditController@getCombo');

        /** Setoran Penjualan*/
        $app->get('setoranpenjualan/get-data-closing', 'Transaksi\SetoranPenjualanController@getDataClosing');

        /** Penerimaan Kasir*/
        $app->get('penerimaankasir/get-penetimaan-kasir', 'Transaksi\PenerimaanKasirController@getPenerimaanKasir');
        $app->get('penerimaankasir/get-combo', 'Transaksi\PenerimaanKasirController@getCombo');
        $app->post('penerimaankasir/simpan-setoran', 'Transaksi\PenerimaanKasirController@saveSetoran');
        $app->post('penerimaankasir/save-closing', 'Transaksi\PenerimaanKasirController@saveClosing');

		/** Dashboard*/

		/** Pembyarana */
		$app->get('pembayaran/get-combo', 'Transaksi\StrukPembayaranController@getCombo');
        $app->get('pembayaran/get-penerimaan-kasir', 'Transaksi\StrukPembayaranController@getPenerimaanKasir');
		// $app->get('pembayaran/save-pembayaran', 'Transaksi\StrukPembayaranController@savePembayaran');

		$app->post('pembayaran/save-pembayaran', 'Transaksi\StrukPembayaranController@savePembayaran');
		$app->get('pembayaran/get-bayar-by-no', 'Transaksi\StrukPembayaranController@getPembayaranByNoBayar');
		$app->get('pembayaran/get-bayar-penerimaan-by-no', 'Transaksi\StrukPembayaranController@getPembayaranByNoBayarPenerimaan');
        $app->post('pembayaran/batal-bayar', 'Transaksi\StrukPembayaranController@batalPemabayaran');

		$app->get('setoran/get-combo', 'Transaksi\SetoranDebitKreditController@getCombo2');
		$app->post('setoran/save-setoran-manual', 'Transaksi\SetoranDebitKreditController@saveSetoranManual');
		$app->get('setoran/get-daftar-setor', 'Transaksi\SetoranDebitKreditController@getDaftarSetoran');
		$app->post('setoran/hapus-setoran', 'Transaksi\SetoranDebitKreditController@hapusSetoran');
        $app->post('setoran/save-setoran-dari-closing', 'Transaksi\SetoranDebitKreditController@saveSetoranDariClosing');
		/** Laporan */
		$app->get('laporan/get-detail-terimabarang', 'Transaksi\LaporanController@getDaftarDetailTerimaBarang');
	});

	/* Master */
	$app->group(['prefix' => 'generic/'], function ($app) {
		$app->get('get-terbilang/{number}', 'Transaksi\StrukPembayaranController@getTerbilangsss');
	});
	/* Master */
	$app->group(['prefix' => 'setting/','middleware' => 'authentication'], function ($app) {
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

