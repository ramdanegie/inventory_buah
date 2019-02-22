
// not found
export * from './404/not-found.component';
// master
export * from './master/user-login/user-login.component';
export * from './master/kelompok-user/kelompok-user.component';
export * from './master/pegawai/pegawai.component';
export * from './master/produk/produk.component';
export * from './master/alamat/alamat.component';
export * from './master/customer/customer.component';

// transaksi
export * from './transaksi/penerimaan-barang-supplier/penerimaan-barang-supplier.component';
export * from './transaksi/daftar-penerimaan-barang-supplier/daftar-penerimaan-barang-supplier.component';

import * as component from './';
var Cmp = [];
for (var key in component){
  if (component[key]) {
    if (key.indexOf('Component') > 0) {
      Cmp.push(component[key]);
    }
  }
}

import * as service from './';
var Srv = [];
for (var k in service){
  if (service[k]) {
    if (k.indexOf('Service') > 0 ) {
      Srv.push(service[k]);
    }
  }
}


import { pathMaster } from './path';
export const routerModule = pathMaster;
export const ComponentMaster = Cmp;
export const ServiceMaster = Srv;
