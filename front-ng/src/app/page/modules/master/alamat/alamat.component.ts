import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
import { Observable } from 'rxjs/Rx';
import { LazyLoadEvent, Message, ConfirmDialogModule, ConfirmationService, SelectItem } from 'primeng/primeng';
import { AlertService, InfoService, Configuration, LoaderService } from '../../../../helper';
import { FormBuilder, FormGroup, FormControl } from '@angular/forms';


@Component({
  selector: 'app-alamat',
  templateUrl: './alamat.component.html',
  styleUrls: ['./alamat.component.scss'],
  providers: [ConfirmationService]
})
export class AlamatComponent implements OnInit {

  formGroup: FormGroup;
  displayDialog: boolean;
  dataSource: any;
  now: any = new Date;
  listJK: SelectItem[];
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
    this.formGroup = this.fb.group({
      'idAlamat': new FormControl(null),
      'alamat': new FormControl(null),
      'provinsi': new FormControl(null),
      'kota': new FormControl(null),
      'kabupaten': new FormControl(null),
      'kecamatan': new FormControl(null),
    });

    this.getData()
  }

  showDialogToAdd() {
    this.resetForm()
    this.displayDialog = true;
  }

  getData() {
    this.httpService.get('master/alamat/get').subscribe(data => {
      this.dataSource = data.data
    })

  }
  save() {
    let data = this.formGroup.value
    this.httpService.post('master/alamat/save', data).subscribe(data => {
      this.getData()
      this.resetForm()
    }, error => {
      this.alertService.error('Error', JSON.stringify(error));
    });

  }
  edit(e) {
    this.formGroup.get('idAlamat').setValue(e.id);
    this.formGroup.get('alamat').setValue(e.alamat);
    this.formGroup.get('kecamatan').setValue(e.kecamatan);
    this.formGroup.get('kota').setValue(e.kota);
    this.formGroup.get('kabupaten').setValue(e.kabupaten);
    this.formGroup.get('provinsi').setValue(e.provinsi);
    this.displayDialog = true;
  }
  hapus(e) {
    let jsonDelete = {
      'idAlamat': e.id
    }
    this.confirmationService.confirm({
      message: 'Yakin mau menghapus data?',
      accept: () => {
        this.httpService.post('master/alamat/delete', jsonDelete).subscribe(data => {
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
