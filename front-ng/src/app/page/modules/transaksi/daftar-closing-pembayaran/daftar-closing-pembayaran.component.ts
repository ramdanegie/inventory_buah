import { Component, OnInit } from '@angular/core';
import { ConfirmationService } from 'primeng/primeng';
import { AlertService } from '../../../../demo';
import { InfoService, HttpClient, LoaderService, CacheService, AuthGuard } from '../../../../helper';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { FormBuilder, FormGroup, FormGroupDirective, FormControl } from '@angular/forms';
import { Router } from '@angular/router';

@Component({
  selector: 'app-daftar-closing-pembayaran',
  templateUrl: './daftar-closing-pembayaran.component.html',
  styleUrls: ['./daftar-closing-pembayaran.component.scss'],
  providers: [ConfirmationService]
})
export class DaftarClosingPembayaranComponent implements OnInit {
  formGroup: FormGroup;
  now = new Date()
  constructor(private alertService: AlertService,
		private InfoService: InfoService,
		private httpService: HttpClient,
		private confirmationService: ConfirmationService,
		private dataHandler: DataHandler,
		private fb: FormBuilder,
		private loader: LoaderService,
		private router: Router,
		private cacheHelper: CacheService,
		private authGuard: AuthGuard) { }

  ngOnInit() {
    this.formGroup = this.fb.group({
      'noclosing': new FormControl,
      'tglAwal': new FormControl(new Date(this.formatDate(this.now) + ' 00:00')),
      'tglAkhir': new FormControl(this.now),
    });
  }
  formatDate(value) {
		if (value == null || value == undefined) {
			return null
		} else {
			let date = new Date(value)
			let hari = ("0" + date.getDate()).slice(-2)
			let bulan = ("0" + (date.getMonth() + 1)).slice(-2)
			let tahun = date.getFullYear()
			let format = tahun + '-' + bulan + '-' + hari
			return format
		}
	}
  cari(){

  }

}
