import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
import { Observable } from 'rxjs/Rx';
import { LazyLoadEvent, Message, ConfirmDialogModule, ConfirmationService, SelectItem } from 'primeng/primeng';
import { AlertService, InfoService, Configuration, LoaderService } from '../../../../helper';
import { FormBuilder, FormGroup, FormControl } from '@angular/forms';


@Component({
  selector: 'app-pegawai',
  templateUrl: './pegawai.component.html',
  styleUrls: ['./pegawai.component.scss'],
  providers: [ConfirmationService]
})
export class PegawaiComponent implements OnInit {

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
      'idPegawai': new FormControl(null),
      'namaLengkap': new FormControl(null),
      'namaPanggilan': new FormControl(null),
      'noHp': new FormControl(null),
      'noTlp': new FormControl(null),
      'kdJenisKelamin': new FormControl(null),
      'kdAlamat': new FormControl(null),
      'tglLahir': new FormControl(this.now),
    });

    this.getData()
  }

  showDialogToAdd() {
    this.getCombo()
    this.resetForm()
    this.displayDialog = true;
  }
  getCombo() {
    this.httpService.get('master/get-combo').subscribe(data => {
      var getData: any = this.dataHandler.get(data);
      this.listJK = [];
      this.listJK.push({ label: '--Pilih--', value: '' });
      getData.jeniskelamin.forEach(response => {
        this.listJK.push({ label: response.jeniskelamin, value: response.id });
      });

      this.listAlamat = [];
      this.listAlamat.push({ label: '--Pilih--', value: '' });
      getData.alamat.forEach(response => {
        this.listAlamat.push({ label: response.alamat, value: response.id });
      });
    }, error => {
      this.alertService.error('Error', JSON.stringify(error));
    });
  }
  getData() {
    this.httpService.get('master/pegawai/get-daftar-pegawai').subscribe(data => {
      if (data.data.length > 0) {
        for (let i = 0; i < data.data.length; i++) {
          data.data[i].no = i + 1
          if (data.data[i].nohp == null)
            data.data[i].nohp = '-'
          if (data.data[i].notlp == null)
            data.data[i].notlp = '-'
          data.data[i].notelp = data.data[i].nohp + ' / ' + data.data[i].notlp
        }
      }
      this.dataSource = data.data
    })

  }
  save() {
    let data = this.formGroup.value
    this.httpService.post('master/pegawai/save-pegawai', data).subscribe(data => {
      this.getData()
      this.resetForm()
    }, error => {
      this.alertService.error('Error', JSON.stringify(error));
    });

  }
  edit(e) {
    this.formGroup.get('idPegawai').setValue(e.id);
    this.formGroup.get('namaLengkap').setValue(e.namalengkap);
    this.formGroup.get('namaPanggilan').setValue(e.namapanggilan);
    this.formGroup.get('noHp').setValue(e.nohp);
    this.formGroup.get('noTlp').setValue(e.notlp);
    this.formGroup.get('kdJenisKelamin').setValue(e.jeniskelaminfk);
    this.formGroup.get('kdAlamat').setValue(e.alamatfk);
    this.formGroup.get('tglLahir').setValue(new Date(e.tgllahir));
    this.displayDialog = true;
  }
  hapus(e) {

    let jsonDelete = {
      'idPegawai': e.id
    }
    this.confirmationService.confirm({
      message: 'Yakin mau menghapus data?',
      accept: () => {
        this.httpService.post('master/pegawai/delete-pegawai', jsonDelete).subscribe(data => {
          this.getData()
          this.resetForm()
        }, error => {
          this.alertService.error('Error', JSON.stringify(error));
        });
      }
    })
  }
  resetForm() {
    this.formGroup.get('idPegawai').reset();
    this.formGroup.get('namaLengkap').reset();
    this.formGroup.get('namaPanggilan').reset();
    this.formGroup.get('noHp').reset();
    this.formGroup.get('noTlp').reset();
    this.formGroup.get('kdJenisKelamin').reset();
    this.formGroup.get('kdAlamat').reset();
    // this.formGroup.get('tglLahir').reset();


  }
}
