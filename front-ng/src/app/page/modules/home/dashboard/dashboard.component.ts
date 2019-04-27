import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
import { Chart, MapChart, HIGHCHARTS_MODULES } from 'angular-highcharts';
import * as Highcharts from 'highcharts';
// import { Chart, Ma pChart } from 'angular-highcharts';
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
  colors = Highcharts.getOptions().colors;
  jmlPenjualan: any = 0;
  persenJual: any = 0;
  persenTerima: any = 0;
  jmlPenerimaan: any = 0;
  jmlPegawai: any = 0;
  jmlUser: any = 0;
  dataChartTend: any;
  chartTrendPendapatan: any;
  dataSeries: any;
  isShowTrend: boolean = true
  constructor(
    private httpService: HttpClient
  ) { }

  ngOnInit() {
    this.getData()
    this.getTrendPendapatan()
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
  getTrendPendapatan() {
    let tipe = 'seminggu';

    // tglna di backend
    this.httpService.get('dashboard/get-trend-pendapatan?tipe=' + tipe).subscribe(data => {

      this.dataChartTend = data

      if (this.dataChartTend.data.length > 0)
        this.isShowTrend = false
      let array = this.dataChartTend.data
      let categories = []
      let periodeCatego = []
      // totalkeun hela
      for (let i in array) {
        array[i].tgl = new Date(array[i].tglpencarian).toDateString()//.substring(4, 10)
        array[i].total = parseFloat(array[i].total)
      }
      let samateuuu = false
      let sumKeun = [];
      for (let i in array) {
        samateuuu = false
        for (let x in sumKeun) {
          if (sumKeun[x].tgl == array[i].tgl) {
            sumKeun[x].total = parseFloat(sumKeun[x].total) + parseFloat(array[i].total)
            sumKeun[x].tgl = array[i].tgl
            samateuuu = true;
          }
        }
        if (samateuuu == false) {
          let result = {
            tgl: array[i].tgl,
            total: array[i].total,
          }
          sumKeun.push(result)
        }
      }
      let dataSeries = []
      for (let i in sumKeun) {
        dataSeries.push(sumKeun[i].total
        );
        categories.push(sumKeun[i].tgl.substring(4, 10))
        periodeCatego.push(sumKeun[i].tgl)
      }

      this.dataSeries = [{
        name: 'Trend Penjualan',
        data: dataSeries,
        color: '#00c0ef'
      }]
      //console.log(sumKeun)
      this.chartTrendPendapatan = new Chart({
        chart: {
          type: 'area',
          spacingBottom: 30
        },
        title: {
          text: ''
        },

        subtitle: {
          text: ''
        },
        xAxis: {
          categories: categories,
        },
        yAxis: {
          title: {
            text: 'Jumlah'
          }
        },

        legend: {
          layout: 'vertical',
          align: 'right',
          borderRadius: 5,
          borderWidth: 1,
          verticalAlign: 'middle'
        },
        plotOptions: {
          // area: {
          //     stacking: 'normal',
          //     lineColor: '#666666',
          //     lineWidth: 1,
          //     marker: {
          //         lineWidth: 1,
          //         lineColor: '#666666'
          //     }
          // },
          // line: {
          //     dataLabels: {
          //         enabled: true,
          //         color: this.colors[1],

          //         formatter: function () {
          //             return 'Rp. ' + Highcharts.numberFormat(this.y, 0, '.', ',');
          //         }
          //     },
          //     enableMouseTracking: false
          // },
          area: {
            cursor: 'pointer',
            dataLabels: {
              enabled: true,
              color: this.colors[1],
              formatter: function () {
               
                return 'Rp. ' + Highcharts.numberFormat(this.y, 0, '.', ',');
              }
            },
            showInLegend: true
          },
          series: {
            cursor: 'pointer',
          }
        },
        tooltip: {
          formatter: function () {
            let point = this.point,
              s = this.series.name +' ' +this.x + ': Rp. ' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' <br/>';
            return s;

          }

        },
        // plotOptions: {
        //     series: {
        //         label: {
        //             connectorAllowed: false
        //         },
        //         pointStart: 2010
        //     }
        // },

        series: this.dataSeries,
        credits: {
          enabled: false
        },

        responsive: {
          rules: [{
            condition: {
              maxWidth: 500
            },
            chartOptions: {
              legend: {
                layout: 'horizontal',
                align: 'center',
                verticalAlign: 'bottom'
              }
            }
          }]
        }
      })
    })
  }
  formatRupiah(value, currency) {
    return currency + "" + parseFloat(value).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
  }


}
