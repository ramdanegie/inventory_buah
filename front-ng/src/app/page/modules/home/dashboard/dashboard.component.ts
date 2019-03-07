import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
//import { Chart, MapChart, HIGHCHARTS_MODULES } from 'angular-highcharts';
//import * as Highcharts from 'highcharts';
@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss']
})
export class DashboardComponent implements OnInit {
  chart1: any;
  chart2: any;
  chart3: any;
  chart4: any;

  jmlPenjualan: any = 0;
  persenJual: any = 0;
  persenTerima: any = 0;
  jmlPenerimaan: any = 0;
  jmlPegawai: any = 0;
  jmlUser: any = 0;
  constructor(
    private httpService: HttpClient
  ) { }

  ngOnInit() {
    this.getData()

  }
  getData() {
    this.httpService.get('dashboard/count').subscribe(e => {
      this.jmlPenjualan = this.formatRupiah(e.penjualan, 'Rp. ');
      this.jmlPenerimaan = this.formatRupiah(e.penerimaan, 'Rp. ');
      this.persenJual = e.persenPenjualan
      this.persenTerima = e.persenPenerimaan
      this.jmlPegawai = e.pegawai
      this.jmlUser = e.uses
    }, error => {

    })
  }
  formatRupiah(value, currency) {
    return currency + "" + parseFloat(value).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
  }


}
