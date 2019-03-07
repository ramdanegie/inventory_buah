import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
import { Observable } from 'rxjs/Rx';
import { LazyLoadEvent, Message, ConfirmDialogModule, ConfirmationService, SelectItem } from 'primeng/primeng';
import { AlertService, InfoService, Configuration, LoaderService, CacheService } from '../../../../helper';
import { FormBuilder, FormGroup, FormControl } from '@angular/forms';
import { Router } from '@angular/router';
@Component({
  selector: 'app-stok-barang',
  templateUrl: './stok-barang.component.html',
  styleUrls: ['./stok-barang.component.scss'],
  providers: [ConfirmationService]
})
export class StokBarangComponent implements OnInit {

  formGroup: FormGroup;
  now = new Date()
  dataSource: any[];
  loading: boolean = false
  listKelompokProduk: SelectItem[]
  listJenisProduk: SelectItem[]
  listDetailJenisProduk: SelectItem[]
  selectedItem: any;
  items: any
  isUbah: boolean = false
  isPreview: boolean = false
  constructor(private alertService: AlertService,
    private InfoService: InfoService,
    private httpService: HttpClient,
    private confirmationService: ConfirmationService,
    private dataHandler: DataHandler,
    private fb: FormBuilder,
    private loader: LoaderService,
    private router: Router,
    private cacheHelper: CacheService
  ) { }


  ngOnInit() {
    this.items = [
      {
        label: 'Pdf', icon: 'fa-file-pdf-o', command: () => {
          this.downloadPdf();
        }
      },
      {
        label: 'Excel', icon: 'fa-file-excel-o ', command: () => {
          this.downloadExcel();
        }
      }
    ];
    this.formGroup = this.fb.group({
      'namaProduk': new FormControl(null),
      'kdKelompokProduk': new FormControl(null),
      'kdJenisProduk': new FormControl(null),
      'kdDetailJenis': new FormControl(null),
      'row': new FormControl(100),
      'harga': new FormControl(null),
    });
    this.getList()

  }
  downloadPdf() {
    this.confirmationService.confirm({
      message: 'Preview Pdf File ?',
      accept: () => {
        this.isPreview = true
      },
      reject: () => {
        this.isPreview = false
      }
    });
  }
  downloadExcel() {

  }
  clear() {
    this.formGroup.reset()
    this.formGroup.get('row').setValue(100)
    this.loadGrid()
  }
  formatDate(value) {
    if (value == null || value == undefined) {
      return null
    } else {
      let date = new Date(value)
      let hari = ("0" + date.getDate()).slice(-2)
      let bulan = ("0" + (date.getMonth() + 1)).slice(-2)
      let tahun = date.getFullYear()
      let format = tahun + '-' + bulan + '-' + hari
      return format
    }
  }
  formatDateFull(value) {
    if (value == null || value == undefined) {
      return null
    } else {
      let date = new Date(value)
      let hari = ("0" + date.getDate()).slice(-2)
      let bulan = ("0" + (date.getMonth() + 1)).slice(-2)
      let tahun = date.getFullYear()
      let h = ("0" + date.getHours()).slice(-2)
      let m = ("0" + date.getMinutes()).slice(-2)
      let s = date.getSeconds()

      let format = hari + '-' + bulan + '-' + tahun + ' '
        + h + ':' + m
      return format
    }
  }
  loadGrid() {

    let namaProduk = this.formGroup.get('namaProduk').value;
    let kdKelompokProduk = this.formGroup.get('kdKelompokProduk').value;
    let kdJenisProduk = this.formGroup.get('kdJenisProduk').value;
    let kdDetailJenis = this.formGroup.get('kdDetailJenis').value;
    let row = this.formGroup.get('row').value;
    if (kdKelompokProduk != null)
      kdKelompokProduk = kdKelompokProduk.id
    if (kdJenisProduk != null)
      kdJenisProduk = kdJenisProduk.id
    if (kdDetailJenis != null)
      kdDetailJenis = kdDetailJenis.id
    this.loading = true
    this.httpService.get('transaksi/stokproduk/get-stok?'
      + 'namaProduk=' + namaProduk
      + '&kdKelompokProduk=' + kdKelompokProduk
      + '&kdJenisProduk=' + kdJenisProduk
      + '&kdDetailJenis=' + kdDetailJenis
      + '&row=' + row
    ).subscribe(res => {
      this.loading = false
      let data = res.data
      if (data.length > 0) {
        for (let i = 0; i < data.length; i++) {
          data[i].tgltransaksi = this.formatDateFull(new Date(data[i].tgltransaksi));
          data[i].hargajual = this.formatRupiah(data[i].hargajual, 'Rp. ');
        }
        this.dataSource = data
      } else {
        this.loading = false
        this.alertService.info('Informasi', 'Data tidak ada')
        this.dataSource = []
      }
    })

  }
  formatRupiah(value, currency) {
    return currency + "" + parseFloat(value).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
  }
  cari() {
    this.loadGrid()
  }
  onRowSelect(e) {
    this.selectedItem = e.data
  }
  getList() {
    this.httpService.get('transaksi/stokproduk/get-combo').subscribe(data => {
      var getData: any = this.dataHandler.get(data);
      this.listKelompokProduk = [];
      this.listKelompokProduk.push({ label: '--Pilih Kelompok Produk --', value: null });
      getData.kelompokproduk.forEach(response => {
        this.listKelompokProduk.push({
          label: response.kelompokproduk, value: {
            id: response.id,
            kelompokproduk: response.kelompokproduk,
            jenisproduk: response.jenisproduk
          }
        });
      });

      this.loadGrid()
    }, error => {
      this.alertService.error('Error', 'Terjadi kesalahan saat loading data');
    });
  }

