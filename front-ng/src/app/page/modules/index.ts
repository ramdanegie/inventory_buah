
// home
export * from './home/dashboard/dashboard.component';

// master
export * from './master/user-login/user-login.component';
export * from './master/kelompok-user/kelompok-user.component';
export * from './master/pegawai/pegawai.component';
export * from './master/produk/produk.component';
export * from './master/alamat/alamat.component';
export * from './master/customer/customer.component';
export * from './master/detail-jenis-produk/detail-jenis-produk.component';
export * from './master/jenis-kelamin/jenis-kelamin.component';
export * from './master/jenis-produk/jenis-produk.component';
export * from './master/jenis-transaksi/jenis-transaksi.component';
export * from './master/kelompok-produk/kelompok-produk.component';
export * from './master/satuan-standar/satuan-standar.component';
export * from './master/supplier/supplier.component';
export * from './master/toko/toko.component';
export * from './master/kode-generate/kode-generate.component';
export * from './master/map-produk-to-satuan-standar/map-produk-to-satuan-standar.component';
// transaksi
export * from './transaksi/penerimaan-barang-supplier/penerimaan-barang-supplier.component';
export * from './transaksi/daftar-penerimaan-barang-supplier/daftar-penerimaan-barang-supplier.component';
export * from './transaksi/transaksi-penjualan/transaksi-penjualan.component';
export * from './transaksi/daftar-penjualan/daftar-penjualan.component';
export * from './transaksi/stok-barang/stok-barang.component';
export * from './transaksi/penerimaan-barang-fix/penerimaan-barang-fix.component';
export * from './transaksi/retur-penjualan/retur-penjualan.component';

// not found
export * from './404/not-found.component';
import * as component from './';
var Cmp = [];
for (var key in component) {
  if (component[key]) {
    if (key.indexOf('Component') > 0) {
      Cmp.push(component[key]);
    }
  }
}

import * as service from './';
var Srv = [];
for (var k in service) {
  if (service[k]) {
    if (k.indexOf('Service') > 0) {
      Srv.push(service[k]);
    }
  }
}


import { pathMaster } from './path';
export const routerModule = pathMaster;
export const ComponentMaster = Cmp;
export const ServiceMaster = Srv;
