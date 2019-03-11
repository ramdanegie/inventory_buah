import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
import { Observable } from 'rxjs/Rx';
import { LazyLoadEvent, Message, ConfirmDialogModule, ConfirmationService, SelectItem } from 'primeng/primeng';
import { AlertService, InfoService, Configuration, LoaderService, CacheService } from '../../../../helper';
import { FormBuilder, FormGroup, FormControl } from '@angular/forms';
import { Router } from '@angular/router';

@Component({
  selector: 'app-setoran-debit-kredit',
  templateUrl: './setoran-debit-kredit.component.html',
  styleUrls: ['./setoran-debit-kredit.component.scss'],
  providers: [ConfirmationService]
})
export class SetoranDebitKreditComponent implements OnInit {

  formGroup: FormGroup;
	now = new Date()
	dataSource: any[];
	loading: boolean = false
	listPegawai: SelectItem[]
	selectedItem: any;
  constructor(private alertService: AlertService,
		private InfoService: InfoService,
		private httpService: HttpClient,
		private confirmationService: ConfirmationService,
		private dataHandler: DataHandler,
		private fb: FormBuilder,
		private loader: LoaderService,
		private router: Router,
		private cacheHelper: CacheService) { }

  ngOnInit() {
    this.formGroup = this.fb.group({
			'noPenerimaan': new FormControl(null),
			'noFaktur': new FormControl(null),
			'namaSupplier': new FormControl(null),
			'kdPegawai': new FormControl(null),
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
	formatDateFull(value) {
		if (value == null || value == undefined) {
			return null
		} else {
			let date = new Date(value)
			let hari = ("0" + date.getDate()).slice(-2)
			let bulan = ("0" + (date.getMonth() + 1)).slice(-2)
			let tahun = date.getFullYear()
			let h = ("0" + date.getHours()).slice(-2)
			let m = ("0" + date.getMinutes()).slice(-2)
			let s = date.getSeconds()

			let format = tahun + '-' + bulan + '-' + hari + ' '
				+ h + ':' + m
			return format
		}
	}

}