  getJenisProduk(event) {
    if (event.value) {
      let jenisproduk = event.value.jenisproduk
      if (jenisproduk.length > 0) {
        this.listJenisProduk = [];
        this.listJenisProduk.push({ label: '--Pilih Jenis Produk --', value: null });
        for (let i = 0; i < jenisproduk.length; i++) {
          const element = jenisproduk[i];

          this.listJenisProduk.push({
            label: element.jenisproduk, value: {
              id: element.id,
              jenisproduk: element.jenisproduk,
              detailjenisproduk: element.detailjenisproduk
            }
          });
        }
      } else
        this.listJenisProduk = [];
    } else
      this.listJenisProduk = [];
  }

  getDetailjenis(event) {
    if (event.value) {
      let detail = event.value.detailjenisproduk
      if (detail.length > 0) {
        this.listDetailJenisProduk = [];
        this.listDetailJenisProduk.push({ label: '--Pilih Detail Jenis Produk --', value: null });
        for (let i = 0; i < detail.length; i++) {
          const element = detail[i];
          this.listDetailJenisProduk.push({
            label: element.detailjenisproduk, value: {
              id: element.id,
              detailjenisproduk: element.detailjenisproduk,
            }
          });
        }
      } else
        this.listDetailJenisProduk = [];
    } else
      this.listDetailJenisProduk = [];
  }
  ubah() {
    if (!this.selectedItem) {
      this.alertService.warn('Pilih data dulu', 'Peringatan')
      return
    }
    this.isUbah = true
  }
  simpan() {
    let jsonSave = {
      'norec': this.selectedItem.norec,
      'produkfk': this.selectedItem.produkfk,
      'hargajual': parseFloat(this.formGroup.get('harga').value)
    }

    this.httpService.post('transaksi/stokproduk/update-harga', jsonSave).subscribe(res => {
      this.formGroup.reset()

    }, error => {

    })
  }
  batal() {
    this.isUbah = false
    this.formGroup.get('harga').reset()
  }
  print(): void {
    // this.namaProfile = this.authGuard.getUserDto().profile.NamaLengkap;
    // this.kelaminProfile = this.authGuard.getUserDto().profile.KelaminLengkap;
    let printContents, popupWin;
    printContents = document.getElementById('print-section').innerHTML;
    popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
    popupWin.document.open();
    popupWin.document.write(`
        <html>
            <head>
                <title></title>
                <style>
                    @media print{
                        @page {
                            size: landscape
                        }
                    }
                    table, th, td {
                        border: 1px solid black;
                        border-collapse: collapse;
                        font-size:11px;
                        font-family: "Source Sans Pro", "Helvetica Neue", sans-serif;
                        text-decoration: none;
                    }
                    body {
                      font-family: "Source Sans Pro", "Helvetica Neue", sans-serif;
                      text-decoration: none;
                    }
                </style>
            </head>
            <body onload="window.print();window.close()">${printContents}</body>
         </html>
         `
    );
    popupWin.document.close();
  }

}
