import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
import { Observable } from 'rxjs/Rx';
import { LazyLoadEvent, Message, ConfirmDialogModule, ConfirmationService, SelectItem } from 'primeng/primeng';
import { AlertService, InfoService, Configuration, LoaderService } from '../../../../helper';
import { FormBuilder, FormGroup, FormControl } from '@angular/forms';
@Component({
  selector: 'app-produk',
  templateUrl: './produk.component.html',
  styleUrls: ['./produk.component.scss'],
  providers: [ConfirmationService]
})
export class ProdukComponent implements OnInit {
  formGroup:FormGroup

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
      'idPegawai': new FormControl(null),
      'namaLengkap': new FormControl(null),
      'namaPanggilan': new FormControl(null),
      'noHp': new FormControl(null),
      'noTlp': new FormControl(null),
      'kdJenisKelamin': new FormControl(null),
      'kdAlamat': new FormControl(null),
      'tglLahir': new FormControl(null),
    });

  }

}
