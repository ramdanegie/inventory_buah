import { Component, OnInit } from '@angular/core';
import { SelectItem, ConfirmationService } from 'primeng/primeng';
import { CarService } from '../service/carservice';
import { EventService } from '../service/eventservice';
import { Car } from '../domain/car';
import { Produk } from './produk.interface';
import { FormBuilder } from '@angular/forms';
import { AlertService, InfoService, HttpClient, AuthGuard, FileService } from '../../helper';
import { DataHandler } from '../../helper/handler/DataHandler';
// import * as React from "react";
// import * as PropTypes from 'prop-types';
// import classNames from 'classnames';
// import { createStyles, Theme, withStyles, WithStyles } from '@material-ui/core/styles';
// import MenuItem from '@material-ui/core/MenuItem';
// import TextField from '@material-ui/core/TextField';
import { MDCNotchedOutline } from '@material/notched-outline';
import * as $ from 'jquery';
// $(function () {

//     $('.form-control').each(function () {
//         changeState($(this));
//     });

//     $('.form-control').on('focusout', function () {
//         changeState($(this));
//     });

//     function changeState($formControl) {
//         if ($formControl.val().length > 0) {
//             $formControl.addClass('has-value');
//         }
//         else {
//             $formControl.removeClass('has-value');
//         }
//     }
// })
// const notchedOutline = new MDCNotchedOutline(document.querySelector('.mdc-notched-outline'));
@Component({
    templateUrl: './emptydemo.component.html',
    providers: [ConfirmationService]
})

export class EmptyDemoComponent implements OnInit {
    d_satuan: SelectItem[];
    cities: SelectItem[];
    cars: Car[];
    cols: any[];
    chartData: any;
    events: any[];
    selectedCity: any;
    selectedCar: Car;
    dataTable: Produk[];
    // export
    items: any;
    // data export
    dataExport: any[];
    constructor(
        private carService: CarService,
        private eventService: EventService,
        // private alertService: AlertService,
        //  private InfoService: InfoService,
        //  private httpService: HttpClient,
        //  private dataHandler: DataHandler,
        //  private confirmationService: ConfirmationService,
        //  private AuthGuard:AuthGuard,
        //  private fb: FormBuilder,
        //  private fileService: FileService
    ) { }

    ngOnInit() {
      
        // this.alertService.su ccess('Succes', 'Uhuy');
        this.items = [
            {
                label: 'Pdf', icon: 'fa-file-pdf-o', command: () => {
                    this.downloadPdf();
                }
            },
            {
                label: 'Excel', icon: 'fa-file-excel-o ', command: () => {
                    this.downloadExcel();
                }
            }
        ];
        let satuan = [{ id: 1, nama: 'Kecil' }, { id: 2, nama: 'Besar' }, { id: 3, nama: 'Sedang' }]
        this.d_satuan = [];
        this.d_satuan.push({ label: '--Silahkan Pilih Satuan--', value: '' });
        for (var i = 0; i < satuan.length; i++) {
            this.d_satuan.push({ label: satuan[i].nama, value: satuan[i].id })
        };
        this.carService.getCarsLarge().then(cars => this.cars = cars);



        this.eventService.getEvents().then(events => { this.events = events; });

        this.cities = [];
        this.cities.push({ label: 'Select City', value: null });
        this.cities.push({ label: 'New York', value: { id: 1, name: 'New York', code: 'NY' } });
        this.cities.push({ label: 'Rome', value: { id: 2, name: 'Rome', code: 'RM' } });
        this.cities.push({ label: 'London', value: { id: 3, name: 'London', code: 'LDN' } });
        this.cities.push({ label: 'Istanbul', value: { id: 4, name: 'Istanbul', code: 'IST' } });
        this.cities.push({ label: 'Paris', value: { id: 5, name: 'Paris', code: 'PRS' } });

        this.chartData = {
            labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            datasets: [
                {
                    label: 'First Dataset',
                    data: [65, 59, 80, 81, 56, 55, 40],
                    fill: false,
                    borderColor: '#FFC107'
                },
                {
                    label: 'Second Dataset',
                    data: [28, 48, 40, 19, 86, 27, 90],
                    fill: false,
                    borderColor: '#03A9F4'
                }
            ]
        };
    }
     
    downloadExcel() {
        var status;
        this.dataExport = [];
        for (let abc = 0; abc < this.dataExport.length; abc++) {
            if (this.dataExport[abc].StatusEnabled == 1) {
                status = "Aktif";
            } else {
                status = "Tidak Aktif";
            }

            let tampung = {
                "No.": this.dataExport[abc].No,
                "Nomor Urut": this.dataExport[abc].NoUrut,
                "Visi Head": this.dataExport[abc].VisiHead,
                "Visi": this.dataExport[abc].NamaVisi,
                "Report Display": this.dataExport[abc].ReportDisplay,
                "Kode External": this.dataExport[abc].KodeExternal,
                "Nama External": this.dataExport[abc].NamaExternal,
                "Status": status
            };
            this.checkNullProperties(tampung);
            this.dataExport.push(tampung);
        }
        // this.fileService.exportAsExcelFile(this.dataExport, 'Master Visi ' + this.default_date_string_dokumen(new Date()));
    }
    default_date_string_dokumen(tgl) {
        var date = tgl.getDate();
        var month = tgl.getMonth() + 1;
        var year = tgl.getFullYear();
        var hours = tgl.getHours();
        var minutes = tgl.getMinutes();

        if (parseInt(date) < 10) {
            date = "0" + date;
        }
        if (parseInt(month) < 10) {
            month = "0" + month;
        }
        let abc = date + '-' + month + '-' + year;
        return abc
    }

    downloadPdf() {
        var status;
        this.dataExport = [];
        for (let abc = 0; abc < this.dataExport.length; abc++) {
            if (this.dataExport[abc].StatusEnabled == 1) {
                status = "Aktif";
            } else {
                status = "Tidak Aktif";
            }
            let tampung = {
                "No.": this.dataExport[abc].No,
                "Nomor Urut": this.dataExport[abc].NoUrut,
                "Visi Head": this.dataExport[abc].VisiHead,
                "Visi": this.dataExport[abc].NamaVisi,
                "Report Display": this.dataExport[abc].ReportDisplay,
                "Kode External": this.dataExport[abc].KodeExternal,
                "Nama External": this.dataExport[abc].NamaExternal,
                "Status": status
            };
            this.checkNullProperties(tampung);
            this.dataExport.push(tampung);
        }

        // this.fileService.exportAsPdfFile("Master Visi", this.dataExport, 'Master Visi ' + this.default_date_string_dokumen(new Date()));
    }
    checkNullProperties(obj) {
        for (var key in obj) {
            if (obj[key] === null) {
                obj[key] = "";
            }
        }
    }
}
