import { Component, Inject, forwardRef } from '@angular/core';
import { AppComponent } from './app.component';
import { Authentication, AuthGuard, InfoService } from './helper';
import { Router } from '@angular/router';

@Component({
    selector: 'app-topbar',
    templateUrl: './app.topbar.component.html',
    styles: ['body .ui-inputtext {' +
        ' font-size: 16px'
        + '}']
})
export class AppTopBarComponent {
    dateNow: any = new Date()
    jamSekarang:any;
    apiTimer: any;
    constructor(@Inject(forwardRef(() => AppComponent)) public app: AppComponent,
        private auth: Authentication,
        private authGuard: AuthGuard,
        private info: InfoService,
        private router: Router) {
        this.apiTimer = setInterval(() => {
            this.getdate()
        }, (1000)); //1 second
    }

    logout(event: Event) {
        this.auth.logout(this.authGuard, this.info, this.router);
        event.preventDefault();
    }
    
    getdate() {
        var today = new Date();
        var h: any = today.getHours();
        var m: any = today.getMinutes();
        var s: any = today.getSeconds();
        if (h < 10) {
            h = "0" + h;
        }
        if (m < 10) {
            m = "0" + m;
        }
        if (s < 10) {
            s = "0" + s;
        }

        var months: any = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        var myDays: any = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        var date: any = new Date();
        var day: any = date.getDate();
        var month: any = date.getMonth();
        var thisDay: any = date.getDay(),
            thisDay = myDays[thisDay];
        var yy: any = date.getYear();
        var year = (yy < 1000) ? yy + 1900 : yy;

        var tgl = ("Hari : " + thisDay + ', ' + day + ' ' + months[month] + ' ' + year);
        var jam = (h + ":" + m + ":" + s + " wib");
        // $("#timer").html(tgl + ' ' + jam);
        // setTimeout(function () {this.getdate() }, 1000);
        var el: HTMLElement = document.getElementById('timer');

        this.jamSekarang = new Date()//tgl + ' ' + jam
        // console.log(this.jamSekarang)
    }
}
