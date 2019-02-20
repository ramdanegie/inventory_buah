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
    // END MASTER
    
    // TRANSAKSI
    { canActivate: [AuthGuard], path: 'penerimaan-barang-supplier', component: pMaster.PenerimaanBarangSupplierComponent },
    { canActivate: [AuthGuard], path: 'daftar-penerimaan-barang-supplier', component: pMaster.DaftarPenerimaanBarangSupplierComponent },
    // END TRANSAKSI
    // END TRANSAKSI
    { canActivate: [AuthGuard], path: '404', component: pMaster.NotFoundComponent },
    { canActivate: [AuthGuard], path: '**', redirectTo: '/404' },

];