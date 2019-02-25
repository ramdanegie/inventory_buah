import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
import { Observable } from 'rxjs/Rx';
import { LazyLoadEvent, Message, ConfirmDialogModule, ConfirmationService, SelectItem } from 'primeng/primeng';
import { AlertService, InfoService, Configuration, LoaderService, CacheService } from '../../../../helper';
import { FormBuilder, FormGroup, FormControl } from '@angular/forms';
import { error } from 'util';

@Component({
  selector: 'app-transaksi-penjualan',
  templateUrl: './transaksi-penjualan.component.html',
  styleUrls: ['./transaksi-penjualan.component.scss'],
  providers: [ConfirmationService]
})
export class TransaksiPenjualanComponent implements OnInit {

  formGroup: FormGroup;
  listToko: SelectItem[];
  listProduk: SelectItem[];
  listPegawai: SelectItem[];
  listSatuan: SelectItem[];
  listCustomer: SelectItem[];
  now: any = new Date;
  tempDataGrid: any = [];
  dataSource: any[];
  nomor: any = undefined;
  subTotal: any = 0
  dataProdukDetail: any
  constructor(private alertService: AlertService,
    private InfoService: InfoService,
    private httpService: HttpClient,
    private confirmationService: ConfirmationService,
    private dataHandler: DataHandler,
    private fb: FormBuilder,
    private loader: LoaderService,
    private cacheHelper: CacheService
  ) { }

