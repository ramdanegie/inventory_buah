import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
import { Observable } from 'rxjs/Rx';
import { LazyLoadEvent, Message, ConfirmDialogModule, ConfirmationService, SelectItem } from 'primeng/primeng';
import { AlertService, InfoService, Configuration, LoaderService } from '../../../../helper';
import { FormBuilder, FormGroup, FormControl } from '@angular/forms';

@Component({
  selector: 'app-satuan-standar',
  templateUrl: './satuan-standar.component.html',
  styleUrls: ['./satuan-standar.component.scss'],
  providers: [ConfirmationService]
})
export class SatuanStandarComponent implements OnInit {

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
      'idSatuanStandar': new FormControl(null),
      'satuanStandar': new FormControl(null),

    });
    // this.getCombo()
    this.getData()
  }
  getCombo() {
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
    this.httpService.get('master/satuanstandar/get').subscribe(data => {
      this.dataSource = data.data
    })

  }
  save() {
    let data = this.formGroup.value
    this.httpService.post('master/satuanstandar/save', data).subscribe(data => {
      this.getData()
      this.resetForm()
    }, error => {
      this.alertService.error('Error', JSON.stringify(error));
    });

  }
  edit(e) {
    this.formGroup.get('idSatuanStandar').setValue(e.id);
    this.formGroup.get('satuanStandar').setValue(e.satuanstandard);
    // this.formGroup.get('kdJenisProduk').setValue(e.jenisprodukfk);
    this.displayDialog = true;
  }
  hapus(e) {
    let jsonDelete = {
      'idSatuanStandar': e.id
    }
    this.confirmationService.confirm({
      message: 'Yakin mau menghapus data?',
      accept: () => {
        this.httpService.post('master/satuanstandar/delete', jsonDelete).subscribe(data => {
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

