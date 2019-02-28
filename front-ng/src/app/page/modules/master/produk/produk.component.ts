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

  formGroup: FormGroup;
  displayDialog: boolean;
  dataSource: any;
  now: any = new Date;
  listDetailJenisProduk: SelectItem[];
  listSatuanStandard: SelectItem[];
  listAlamat: SelectItem[];
  constructor(private alertService: AlertService,
    private InfoService: InfoService,
    private httpService: HttpClient,
    private confirmationService: ConfirmationService,
    private dataHandler: DataHandler,
    private fb: FormBuilder,
    private loader: LoaderService
  ) { }


  ngOnInit() {
    this.getComboDetailJenisProduk()
    this.getComboSatuanStandard()
    this.formGroup = this.fb.group({
      'id': new FormControl(null),
      'namaProduk': new FormControl(null),
      'kdExternal': new FormControl(null),
      'detailJenisProduk': new FormControl(null),
      'satuanStandard': new FormControl(null),
      'statusEnabled': new FormControl(null),
    });

    this.getData()
  }

  showDialogToAdd() {

    this.resetForm()
    this.displayDialog = true;
  }
  getComboSatuanStandard() {
    this.httpService.get('master/satuanstandar/get').subscribe(data => {
      var getData: any = this.dataHandler.get(data);
      this.listSatuanStandard = [];
      this.listSatuanStandard.push({ label: '--Pilih--', value: '' });
      getData.forEach(response => {
        this.listSatuanStandard.push({ label: response.satuanstandard, value: response.id });
      });
    }, error => {
      this.alertService.error('Error', JSON.stringify(error));
    });
  }
  getComboDetailJenisProduk() {
    this.httpService.get('master/detailjenisproduk/get').subscribe(data => {
      var getData: any = this.dataHandler.get(data);
      this.listDetailJenisProduk = [];
      this.listDetailJenisProduk.push({ label: '--Pilih--', value: '' });
      // 
      getData.forEach(response => {
        this.listDetailJenisProduk.push({ label: response.detailjenisproduk, value: response.id });
      });
    }, error => {
      this.alertService.error('Error', JSON.stringify(error));
    });
  }
  getData() {
    this.httpService.get('master/produk/get-master-produk').subscribe(data => {
      // if (data.data.length > 0) {
      //   for (let i = 0; i < data.data.length; i++) {
      //     data.data[i].no = i + 1
      //     if (data.data[i].nohp == null)
      //       data.data[i].nohp = '-'
      //     if (data.data[i].notlp == null)
      //       data.data[i].notlp = '-'
      //     data.data[i].notelp = data.data[i].nohp + ' / ' + data.data[i].notlp
      //   }
      // }
      this.dataSource = data.data
    })

  }
  save() {
    let data = this.formGroup.value
    this.httpService.post('master/produk/save-master-produk', data).subscribe(data => {
      this.getData()
      this.resetForm()
      this.displayDialog = false;
    }, error => {
      this.alertService.error('Error', JSON.stringify(error));
    });

  }
  edit(e) {
    this.formGroup.get('id').setValue(e.id);
    this.formGroup.get('namaProduk').setValue(e.namaproduk);
    this.formGroup.get('kdExternal').setValue(e.kdexternal);
    this.formGroup.get('detailJenisProduk').setValue(e.detailjenisprodukfk);
    this.formGroup.get('satuanStandard').setValue(e.satuanstandardfk);
    this.formGroup.get('statusEnabled').setValue(e.statusenabled);
    this.displayDialog = true;
  }
  hapus(e) {

    let jsonDelete = {
      'id': e.id
    }
    this.confirmationService.confirm({
      message: 'Yakin mau menghapus data?',
      accept: () => {
        this.httpService.post('master/produk/delete-master-produk', jsonDelete).subscribe(data => {
          this.getData()
          this.resetForm()
        }, error => {
          this.alertService.error('Error', JSON.stringify(error));
        });
      }
    })
  }
  resetForm() {
    this.formGroup.get('namaProduk').reset();
    this.formGroup.get('kdExternal').reset();
    this.formGroup.get('detailJenisProduk').reset();
    this.formGroup.get('satuanStandard').reset();
    this.formGroup.get('statusEnabled').reset();
  }
}
