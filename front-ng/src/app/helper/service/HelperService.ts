import { Injectable } from '@angular/core';

@Injectable()
export class HelperService {

    constructor() { }

    // format currency view grid
    public formatCurrency(data) {
        data = data.replace(/.{3}$/, "").replace(/\d{1,3}(?=(\d{3})+(?!\d))/g, "$&.");
        if (data != null && data != "") {
            return data;
        } else {
            return '-';
        }

    }

    // public formatCurrencyPengajuanAnggaran(data) {
    //     console.log(data);
    //     if(data !== null || data !== ""){
    //         if(/\./g.test(data) === true) {
    //             data = data.replace(/\./g,"").replace(/\d{1,3}(?=(\d{3})+(?!\d))/g,"$&.");
    //         } else {
    //             data = data.replace(/\d{1,3}(?=(\d{3})+(?!\d))/g,"$&.");
    //         }
    //         return data;
    //     }
    // }
}