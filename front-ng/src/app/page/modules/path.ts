import { AuthGuard } from '../../helper';
import * as pMaster from './';

import * as demo from '../../demo/index';

let session = JSON.parse(localStorage.getItem('user.data'));
export const pathMaster = [

    { canActivate: [AuthGuard], path: '', redirectTo: localStorage.getItem('user.data') != null ? 'dashboard' : 'login', pathMatch: 'full' },
    { canActivate: [AuthGuard], path: 'dashboard', component: demo.DashboardDemoComponent },
    { canActivate: [AuthGuard], path: 'empty', component: demo.EmptyDemoComponent },
    // MASTER
    { canActivate: [AuthGuard], path: 'user-login', component: pMaster.UserLoginComponent },
    { canActivate: [AuthGuard], path: 'kelompok-user', component: pMaster.KelompokUserComponent },
    { canActivate: [AuthGuard], path: 'pegawai', component: pMaster.PegawaiComponent },
    { canActivate: [AuthGuard], path: 'produk', component: pMaster.ProdukComponent },
    { canActivate: [AuthGuard], path: 'alamat', component: pMaster.AlamatComponent },
    { canActivate: [AuthGuard], path: 'customer', component: pMaster.CustomerComponent },
    { canActivate: [AuthGuard], path: 'detail-jenis-produk', component: pMaster.DetailJenisProdukComponent },
    { canActivate: [AuthGuard], path: 'jenis-kelamin', component: pMaster.JenisKelaminComponent },
    { canActivate: [AuthGuard], path: 'jenis-produk', component: pMaster.JenisProdukComponent },
    { canActivate: [AuthGuard], path: 'jenis-transaksi', component: pMaster.JenisTransaksiComponent },
    { canActivate: [AuthGuard], path: 'kelompok-produk', component: pMaster.KelompokProdukComponent },
    { canActivate: [AuthGuard], path: 'satuan-standar', component: pMaster.SatuanStandarComponent },
    { canActivate: [AuthGuard], path: 'supplier', component: pMaster.SupplierComponent },
    { canActivate: [AuthGuard], path: 'toko', component: pMaster.TokoComponent },
    { canActivate: [AuthGuard], path: 'kode-generate', component: pMaster.KodeGenerateComponent },
    { canActivate: [AuthGuard], path: 'map-produk-to-satuan-standar', component: pMaster.MapProdukToSatuanStandarComponent },
    // END MASTER

    // TRANSAKSI
    { canActivate: [AuthGuard], path: 'penerimaan-barang-supplier', component: pMaster.PenerimaanBarangSupplierComponent },
    { canActivate: [AuthGuard], path: 'daftar-penerimaan-barang-supplier', component: pMaster.DaftarPenerimaanBarangSupplierComponent },
    // END TRANSAKSI
    // END TRANSAKSI
    { canActivate: [AuthGuard], path: '404', component: pMaster.NotFoundComponent },
    { canActivate: [AuthGuard], path: '**', redirectTo: '/404' },

];