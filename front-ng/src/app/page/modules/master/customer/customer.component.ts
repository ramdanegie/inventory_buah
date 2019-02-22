import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
import { Observable } from 'rxjs/Rx';
import { LazyLoadEvent, Message, ConfirmDialogModule, ConfirmationService, SelectItem } from 'primeng/primeng';
import { AlertService, InfoService, Configuration, LoaderService } from '../../../../helper';
import { FormBuilder, FormGroup, FormControl } from '@angular/forms';


@Component({
  selector: 'app-customer',
  templateUrl: './customer.component.html',
  styleUrls: ['./customer.component.scss'],
  providers: [ConfirmationService]
})
export class CustomerComponent implements OnInit {


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
      'idCustomer': new FormControl(null),
      'namaCustomer': new FormControl(null),
      'kdAlamat': new FormControl(null),
      'noHp': new FormControl(null),
      'noTlp': new FormControl(null)
    });
    this.getCombo()
    this.getData()
  }
  getCombo(){
    this.httpService.get('master/alamat/get').subscribe(data => {
			var getData: any = this.dataHandler.get(data);
			this.listAlamat = [];
			this.listAlamat.push({ label: '--Pilih Alamat --', value: null });
			getData.forEach(response => {
				this.listAlamat.push({ label: response.alamat, value: response.id });
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
    this.httpService.get('master/customer/get').subscribe(data => {
      this.dataSource = data.data
    })

  }
  save() {
    let data = this.formGroup.value
    this.httpService.post('master/customer/save', data).subscribe(data => {
      this.getData()
      this.resetForm()
    }, error => {
      this.alertService.error('Error', JSON.stringify(error));
    });

  }
  edit(e) {
    this.formGroup.get('idCustomer').setValue(e.id);
    this.formGroup.get('namaCustomer').setValue(e.namacustomer);
    this.formGroup.get('kdAlamat').setValue(e.alamatfk);
    this.formGroup.get('noHp').setValue(e.nohp);
    this.formGroup.get('noTlp').setValue(e.notlp);
    this.displayDialog = true;
  }
  hapus(e) {
    let jsonDelete = {
      'idCustomer': e.id
    }
    this.confirmationService.confirm({
      message: 'Yakin mau menghapus data?',
      accept: () => {
        this.httpService.post('master/customer/delete', jsonDelete).subscribe(data => {
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
