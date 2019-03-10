import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
import { Observable } from 'rxjs/Rx';
import { LazyLoadEvent, Message, ConfirmDialogModule, ConfirmationService, SelectItem } from 'primeng/primeng';
import { AlertService, InfoService, Configuration, LoaderService, CacheService, AuthGuard } from '../../../../helper';
import { FormBuilder, FormGroup, FormControl } from '@angular/forms';
import { Router } from '@angular/router';
@Component({
  selector: 'app-daftar-penjualan',
  templateUrl: './daftar-penjualan.component.html',
  styleUrls: ['./daftar-penjualan.component.scss'],
  providers: [ConfirmationService]
})
export class DaftarPenjualanComponent implements OnInit {
  formGroup: FormGroup;
  now = new Date()
  dataSource: any[];
  loading: boolean = false
  listPegawai: SelectItem[]
  listToko: SelectItem[]
  selectedItem: any;
  items: any
  namaProfile: any
  alamatProfile: any
  dataSourcePrint: any[]
  isPreview: boolean = false
  constructor(private alertService: AlertService,
    private InfoService: InfoService,
    private httpService: HttpClient,
    private confirmationService: ConfirmationService,
    private dataHandler: DataHandler,
    private fb: FormBuilder,
    private loader: LoaderService,
    private router: Router,
    private cacheHelper: CacheService,
    private authGuard: AuthGuard
  ) { }


  ngOnInit() {
    this.namaProfile = this.authGuard.getUserDto().profile.namaProfile;
    this.alamatProfile = this.authGuard.getUserDto().profile.alamatProfile;

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
      'noTransaksi': new FormControl(null),
      'namaCustomer': new FormControl(null),
      'kdToko': new FormControl(null),
      'kdPegawai': new FormControl(null),
      'tglAwal': new FormControl(new Date(this.formatDate(this.now) + ' 00:00')),
      'tglAkhir': new FormControl(this.now),
    });
    this.getList()
    this.loadGrid()
  }
  downloadPdf() {
    // this.confirmationService.confirm({
    //   message: 'Preview Pdf File ?',
    //   accept: () => {
        this.isPreview = true
    //   },
    //   reject: () => {
    //     this.isPreview = false
    //   }
    // });
  }
  downloadExcel() {

  }
  clear() {
    this.formGroup.reset()
    this.formGroup.get('tglAwal').setValue(new Date(this.formatDate(this.now) + ' 00:00'))
    this.formGroup.get('tglAkhir').setValue(this.now)
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

      let format = tahun + '-' + bulan + '-' + hari + ' '
        + h + ':' + m
      return format
    }
  }
  loadGrid() {
    let noTransaksi = this.formGroup.get('noTransaksi').value;
    let namaCustomer = this.formGroup.get('namaCustomer').value;
    let tglAkhir = this.formatDateFull(this.formGroup.get('tglAkhir').value);
    let tglAwal = this.formatDateFull(this.formGroup.get('tglAwal').value);
    let kdPegawai = this.formGroup.get('kdPegawai').value;
    let kdToko = this.formGroup.get('kdToko').value;
    if (noTransaksi)
      noTransaksi = '&notransaksi=' + noTransaksi
    else
      noTransaksi = ''

    if (namaCustomer)
      namaCustomer = '&namacustomer=' + namaCustomer
    else
      namaCustomer = ''

    if (kdToko)
      kdToko = '&kdtoko=' + kdToko
    else
      kdToko = ''

    if (kdPegawai)
      kdPegawai = '&kdpegawai=' + kdPegawai
    else
      kdPegawai = ''


    this.loading = true
    this.httpService.get('transaksi/penjualan/get-penjualan?tglAwal=' + tglAwal
      + '&tglAkhir=' + tglAkhir
      + noTransaksi + namaCustomer + kdToko + kdPegawai
    ).subscribe(res => {
      this.loading = false
      let data = res.data
      let dataPrint = []
      if (data.length > 0) {
        for (let i = 0; i < data.length; i++) {
          data[i].totalall = this.formatRupiah(data[i].totalall, 'Rp. ');
          for (let j = 0; j < data[i].details.length; j++) {
            const element = data[i].details[j]
            element.hargadiskon = this.formatRupiah(element.hargadiskon, 'Rp. ');
            element.total = this.formatRupiah(element.total, 'Rp. ');
            element.hargajual = this.formatRupiah(element.hargajual, 'Rp. ');
          }
        }

        for (let i = 0; i < data.length; i++) {
          const element = data[i];
          for (let j = 0; j < element.details.length; j++) {
            const element2 = element.details[j]
            let push = {
              'notransaksi': element.notransaksi,
              'tgltransaksi': element.tgltransaksi,
              'namalengkap':element.namalengkap,
              'namacustomer': element.namacustomer,
              'namatoko': element.namatoko,
              'namaproduk': element2.namaproduk,
              'qty': element2.qty,
              'satuanstandard': element2.satuanstandard,
              'hargajual': element2.hargajual,
              'hargadiskon': element2.hargadiskon,
              'total': element2.total,
            }
            dataPrint.push(push)
          }
        }
        this.dataSource = data
        this.dataSourcePrint = dataPrint
      } else {
        this.loading = false
        this.alertService.info('Informasi', 'Data tidak ada')
        this.dataSource = []
        this.dataSourcePrint = []
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
    this.httpService.get('transaksi/penerimaan/get-list-data').subscribe(data => {
      var getData: any = this.dataHandler.get(data);
      this.listPegawai = [];
      this.listPegawai.push({ label: '--Pilih Pegawai --', value: null });
      getData.pegawai.forEach(response => {
        this.listPegawai.push({ label: response.namalengkap, value: response.id });
      });

      this.listToko = [];
      this.listToko.push({ label: '--Pilih Toko --', value: null });
      getData.toko.forEach(response => {
        this.listToko.push({ label: response.namatoko, value: response.id });
      });

    }, error => {
      this.alertService.error('Error', 'Terjadi kesalahan saat loading data');
    });


  }
  ubah() {
    if (this.selectedItem == undefined) {
      this.alertService.warn('Peringatan', 'Pilih data dulu')
      return
    }
    var cache = {
      0: this.selectedItem.norec,
      1: 'EditPenjualan',
    }

    this.cacheHelper.set('cacheUbahTransaksiPenjualan', cache);
    this.router.navigate(['/transaksi-penjualan'])
  }
  hapus() {
    if (this.selectedItem == undefined) {
      this.alertService.warn('Peringatan', 'Pilih data dulu')
      return
    }
    let obj = {
      'noRec': this.selectedItem.norec
    }
    this.confirmationService.confirm({
      message: 'Yakin mau menghapus data?',
      accept: () => {
        this.httpService.post('transaksi/penjualan/delete-penjualan', obj).subscribe(res => {
          this.loadGrid()
        }, error => {

        })
      }
    })
  }
  cetak(): void {

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
                        font-size:8px;
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
