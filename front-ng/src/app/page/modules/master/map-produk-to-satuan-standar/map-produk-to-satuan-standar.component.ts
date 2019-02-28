import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
import { Observable } from 'rxjs/Rx';
import { LazyLoadEvent, Message, ConfirmDialogModule, ConfirmationService, SelectItem } from 'primeng/primeng';
import { AlertService, InfoService, Configuration, LoaderService } from '../../../../helper';
import { FormBuilder, FormGroup, FormControl } from '@angular/forms';
@Component({
  selector: 'app-map-produk-to-satuan-standar',
  templateUrl: './map-produk-to-satuan-standar.component.html',
  styleUrls: ['./map-produk-to-satuan-standar.component.scss'],
  providers: [ConfirmationService]
})
export class MapProdukToSatuanStandarComponent implements OnInit {
  formGroup: FormGroup;
  listSatuan: SelectItem[];
  listProduk: SelectItem[];
  loading: boolean = false
  dataSource: any[]
  selectedItem: any;
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
      'idMap': new FormControl(null),
      'produk': new FormControl(null),
      'kdSatuanAsal': new FormControl(null),
      'kdSatuanTujuan': new FormControl(null),
      'hasilKonversi': new FormControl(null),
      // 'asalKonversi': new FormControl(1)
    });
    this.getList()
    this.loadData()
  }

  loadData() {
    this.loading = true
    this.httpService.get('master/mapproduktosatuan/get').subscribe(data => {
      this.dataSource = data.data
      this.loading = false
    }, error => {
      this.loading = false
    })
  }
  getList() {
    this.httpService.get('transaksi/penerimaan/get-list-data').subscribe(data => {
      var getData: any = this.dataHandler.get(data);

      this.listProduk = [];
      this.listProduk.push({ label: '--Pilih Produk--', value: null });
      getData.produk.forEach(response => {
        this.listProduk.push({
          label: response.namaproduk, value: {
            kdProduk: response.id,
            namaProduk: response.namaproduk,
            kdSatuan: response.satuanstandardfk,
            namaSatuan: response.satuanstandard
          }
        });
      });

      this.listSatuan = [];
      this.listSatuan.push({ label: '--Pilih Satuan--', value: null });
      getData.satuan.forEach(response => {
        this.listSatuan.push({ label: response.satuanstandard, value: response.id });
      });
    }, error => {
      this.alertService.error('Error', 'Terjadi kesalahan saat loading data');
    });
  }
  save() {
    let data = this.formGroup.value
    if (!data.produk) {
      this.alertService.warn('Peringatan', 'Produk Harus di Pilih')
      return
    }
    if (!data.kdSatuanAsal) {
      this.alertService.warn('Peringatan', 'Satuan Asal Harus di Pilih')
      return
    }
    if (!data.kdSatuanTujuan) {
      this.alertService.warn('Peringatan', 'Satuan Tujuan Harus di Pilih')
      return
    }
    if (!data.hasilKonversi) {
      this.alertService.warn('Peringatan', 'Hasil Konversi Harus di isi')
      return
    }

    this.httpService.post('master/mapproduktosatuan/save', data).subscribe(data => {
      this.loadData()
      this.resetForm()
    }, error => {
      this.alertService.error('Error', JSON.stringify(error));
    });

  }
  setValueKdSatuan() {
    this.formGroup.get('kdSatuanAsal').setValue(this.formGroup.get('produk').value.kdSatuan)
  }
  onRowSelect(event) {
    let e = event.data
    this.formGroup.get('idMap').setValue(e.id);
    this.formGroup.get('kdSatuanAsal').setValue(e.satuanasalfk);
    this.formGroup.get('kdSatuanTujuan').setValue(e.satuantujuanfk);
    this.formGroup.get('produk').setValue({
      namaProduk: e.namaproduk, kdProduk: e.produkfk,
      kdSatuan: e.satuanprodukfk, namaSatuan: e.satuanproduk
    });
    this.formGroup.get('hasilKonversi').setValue(e.hasilkonversi);
  }
  edit(e) {
    this.formGroup.get('idMap').setValue(e.id);
    this.formGroup.get('kdSatuanAsal').setValue(e.satuanasalfk);
    this.formGroup.get('kdSatuanTujuan').setValue(e.satuantujuanfk);
    this.formGroup.get('produk').setValue({
      namaProduk: e.namaproduk, kdProduk: e.produkfk,
      kdSatuan: e.satuanprodukfk, namaSatuan: e.satuanproduk
    });
    this.formGroup.get('hasilKonversi').setValue(e.hasilkonversi);
  }
  hapus(e) {
    let jsonDelete = {
      'idMap': e.id
    }
    this.confirmationService.confirm({
      message: 'Yakin mau menghapus data?',
      accept: () => {
        this.httpService.post('master/mapproduktosatuan/delete', jsonDelete).subscribe(data => {
          this.loadData()
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
