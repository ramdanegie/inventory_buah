import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
import { Observable } from 'rxjs/Rx';
import { LazyLoadEvent, Message, ConfirmDialogModule, ConfirmationService, SelectItem } from 'primeng/primeng';
import { AlertService, InfoService, Configuration, LoaderService } from '../../../../helper';
import { FormBuilder, FormGroup, FormControl } from '@angular/forms';


@Component({
  selector: 'app-jenis-produk',
  templateUrl: './jenis-produk.component.html',
  styleUrls: ['./jenis-produk.component.scss'],
  providers: [ConfirmationService]
})
export class JenisProdukComponent implements OnInit {

 
  formGroup: FormGroup;
  displayDialog: boolean;
  dataSource: any;
  now: any = new Date;
  listKelompokProduk: SelectItem[];
  constructor(private alertService: AlertService,
    private InfoService: InfoService,
    private httpService: HttpClient,
    private confirmationService: ConfirmationService,
    private dataHandler: DataHandler,
    private fb: FormBuilder,
    private loader: LoaderService
  ) { }


  ngOnInit() {
    this.formGroup = this.fb.group({
      'idJenisProduk': new FormControl(null),
      'jenisProduk': new FormControl(null),
      'kdKelompokProduk': new FormControl(null),
  
    });
    this.getCombo()
    this.getData()
  }
  getCombo(){
    this.httpService.get('master/kelompokproduk/get').subscribe(data => {
			var getData: any = this.dataHandler.get(data);
			this.listKelompokProduk = [];
			this.listKelompokProduk.push({ label: '--Pilih Kelompok Produk --', value: null });
			getData.forEach(response => {
				this.listKelompokProduk.push({ label: response.kelompokproduk, value: response.id });
			});

		}, error => {
			this.alertService.error('Error', 'Terjadi kesalahan saat loading data');
    })
  }

  showDialogToAdd() {
    this.resetForm()
    this.displayDialog = true;
  }

  getData() {
    this.httpService.get('master/jenisproduk/get').subscribe(data => {
      this.dataSource = data.data
    })

  }
  save() {
    let data = this.formGroup.value
    this.httpService.post('master/jenisproduk/save', data).subscribe(data => {
      this.getData()
      this.resetForm()
    }, error => {
      this.alertService.error('Error', JSON.stringify(error));
    });

  }
  edit(e) {
    this.formGroup.get('idJenisProduk').setValue(e.id);
    this.formGroup.get('jenisProduk').setValue(e.jenisproduk);
    this.formGroup.get('kdKelompokProduk').setValue(e.kelompokprodukfk);
    this.displayDialog = true;
  }
  hapus(e) {
    let jsonDelete = {
      'idJenisProduk': e.id
    }
    this.confirmationService.confirm({
      message: 'Yakin mau menghapus data?',
      accept: () => {
        this.httpService.post('master/jenisproduk/delete', jsonDelete).subscribe(data => {
          this.getData()
          this.resetForm()
        }, error => {
          this.alertService.error('Error', JSON.stringify(error));
        });
      }
    })
  }
  resetForm() {
    this.formGroup.reset()
  }
}

