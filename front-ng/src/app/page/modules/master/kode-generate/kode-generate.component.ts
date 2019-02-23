import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
import { Observable } from 'rxjs/Rx';
import { LazyLoadEvent, Message, ConfirmDialogModule, ConfirmationService, SelectItem } from 'primeng/primeng';
import { AlertService, InfoService, Configuration, LoaderService } from '../../../../helper';
import { FormBuilder, FormGroup, FormControl } from '@angular/forms';
@Component({
  selector: 'app-kode-generate',
  templateUrl: './kode-generate.component.html',
  styleUrls: ['./kode-generate.component.scss'],
  providers: [ConfirmationService]
})
export class KodeGenerateComponent implements OnInit {

  formGroup: FormGroup;
  displayDialog: boolean;
  dataSource: any;
  now: any = new Date;
  listJenisProduk: SelectItem[];
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
      'idKode': new FormControl(null),
      'jenisKode': new FormControl(null),
      'format': new FormControl(null),
    });
    // this.getCombo()
    this.getData()
  }
  getCombo(){
    this.httpService.get('master/jenisproduk/get').subscribe(data => {
			var getData: any = this.dataHandler.get(data);
			this.listJenisProduk = [];
			this.listJenisProduk.push({ label: '--Pilih Jenis Produk --', value: null });
			getData.forEach(response => {
				this.listJenisProduk.push({ label: response.jenisproduk, value: response.id });
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
    this.httpService.get('master/kodegenerate/get').subscribe(data => {
      this.dataSource = data.data
    })

  }
  save() {
    let data = this.formGroup.value
    if(!data.format){
      this.alertService.warn('Peringatan','Format Harus di isi')
      return
    }
    if(!data.jenisKode){
      this.alertService.warn('Peringatan','Jenis Kode Harus di isi')
      return
    }
    this.httpService.post('master/kodegenerate/save', data).subscribe(data => {
      this.getData()
      this.resetForm()
    }, error => {
      this.alertService.error('Error', JSON.stringify(error));
    });

  }
  edit(e) {
    this.formGroup.get('idKode').setValue(e.id);
    this.formGroup.get('format').setValue(e.format);
    this.formGroup.get('jenisKode').setValue(e.jeniskode);
    this.displayDialog = true;
  }
  hapus(e) {
    let jsonDelete = {
      'idKode': e.id
    }
    this.confirmationService.confirm({
      message: 'Yakin mau menghapus data?',
      accept: () => {
        this.httpService.post('master/kodegenerate/delete', jsonDelete).subscribe(data => {
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

