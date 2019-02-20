import { Injectable } from '@angular/core';
import { Message } from 'primeng/primeng';
import { AlertService } from '../../helper';

@Injectable()
export class DataHandler {

    public listData: any[];
    public data: any;

    constructor(
        private alertService: AlertService
    ) { }

    get(response: any) {
        if (response.code == '200') {
            this.listData = response.data;
            return this.listData;
        } else {
            this.alertService.error('Gagal Menampilkan Data', JSON.stringify(response.message));
            return null;
        }
    }

    getOne(response: any) {
        if (response.code == '200') {
            this.data = response.data;
            return this.data;
        } else {
            this.alertService.error('Gagal Menampilkan Data', JSON.stringify(response.message));
            return null;
        }
    }

    post(response: any) {
        if (response.status == '201') {
            this.alertService.success('Berhasil Post', 'Sudah Berhasil');
            return true;
        } else {
            // Masih Bermasalah Belum Bisa Ngambil Array
            this.alertService.error('Gagal Melakukan Post', JSON.stringify(response.message));
            return false;
        }
    }

    put(response: any) {
        if (response.status == '201') {
            this.alertService.success('Berhasil Update', 'Data Diperbarui');
            return true;
        } else {
            // Masih Bermasalah Belum Bisa Ngambil Array
            this.alertService.error('Gagal Melakukan Update', "Ada Kesalahan");
            return false;
        }
    }

    delete(response: any) {
        if (response.status == '201' || response.status == '204') {
            this.alertService.success('Berhasil Hapus', 'Data sudah dihapus');
            return true;
        } else {
            // Masih Bermasalah Belum Bisa Ngambil Array
            this.alertService.error('Gagal Melakukan Hapus', "Ada Kesalahan");
            return false;
        }
    }

    error(errorMsg: any) {
        this.alertService.error('Menampilkan Data', JSON.stringify(errorMsg));
    }




}