  ngOnInit() {
    this.getList()
    this.formGroup = this.fb.group({
      'noRec': new FormControl(null),
      'noTransaksi': new FormControl(null),
      'noTerima': new FormControl(null),
      'tglTransaksi': new FormControl(this.now),
      'kdToko': new FormControl(null),
      'kdPegawai': new FormControl(null),
      'kdCustomer': new FormControl(null),
      'produk': new FormControl(null),
      'qtyProduk': new FormControl(0),
      'kdSatuan': new FormControl(null),
      'stok': new FormControl(0),
      'hargaJual': new FormControl(0),
      'hargaDiskon': new FormControl(0),
      'total': new FormControl(0),
      'konversi': new FormControl(null),
    });
    let cache = this.cacheHelper.get('cacheUbahPenerimaanSupplier')
    if (cache != undefined) {
      this.loadFromEdit(cache)
      this.cacheHelper.set('cacheUbahPenerimaanSupplier', undefined);
    }

  }
  loadFromEdit(data) {
    this.httpService.get('transaksi/penerimaan/get-daftar-penerimaan?norec=' + data[0]
    ).subscribe(res => {
      let result = res.data[0]
      this.formGroup.get('noRec').setValue(result.norec)
      this.formGroup.get('noPenerimaan').setValue(result.nopenerimaan)
      this.formGroup.get('noFaktur').setValue(result.nofaktur)
      this.formGroup.get('tglPenerimaan').setValue(new Date(result.tgltransaksi))
      this.formGroup.get('kdToko').setValue(result.tokofk)
      this.formGroup.get('kdSupplier').setValue(result.supplierfk)
      this.formGroup.get('kdPegawai').setValue(result.pegawaifk)
      for (let i = 0; i < result.details.length; i++) {
        const element = result.details[i]
        let data = {
          'no': i + 1,
          'kdProduk': element.produkfk,
          'namaProduk': element.namaproduk,
          'qtyProduk': element.qtypenerimaan,
          'namaSatuan': element.satuanstandard,
          'kdSatuan': element.satuanfk,
          'hargaJual': element.hargajual,
          'hargaSatuan': element.hargapenerimaan,
          'total': element.totalpenerimaan,
        }
        this.tempDataGrid.push(data)
      }
      this.dataSource = this.tempDataGrid
    }, error => {

    })
  }
  getList() {
    this.httpService.get('transaksi/penerimaan/get-list-data').subscribe(data => {
      var getData: any = this.dataHandler.get(data);
      this.listPegawai = [];
      this.listPegawai.push({ label: '--Pilih Pegawai --', value: null });
      getData.pegawai.forEach(response => {
        this.listPegawai.push({ label: response.namalengkap, value: response.id });
      });

      this.listToko = [];
      this.listToko.push({ label: '--Pilih Toko--', value: null });
      getData.toko.forEach(response => {
        this.listToko.push({ label: response.namatoko, value: response.id });
      });

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

    this.httpService.get('master/customer/get').subscribe(data => {
      this.listCustomer = [];
      this.listCustomer.push({ label: '--Pilih Customer --', value: null });
      data.data.forEach(response => {
        this.listCustomer.push({ label: response.namacustomer, value: response.id });
      });
    }, error => {

    })
  }
  changeProduk(produk) {
    debugger
  }
  resetAll() {
    this.formGroup.reset()
    this.formGroup.get('qtyProduk').setValue(0);
    this.formGroup.get('hargaSatuan').setValue(0);
    this.formGroup.get('hargaJual').setValue(0);
    this.formGroup.get('total').setValue(0);
    this.formGroup.get('tglPenerimaan').setValue(this.now);
    this.dataSource = []
    this.tempDataGrid = []
  }
  resetPart() {
    this.formGroup.get('kdProduk').reset();
    this.formGroup.get('namaProduk').reset();
    this.formGroup.get('qtyProduk').setValue(0);
    this.formGroup.get('kdSatuan').reset();
    this.formGroup.get('hargaSatuan').setValue(0);
    this.formGroup.get('hargaJual').setValue(0);
    this.formGroup.get('total').setValue(0);
  }

  tambah() {
    let produk = this.formGroup.get('produk').value;
    let kdSatuan = this.formGroup.get('kdSatuan').value;
    let qtyProduk = this.formGroup.get('qtyProduk').value;
    // let hargaSatuan = this.formGroup.get('hargaSatuan').value;
    let hargaJual = this.formGroup.get('hargaJual').value;
    let total = this.formGroup.get('total').value;
    let kdProduk = this.formGroup.get('kdProduk').value;
    if (!produk) {
      this.alertService.warn("Peringatan", "Nama Produk harus di isi !")
      return
    }
    if (!kdSatuan) {
      this.alertService.warn("Peringatan", "Satuan harus di isi !")
      return
    }
    if (qtyProduk == 0) {
      this.alertService.warn("Peringatan", "Qty tidak boleh nol !")
      return
    }
    if (hargaJual == 0) {
      this.alertService.warn("Peringatan", "Harga Jual tidak boleh nol !")
      return
    }
    let nomor = 0
    if (this.dataSource == undefined || this.dataSource.length == 0) {
      nomor = 1
    } else {
      nomor = this.tempDataGrid.length + 1
    }
    let data: any = {};

    if (this.nomor != undefined) {
      for (var i = this.tempDataGrid.length - 1; i >= 0; i--) {
        if (this.tempDataGrid[i].no == this.nomor) {
          data.no = this.nomor
          data.kdProduk = produk.kdProduk
          data.namaProduk = produk.namaProduk
          data.qtyProduk = qtyProduk
          data.namaSatuan = produk.namaSatuan
          data.kdSatuan = produk.kdSatuan
          data.hargaJual = hargaJual
          // data.hargaSatuan = hargaSatuan
          data.total = total

          this.tempDataGrid[i] = data;
          this.dataSource = this.tempDataGrid
        }
      }
    } else {
      data = {
        'no': nomor,
        'kdProduk': kdProduk,
        'namaProduk': produk.namaProduk,
        'qtyProduk': qtyProduk,
        'namaSatuan': produk.namaSatuan,
        'kdSatuan': produk.kdSatuan,
        'hargaJual': hargaJual,
        // 'hargaSatuan': hargaSatuan,
        'total': total
      }
      this.tempDataGrid.push(data)
      this.dataSource = this.tempDataGrid
      this.resetPart()
    }
  }
  hapus() {
    let formControl = this.formGroup.value
    if (formControl.nomor == undefined) {
      this.alertService.warn('Peringatan', 'Pilih data dulu')
      return
    }
    var data: any = {};
    if (formControl.nomor != undefined) {
      for (var i = this.tempDataGrid.length - 1; i >= 0; i--) {
        if (this.tempDataGrid[i].no == formControl.nomor) {
          this.tempDataGrid.splice(i, 1);
          for (var i = this.tempDataGrid.length - 1; i >= 0; i--) {
            this.tempDataGrid[i].no = i + 1
          }
          this.dataSource = this.tempDataGrid
        }
      }
    }
    this.resetPart()
  }
  batal() {
    this.resetPart()
  }
  onChangeHargaSatuan(value: number) {
    let qty = this.formGroup.get('qtyProduk').value
    let diskon = this.formGroup.get('hargaDiskon').value
    let total = (qty * value) - diskon
    this.formGroup.get('total').setValue(total)
    // console.log(total);
  }
  onChangeQty(value: number) {
    let stok = this.formGroup.get('stok').value
    if (value > stok) {
      this.alertService.warn('Peringatan', 'Jumlah tidak boleh melebihi stok')
      this.formGroup.get('qtyProduk').setValue(0)
      return
    }
    let hargaSatuan = this.formGroup.get('hargaJual').value
    let diskon = this.formGroup.get('hargaDiskon').value
    let total = (hargaSatuan * value) - diskon
    this.formGroup.get('total').setValue(total)
  }
  OnChangeDiskon(value: number) {
    let harga = this.formGroup.get('hargaJual').value
    let jml = this.formGroup.get('qtyProduk').value
    let total = (harga * jml) - value
    this.formGroup.get('total').setValue(total)
    // console.log(total);
  }
  setValueKdSatuan() {
    // this.formGroup.get('kdProduk').setValue(this.formGroup.get('produk').value.kdProduk)
    this.formGroup.get('kdSatuan').setValue(this.formGroup.get('produk').value.kdSatuan)
    this.getKonversi()
  }

  onRowSelect(event) {
    let e = event.data
    this.nomor = e.no
    this.formGroup.get('kdProduk').setValue(e.kdProduk);
    this.formGroup.get('namaProduk').setValue({
      namaProduk: e.namaProduk, kdProduk: e.kdProduk,
      kdSatuan: e.kdSatuan, namaSatuan: e.namaSatuan
    });
    this.formGroup.get('kdSatuan').setValue(e.kdSatuan);
    this.formGroup.get('qtyProduk').setValue(e.qtyProduk);
    this.formGroup.get('hargaSatuan').setValue(e.hargaSatuan);
    this.formGroup.get('hargaJual').setValue(e.hargaJual);
    this.formGroup.get('total').setValue(e.total);

  }
  simpan() {
    if (!this.formGroup.get('kdToko').value) {
      this.alertService.warn('Peringatan', 'Pilih Toko terlebih dahulu !')
      return
    }
    if (!this.formGroup.get('kdPegawai').value) {
      this.alertService.warn('Peringatan', 'Pilih Pegawai terlebih dahulu !')
      return
    }
    if (!this.formGroup.get('kdSupplier').value) {
      this.alertService.warn('Peringatan', 'Pilih Supplier terlebih dahulu !')
      return
    }
    if (this.tempDataGrid.length == 0) {
      this.alertService.warn('Peringatan', 'Pilih produk terlebih dahulu !')
      return
    }

    let jsonSave = {
      'penerimaan': this.formGroup.value,
      'details': this.tempDataGrid
    }
    this.confirmationService.confirm({
      message: 'Yakin mau menyimpan data?',
      accept: () => {
        this.httpService.post('transaksi/penerimaan/save-penerimaan', jsonSave).subscribe(res => {
          this.formGroup.reset()

        }, error => {

        })
      }
    })
  }

  getKonversi() {
    this.formGroup.get('konversi').setValue(1)
    let produk = this.formGroup.get('produk').value;
    this.httpService.get("transaksi/penjualan/get-stok-produk?" +
      "produkfk=" + produk.kdProduk).subscribe(res => {
        this.dataProdukDetail = res.detail
        this.formGroup.get('stok').setValue(parseFloat(res.jmlstok))
        if (this.dataProdukDetail.length > 0) {
          this.formGroup.get('noTerima').setValue(this.dataProdukDetail[0].nopenerimaan)
          this.formGroup.get('hargaJual').setValue(parseFloat(this.dataProdukDetail[0].hargajual))
        }
      }, error => {

      })

  }

}

