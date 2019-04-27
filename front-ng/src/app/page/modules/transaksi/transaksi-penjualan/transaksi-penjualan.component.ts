import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
import { Observable } from 'rxjs/Rx';
import { LazyLoadEvent, Message, ConfirmDialogModule, ConfirmationService, SelectItem } from 'primeng/primeng';
import { AlertService, InfoService, Configuration, LoaderService, CacheService, AuthGuard } from '../../../../helper';
import { FormBuilder, FormGroup, FormControl } from '@angular/forms';
import { error } from 'util';
import { TemplatePortal } from '@angular/cdk/portal';

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
  listNoTerima: SelectItem[]
  listTipePembayaran: SelectItem[]
  now: any = new Date();
  tempDataGrid: any = [];
  dataSource: any[];
  nomor: any = undefined;
  subTotal: any = 0
  dataProdukDetail: any
  noRecTerima: any = ''
  hargaJual: any
  listPenerimaan: any[]
  isEdit: boolean = false
  displayDialog: boolean = false
  dataSourcePembayaran: any[]
  totalPembayaran: any = 0
  temPembayaran: any = []
  norecTransaksi: any = null
  selectNoBayar: any
  subtotalPembayaran: any
  listBayar: any
  noPembayaran: any
  penerimaPembayaran: any
  tglPembayaran: any
  totalbayarNa: any
  namaProfile: any
  alamatProfile: any
  isPrintBukti: boolean = false
  constructor(private alertService: AlertService,
    private InfoService: InfoService,
    private httpService: HttpClient,
    private confirmationService: ConfirmationService,
    private dataHandler: DataHandler,
    private fb: FormBuilder,
    private loader: LoaderService,
    private cacheHelper: CacheService,
    private authGuard: AuthGuard,
  ) { }

  ngOnInit() {
    this.namaProfile = this.authGuard.getUserDto().profile.namaProfile;
    this.alamatProfile = this.authGuard.getUserDto().profile.alamatProfile;
    this.getList()
    this.formGroup = this.fb.group({
      'noRec': new FormControl(null),
      'noTransaksi': new FormControl(null),
      'noRecTerima': new FormControl(null),
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
      'totalTagihan': new FormControl(0),
      'terbilang': new FormControl(null),
      'tipeBayar': new FormControl(null),
      'nominal': new FormControl(null),
      'kdPegawaiPenerima': new FormControl(null),

    });
    this.formGroup.get('kdPegawai').setValue(this.authGuard.getUserDto().kdPegawai)
    this.formGroup.get('kdPegawaiPenerima').setValue(this.authGuard.getUserDto().kdPegawai)
    let cache = this.cacheHelper.get('cacheUbahTransaksiPenjualan')
    if (cache != undefined) {
      this.loadFromEdit(cache)
      this.cacheHelper.set('cacheUbahTransaksiPenjualan', undefined);
    }

  }
  loadFromEdit(data) {
    this.isEdit = true
    this.httpService.get('transaksi/penjualan/get-penjualan?norec=' + data[0]
    ).subscribe(res => {
      let result = res.data[0]
      this.formGroup.get('noRec').setValue(result.norec)
      this.formGroup.get('noTransaksi').setValue(result.notransaksi)
      this.formGroup.get('kdCustomer').setValue(result.customerfk)
      this.formGroup.get('tglTransaksi').setValue(new Date(result.tgltransaksi))
      this.formGroup.get('kdToko').setValue(result.tokofk)
      this.formGroup.get('kdPegawai').setValue(result.pegawaifk)
      for (let i = 0; i < result.details.length; i++) {
        const element = result.details[i]
        let data = {
          'no': i + 1,
          'kdProduk': element.produkfk,
          'namaProduk': element.namaproduk,
          'qtyProduk': element.qty,
          'namaSatuan': element.satuanjual,
          'kdSatuan': element.satuanjualfk,
          'hargaJual': element.hargajual,
          'hargaDiskon': element.hargadiskon,
          'total': element.total,
          'konversi': element.nilaikonversi,
          'strukpenerimaanfk': element.penerimaanfk,
        }
        this.tempDataGrid.push(data)


      }
      this.dataSource = this.tempDataGrid
      let subTotal: any = 0;
      for (let i = this.tempDataGrid.length - 1; i >= 0; i--) {
        subTotal = subTotal + parseFloat(this.tempDataGrid[i].total)
      }
      this.subTotal = parseFloat(subTotal).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,")
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
    this.formGroup.get('hargaJual').setValue(0);
    this.formGroup.get('total').setValue(0);
    this.formGroup.get('stok').setValue(0);
    this.formGroup.get('hargaDiskon').setValue(0);
    this.formGroup.get('tglTransaksi').setValue(this.now);
    this.tempDataGrid = []
    this.dataSource = this.tempDataGrid
    this.subTotal = 0
    this.nomor = undefined
  }
  resetPart() {
    this.formGroup.get('produk').reset();
    this.formGroup.get('qtyProduk').setValue(0);
    this.formGroup.get('kdSatuan').reset();
    this.formGroup.get('hargaJual').setValue(0);
    this.formGroup.get('total').setValue(0);
    this.formGroup.get('hargaDiskon').setValue(0);
    this.formGroup.get('stok').setValue(0);
    this.formGroup.get('konversi').setValue(0);
    this.formGroup.get('noRecTerima').reset()
    this.nomor = undefined
  }

  tambah() {
    let produk = this.formGroup.get('produk').value;
    let kdSatuan = this.formGroup.get('kdSatuan').value;
    let qtyProduk = this.formGroup.get('qtyProduk').value;
    let konversi = this.formGroup.get('konversi').value;
    let hargaJual = this.formGroup.get('hargaJual').value;
    let total = this.formGroup.get('total').value;
    let hargaDiskon = this.formGroup.get('hargaDiskon').value;
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
          data.namaSatuan = kdSatuan.namaSatuan
          data.kdSatuan = kdSatuan.id
          data.hargaJual = hargaJual
          data.hargaDiskon = hargaDiskon
          data.total = total
          data.konversi = konversi
          data.strukpenerimaanfk = this.formGroup.get('noRecTerima').value

          this.tempDataGrid[i] = data;
          this.dataSource = this.tempDataGrid
          let subTotal: any = 0;
          for (let i = this.tempDataGrid.length - 1; i >= 0; i--) {
            subTotal = subTotal + parseFloat(this.tempDataGrid[i].total)
          }
          this.subTotal = parseFloat(subTotal).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,")
        }
      }
    } else {
      data = {
        'no': nomor,
        'kdProduk': produk.kdProduk,
        'namaProduk': produk.namaProduk,
        'qtyProduk': qtyProduk,
        'namaSatuan': kdSatuan.namaSatuan,
        'kdSatuan': kdSatuan.id,
        'hargaJual': hargaJual,
        'hargaDiskon': hargaDiskon,
        'total': total,
        'konversi': konversi,
        'strukpenerimaanfk': this.formGroup.get('noRecTerima').value
      }
      this.tempDataGrid.push(data)
      this.dataSource = this.tempDataGrid
      let subTotal: any = 0;
      for (let i = this.tempDataGrid.length - 1; i >= 0; i--) {
        subTotal = subTotal + parseFloat(this.tempDataGrid[i].total)
      }
      this.subTotal = parseFloat(subTotal).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,")
    }
    this.resetPart()
  }
  hapus() {
    if (this.nomor == undefined) {
      this.alertService.warn('Peringatan', 'Pilih data dulu')
      return
    }

    for (var i = this.tempDataGrid.length - 1; i >= 0; i--) {
      if (this.tempDataGrid[i].no == this.nomor) {
        this.tempDataGrid.splice(i, 1);
        for (var i = this.tempDataGrid.length - 1; i >= 0; i--) {
          this.tempDataGrid[i].no = i + 1
        }
        this.dataSource = this.tempDataGrid
      }
    }
    let subTotal: any = 0;
    for (let i = this.tempDataGrid.length - 1; i >= 0; i--) {
      subTotal = subTotal + parseFloat(this.tempDataGrid[i].total)
    }
    this.subTotal = parseFloat(subTotal).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,")

    this.resetPart()
    this.nomor = undefined
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
    let kdProduk = this.formGroup.get('produk').value.kdProduk
    let kdSatuan = this.formGroup.get('kdSatuan').value.id
    if (kdSatuan) {
      this.httpService.get('master/mapproduktosatuan/get?produkfk=' + kdProduk
        + '&satuantujuanfk=' + kdSatuan).subscribe(res => {
          if (res.data.length > 0) {
            this.formGroup.get('konversi').setValue(parseFloat(this.formGroup.get('qtyProduk').value) * parseFloat(res.data[0].hasilkonversi))
          } else {
            this.formGroup.get('konversi').setValue(1)
          }
        }, error => {

        })
    }
    let hargaSatuan = this.formGroup.get('hargaJual').value
    let diskon = this.formGroup.get('hargaDiskon').value
    let total = (hargaSatuan * value) - diskon
    this.formGroup.get('total').setValue(total)

  }
  onChangeSatuan(value) {
    let kdProduk = this.formGroup.get('produk').value.kdProduk
    let kdSatuan = this.formGroup.get('kdSatuan').value.id
    if (kdSatuan) {
      this.httpService.get('master/mapproduktosatuan/get?produkfk=' + kdProduk
        + '&satuantujuanfk=' + kdSatuan).subscribe(res => {
          if (res.data.length > 0) {
            this.formGroup.get('konversi').setValue(parseFloat(this.formGroup.get('qtyProduk').value) * parseFloat(res.data[0].hasilkonversi))
          } else {
            this.formGroup.get('konversi').setValue(1)
          }
        }, error => {

        })
    }
    let hargaSatuan = this.formGroup.get('hargaJual').value
    let diskon = this.formGroup.get('hargaDiskon').value
    let total = (hargaSatuan * this.formGroup.get('qtyProduk').value) - diskon
    this.formGroup.get('total').setValue(total)
  }
  OnChangeDiskon(value: number) {
    let harga = this.formGroup.get('hargaJual').value
    let jml = this.formGroup.get('qtyProduk').value
    let total = (harga * jml) - value
    this.formGroup.get('total').setValue(total)
    // console.log(total);
  }
  getPenerimaan() {
  
    let produk = this.formGroup.get('produk').value;
    let kdToko = this.formGroup.get('kdToko').value;
    if(kdToko == null){
      this.alertService.warn('Peringatan','Pilih Toko dulu')
      this.formGroup.get('produk').reset()
      return
    }
    let norec = ''//this.formGroup.get('noRecTerima').value;
    this.httpService.get("transaksi/penerimaan/get-penerimaan-ada-stok?" +
      "produkfk=" + produk.kdProduk
      + "&tokofk="+ kdToko
      + "&norecTerima="
    ).subscribe(res => {
      // this.dataProdukDetail = res.data
      this.listNoTerima = [];
      this.listPenerimaan = []

      this.listNoTerima.push({ label: '--Pilih No Terima --', value: null });
      res.data.forEach(response => {
        this.listPenerimaan.push(response)
        this.listNoTerima.push({ label: response.nopenerimaan, value: response.norec });
      });

      // this.formGroup.get('stok').setValue(parseFloat(res.jmlstok))
      // if (this.dataProdukDetail.length > 0) {
      //   this.formGroup.get('noRecTerima').setValue(this.dataProdukDetail[0].nopenerimaan)
      //   this.formGroup.get('hargaJual').setValue(parseFloat(this.dataProdukDetail[0].hargajual))
      // }
      // this.getStok()
    }, error => {
      this.listPenerimaan = []
      this.listNoTerima = [];
      this.listNoTerima.push({ label: '--Data tidak ada --', value: null });
    })

    this.getMapSatuan(null, produk.kdProduk)

  }

  getMapSatuan(kdSatuan, kdProduk) {
    if (!kdSatuan)
      kdSatuan = ''
    if (!kdProduk)
      kdProduk = ''
    this.httpService.get('master/mapproduktosatuan/get?produkfk=' + kdProduk
      + '&satuantujuanfk=' + kdSatuan).subscribe(res => {
        if (res.data.length > 0) {
          this.listSatuan = [];
          this.listSatuan.push({ label: '--Pilih Satuan--', value: null });
          res.data.forEach(response => {
            this.listSatuan.push({
              label: response.satuantujuan, value: {
                id: response.satuantujuanfk,
                namaSatuan: response.satuantujuan
              }
            });

          });
          this.formGroup.get('kdSatuan').setValue(
            {
              id: this.formGroup.get('produk').value.kdSatuan,
              namaSatuan: this.formGroup.get('produk').value.namaSatuan,
            }
          )
          for (let i = 0; i < res.data.length; i++) {
            const element = res.data[i];
            if (this.formGroup.get('kdSatuan').value.id &&
              element.satuantujuanfk == this.formGroup.get('kdSatuan').value.id) {
              this.formGroup.get('konversi').setValue(
                parseFloat(this.formGroup.get('qtyProduk').value) * parseFloat(element.hasilkonversi)
              )
              break
            }
          }
        } else {
          this.listSatuan = [];
          this.listSatuan.push({ label: '--Pilih Satuan--', value: null });
          this.formGroup.get('konversi').setValue(1)
        }
      }, error => {

      })
  }
  getStok() {
    let noRecTerima = this.formGroup.get('noRecTerima').value
    let kdProduk = this.formGroup.get('produk').value.kdProduk
    for (let i = 0; i < this.listPenerimaan.length; i++) {
      const element = this.listPenerimaan[i];
      if (element.norec == noRecTerima && kdProduk == element.produkfk) {
        this.formGroup.get('stok').setValue(parseFloat(element.qty))
        this.formGroup.get('hargaJual').setValue(parseFloat(element.hargajual))
        break
      }
    }
    // this.formGroup.get('kdSatuan').setValue(this.formGroup.get('produk').value.kdSatuan)
    // let produk = this.formGroup.get('produk').value;
    // let norec = this.formGroup.get('noRecTerima').value;
    // this.httpService.get("transaksi/penerimaan/get-penerimaan-ada-stok?" +
    //   "produkfk=" + produk.kdProduk
    //   + "&norecTerima=" + norec
    // ).subscribe(res => {
    //   this.dataProdukDetail = res.data
    //   this.formGroup.get('stok').setValue(parseFloat(res.stok))
    //   if (this.dataProdukDetail.length > 0) {
    //     // this.formGroup.get('noRecTerima').setValue(this.dataProdukDetail[0].nopenerimaan)
    //     this.formGroup.get('hargaJual').setValue(parseFloat(this.dataProdukDetail[0].hargajual))
    //   }
    // }, error => {

    // })
  }
  onRowSelect(event) {
    let e = event.data
    this.nomor = e.no
    this.formGroup.get('produk').setValue({
      namaProduk: e.namaProduk, kdProduk: e.kdProduk,
      kdSatuan: e.kdSatuan, namaSatuan: e.namaSatuan
    });
    this.getMapSatuan(null, e.kdProduk)
    this.getPenerimaan()

    // this.formGroup.get('satuan').setValue({
    // 	id: e.satuanterimafk,
    // 	namaSatuan: e.namaSatuan,
    // });
    this.formGroup.get('kdSatuan').setValue({
      id: e.kdSatuan,
      namaSatuan: e.namaSatuan
    });
    this.formGroup.get('qtyProduk').setValue(e.qtyProduk);
    this.formGroup.get('hargaJual').setValue(e.hargaJual);
    this.formGroup.get('hargaDiskon').setValue(e.hargaDiskon);
    this.formGroup.get('konversi').setValue(e.konversi);
    // this.noRecTerima =
    this.formGroup.get('noRecTerima').setValue(e.strukpenerimaanfk)
    this.getStok()
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
    if (!this.formGroup.get('kdCustomer').value) {
      this.alertService.warn('Peringatan', 'Pilih Customer terlebih dahulu !')
      return
    }
    if (this.tempDataGrid.length == 0) {
      this.alertService.warn('Peringatan', 'Pilih produk terlebih dahulu !')
      return
    }

    let jsonSave = {
      'penjualan': this.formGroup.value,
      'detail': this.tempDataGrid,
    }

    this.httpService.post('transaksi/penjualan/save-penjualan', jsonSave).subscribe(res => {
      this.norecTransaksi = res.data.norec
      this.confirmationService.confirm({
        message: 'Lanjut Pembayaran?',
        accept: () => {
          this.displayDialog = true
          let subTotal: any = 0;
          for (let i = this.tempDataGrid.length - 1; i >= 0; i--) {
            subTotal = subTotal + parseFloat(this.tempDataGrid[i].total)
          }
          this.formGroup.get('totalTagihan').setValue(subTotal)
          let totaltagihan = this.formGroup.get('totalTagihan').value
          this.formGroup.get('nominal').setValue(subTotal)

          this.httpService.get('generic/get-terbilang/' + totaltagihan).subscribe(data => {
            this.formGroup.get('terbilang').setValue(data)
          })

          this.httpService.get('transaksi/pembayaran/get-combo').subscribe(data => {
            var getData: any = this.dataHandler.get(data);
            this.listTipePembayaran = [];
            this.listTipePembayaran.push({ label: '--Pilih Tipe Bayar --', value: null });
            getData.tipepembayaran.forEach(response => {
              this.listTipePembayaran.push({
                label: response.tipepembayaran, value:
                {
                  id: response.id,
                  tipepembayaran: response.tipepembayaran
                }

              });
            });
          })
        },
        reject: () => {
          this.resetAll()
        }
      })

    }, error => {

    })


  }
  addPembayaran() {

    // let tempPembayaran = []
    let tipe = this.formGroup.get('tipeBayar').value
    let totalTagihan = this.formGroup.get('totalTagihan').value

    let nominal = this.formGroup.get('nominal').value
    if (!tipe) {
      this.alertService.warn('Peringatan', 'Pilih Tipe Pembayaran')
      return
    }
    if (!nominal) {
      this.alertService.warn('Peringatan', 'Nominal Belum Di isi')
      return
    }

    for (let i = this.temPembayaran.length - 1; i >= 0; i--) {
      if (this.temPembayaran[i].tipepembayaranfk == tipe.id) {
        this.alertService.warn('Peringatan', 'Tipe Pembayaran yang sama sudah ada')
        return
      }
    }
    let data = {
      'tipepembayaran': tipe.tipepembayaran,
      'tipepembayaranfk': tipe.id,
      'nominal': parseFloat(nominal),
    }


    this.temPembayaran.push(data)
    this.dataSourcePembayaran = this.temPembayaran
    let subTotal: any = 0;
    for (let i = this.temPembayaran.length - 1; i >= 0; i--) {
      subTotal = subTotal + parseFloat(this.temPembayaran[i].nominal)
      this.temPembayaran[i].no = i + 1
    }

    this.formGroup.get('nominal').setValue(parseFloat(totalTagihan) - subTotal)
    this.totalPembayaran = parseFloat(subTotal).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,")

  }
  tutupPembayaran() {
    this.displayDialog = false
    this.formGroup.get('nominal').reset()
    this.formGroup.get('totalTagihan').reset()
    this.formGroup.get('tipeBayar').reset()
    this.temPembayaran = []
    this.dataSourcePembayaran = this.temPembayaran
    this.totalPembayaran = 0
  }
  savePembayaran() {
    if (this.temPembayaran.length == 0) {
      this.alertService.warn('Peringatan', 'Pembayaran Belum ada ')
      return
    }
    let totalTagihan = this.formGroup.get('totalTagihan').value
    let kdPegawaiPenerima = this.formGroup.get('kdPegawaiPenerima').value
    let total = 0
    for (let i = this.temPembayaran.length - 1; i >= 0; i--) {
      total = total + parseFloat(this.temPembayaran[i].nominal)
    }
    let json = {
      'norec_transaksi': this.norecTransaksi,
      'totalbayar': total,
      'pegawaifk': kdPegawaiPenerima,
      'detail': this.temPembayaran
    }
    this.httpService.post('transaksi/pembayaran/save-pembayaran', json).subscribe(res => {
      this.selectNoBayar = res.data.nopembayaran
      this.httpService.get('transaksi/pembayaran/get-bayar-by-no?nopembayaran=' + this.selectNoBayar).subscribe(e => {
        if (e.data.length > 0) {
          let totals: any = 0
          for (let i = 0; i < e.data.length; i++) {
            const element = e.data[i];
            element.total = parseFloat(element.hargajual) * parseFloat(element.qty)
            totals = totals + element.total
          }
          this.subtotalPembayaran = parseFloat(totals).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,")

          for (let i = 0; i < e.data.length; i++) {
            const element = e.data[i];
            element.total = parseFloat(element.total).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,")
            element.hargajual = parseFloat(element.hargajual).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,")
            element.hargadiskon = parseFloat(element.hargadiskon).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,")
            element.qty = parseFloat(element.qty).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,")
          }
          this.listBayar = e.data
          this.noPembayaran = e.data[0].nopembayaran
          this.penerimaPembayaran = e.data[0].namalengkap
          this.tglPembayaran = e.data[0].tglpembayaran
          this.totalbayarNa = parseFloat(e.data[0].totalbayar).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,")
          this.isPrintBukti = true
        }
      })


    }, error => {

    })
  }
  hapusBayar(e) {
    let select = e
    for (let i = 0; i < this.temPembayaran.length; i++) {
      const element = this.temPembayaran[i];
      if (select.no == element.no) {
        this.temPembayaran.splice([i], 1)
        break
      }
    }
    this.dataSourcePembayaran = this.temPembayaran
    let subTotal: any = 0;
    for (let i = this.temPembayaran.length - 1; i >= 0; i--) {
      subTotal = subTotal + parseFloat(this.temPembayaran[i].nominal)
      this.temPembayaran[i].no = i + 1
    }
    this.totalPembayaran = parseFloat(subTotal).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,")


  }

  loadHtmlPrint(): void {

    let printContents, popupWin;
    printContents = document.getElementById('bayar-section').innerHTML;
    popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
    popupWin.document.open();
    popupWin.document.write(`
          <html>
              <head>
                  <title></title>
                  <style>
                      @media print{
                          @page {
                              size: portrait
                          }
                      }
                      table{
                        font-size:7px;
                      }
                      .table_style {
                        font-family: arial, sans-serif;
                        border-collapse: collapse;
                        width: 100%;
                 
                      }
                    
                      .td_style,
                      .th_style {
                        border-top: 1px solid #dddddd;
                        text-align: left;
                        padding: 10px;
                      }
                  
                      .tr_style:nth-child(even) {
                        background-color: #dddddd;
                      }
                      body {
                        font-family: "Source Sans Pro", "Helvetica Neue", sans-serif;
                        text-decoration: none;
                        font-size:7px;
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

