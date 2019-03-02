import { Component, OnInit } from '@angular/core';
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
  chart4:any;
  constructor() { }

  ngOnInit() {
    // this.getChart()
  }
  


}
