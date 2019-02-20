import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
import { Observable } from 'rxjs/Rx';
import { LazyLoadEvent, Message, ConfirmDialogModule, ConfirmationService, SelectItem } from 'primeng/primeng';
import { AlertService, InfoService, Configuration, LoaderService } from '../../../../helper';
import { FormBuilder, FormGroup, FormControl } from '@angular/forms';


@Component({
  selector: 'app-daftar-penerimaan-barang-supplier',
  templateUrl: './daftar-penerimaan-barang-supplier.component.html',
  styleUrls: ['./daftar-penerimaan-barang-supplier.component.scss'],
  providers: [ConfirmationService]
})
export class DaftarPenerimaanBarangSupplierComponent implements OnInit {

	formGroup: FormGroup;
  constructor(private alertService: AlertService,
		private InfoService: InfoService,
		private httpService: HttpClient,
		private confirmationService: ConfirmationService,
		private dataHandler: DataHandler,
		private fb: FormBuilder,
		private loader : LoaderService
	) { }


  ngOnInit() {
    this.formGroup = this.fb.group({
			'noPenerimaan': new FormControl(null),
			'noFaktur': new FormControl(null),
			'namaSupplier': new FormControl(null),
			'tglPenerimaan': new FormControl(null),
			'kdToko': new FormControl(null),
      'kdPegawai': new FormControl(null),
      'kdProduk': new FormControl(null),
      'namaProduk': new FormControl(null),
      // 'kdPegawai': new FormControl(null),
      // 'kdPegawai': new FormControl(null),


		});
  }

}
