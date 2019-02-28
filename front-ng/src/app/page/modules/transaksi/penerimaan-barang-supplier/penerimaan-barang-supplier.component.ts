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
	selector: 'app-penerimaan-barang-supplier',
	templateUrl: './penerimaan-barang-supplier.component.html',
	styleUrls: ['./penerimaan-barang-supplier.component.scss'],
	providers: [ConfirmationService]
})
export class PenerimaanBarangSupplierComponent implements OnInit {
	formGroup: FormGroup;
	listToko: SelectItem[];
	listProduk: SelectItem[];
	listPegawai: SelectItem[];
	listSatuan: SelectItem[];
	listSupplier: SelectItem[];
	now: any = new Date;
	tempDataGrid: any = [];
	dataSource: any[];
	nomor: any = undefined;
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
			'noPenerimaan': new FormControl(null),
			'noFaktur': new FormControl(null),
			// 'namaSupplier': new FormControl(null),
			'tglPenerimaan': new FormControl(this.now),
			'kdToko': new FormControl(null),
			'kdPegawai': new FormControl(null),
			'kdProduk': new FormControl(null),
			'namaProduk': new FormControl(null),
			'qtyProduk': new FormControl(0),
			'kdSatuan': new FormControl(null),
			'hargaSatuan': new FormControl(0),
			'hargaJual': new FormControl(0),
			'total': new FormControl(0),
			'kdSupplier': new FormControl(null),
			'konversi': new FormControl(0),
			'isAutoNoTerima': new FormControl(false),
			'isAutoNoFaktur': new FormControl(false),
			'satuan': new FormControl(null),
		});
		let cache = this.cacheHelper.get('cacheUbahPenerimaanSupplier')
		if (cache != undefined) {
			this.loadFromEdit(cache)
			this.cacheHelper.set('cacheUbahPenerimaanSupplier', undefined);
		}

	}
	autoNoTerima() {
		this.formGroup.get('noPenerimaan')[this.formGroup.get('isAutoNoTerima').value ? 'disable' : 'enable']();
	}
	autoNoFaktur() {
		this.formGroup.get('noFaktur')[this.formGroup.get('isAutoNoFaktur').value ? 'disable' : 'enable']();
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
					'namaSatuan': element.satuanterima,
					'kdSatuan': element.satuanfk,
					'hargaJual': element.hargajual,
					'hargaSatuan': element.hargapenerimaan,
					'total': element.totalpenerimaan,
					'satuanterimafk': element.satuanterimafk,
					'konversi': element.konversi,
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


			this.listSupplier = [];
			this.listSupplier.push({ label: '--Pilih Supplier--', value: null });
			getData.supplier.forEach(response => {
				this.listSupplier.push({ label: response.namasupplier, value: response.id });
			});
		}, error => {
			this.alertService.error('Error', 'Terjadi kesalahan saat loading data');
		});

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
		this.nomor = undefined
	}
	resetPart() {
		this.formGroup.get('kdProduk').reset();
		this.formGroup.get('namaProduk').reset();
		this.formGroup.get('qtyProduk').setValue(0);
		this.formGroup.get('kdSatuan').reset();
		this.formGroup.get('hargaSatuan').setValue(0);
		this.formGroup.get('hargaJual').setValue(0);
		this.formGroup.get('total').setValue(0);
		this.formGroup.get('satuan').reset();
		this.formGroup.get('konversi').setValue(0);
		this.nomor = undefined
	}

	tambah() {
		let namaProduk = this.formGroup.get('namaProduk').value;
		let kdSatuan = this.formGroup.get('kdSatuan').value;
		let qtyProduk = this.formGroup.get('qtyProduk').value;
		let hargaSatuan = this.formGroup.get('hargaSatuan').value;
		let hargaJual = this.formGroup.get('hargaJual').value;
		let total = this.formGroup.get('total').value;
		let kdProduk = this.formGroup.get('kdProduk').value;
		let konversi = this.formGroup.get('konversi').value;
		let satuan = this.formGroup.get('satuan').value;
		if (!namaProduk) {
			this.alertService.warn("Peringatan", "Nama Produk harus di isi !")
			return
		}
		if (!satuan) {
			this.alertService.warn("Peringatan", "Satuan harus di isi !")
			return
		}
		if (qtyProduk == 0) {
			this.alertService.warn("Peringatan", "Qty tidak boleh nol !")
			return
		}
		if (hargaSatuan == 0) {
			this.alertService.warn("Peringatan", "Harga Satuan tidak boleh nol !")
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
					data.kdProduk = namaProduk.kdProduk
					data.namaProduk = namaProduk.namaProduk
					data.qtyProduk = qtyProduk
					data.namaSatuan = satuan.namaSatuan
					data.kdSatuan = satuan.id
					data.hargaJual = hargaJual
					data.hargaSatuan = hargaSatuan
					data.total = total
					data.konversi = konversi
					data.satuanterimafk = satuan.id
					this.tempDataGrid[i] = data;
					this.dataSource = this.tempDataGrid
				}
			}
		} else {
			data = {
				'no': nomor,
				'kdProduk': kdProduk,
				'namaProduk': namaProduk.namaProduk,
				'qtyProduk': qtyProduk,
				'namaSatuan': satuan.namaSatuan,
				'kdSatuan': satuan.id,
				'hargaJual': hargaJual,
				'hargaSatuan': hargaSatuan,
				'total': total,
				'satuanterimafk': satuan.id,
				'konversi': konversi,
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
		this.nomor = undefined
	}
	batal() {
		this.resetPart()
	}
	onChangeHargaSatuan(value: number) {
		let qty = this.formGroup.get('qtyProduk').value
		let total = qty * value
		this.formGroup.get('total').setValue(total)
		// console.log(total);
	}
	onChangeQty(value: number) {
		let kdProduk = this.formGroup.get('namaProduk').value.kdProduk
		let kdSatuan = this.formGroup.get('satuan').value.id
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

		let hargaSatuan = this.formGroup.get('hargaSatuan').value
		let total = hargaSatuan * value
		this.formGroup.get('total').setValue(total)

		// console.log(total);
	}
	changeSatuan(e) {
		let kdProduk = this.formGroup.get('namaProduk').value.kdProduk
		let kdSatuan = this.formGroup.get('satuan').value.id
		this.getMapSatuan(kdSatuan, kdProduk)
		// this.httpService.get('master/mapproduktosatuan/get?produkfk=' + kdProduk
		// 	+ '&satuantujuanfk=' + kdSatuan).subscribe(res => {
		// 		if (res.data.length > 0) {

		// 			this.formGroup.get('konversi').setValue(parseFloat(this.formGroup.get('qtyProduk').value) * parseFloat(res.data[0].hasilkonversi))
		// 		} else {
		// 			this.formGroup.get('konversi').setValue(1)
		// 		}
		// 	}, error => {

		// 	})
	}
	setValueKdSatuan() {
		this.formGroup.get('kdProduk').setValue(this.formGroup.get('namaProduk').value.kdProduk)
		this.formGroup.get('satuan').setValue({
			id: this.formGroup.get('namaProduk').value.kdSatuan,
			namaSatuan: this.formGroup.get('namaProduk').value.namaSatuan
		})
		this.getMapSatuan(null, this.formGroup.get('namaProduk').value.kdProduk)
		// this.httpService.get('master/mapproduktosatuan/get?produkfk=' + this.formGroup.get('namaProduk').value.kdProduk).subscribe(res => {
		// 	if (res.data.length > 0) {
		// 		this.listSatuan = [];
		// 		this.listSatuan.push({ label: '--Pilih Satuan--', value: null });
		// 		res.data.forEach(response => {
		// 			this.listSatuan.push({ label: response.satuantujuan, value: response.satuantujuanfk });
		// 		});
		// 		this.formGroup.get('konversi').setValue(parseFloat(this.formGroup.get('qtyProduk').value) * parseFloat(res.data[0].hasilkonversi))
		// 	} else {
		// 		this.listSatuan = [];
		// 		this.listSatuan.push({ label: '--Pilih Satuan--', value: null });
		// 		this.formGroup.get('konversi').setValue(1)
		// 	}
		// }, error => {

		// })



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
					for (let i = 0; i < res.data.length; i++) {
						const element = res.data[i];
						if (this.formGroup.get('satuan').value.id &&
							element.satuantujuanfk == this.formGroup.get('satuan').value.id) {
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
	onRowSelect(event) {
		let e = event.data
		this.nomor = e.no
		this.formGroup.get('kdProduk').setValue(e.kdProduk);
		this.formGroup.get('namaProduk').setValue({
			namaProduk: e.namaProduk, kdProduk: e.kdProduk,
			kdSatuan: e.kdSatuan, namaSatuan: e.namaSatuan
		});
		this.getMapSatuan(null, e.kdProduk)

		this.formGroup.get('satuan').setValue({
			id: e.satuanterimafk,
			namaSatuan: e.namaSatuan,
		});
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
			'isAutoNoTerima': this.formGroup.get('isAutoNoTerima').value,
			'isAutoNoFaktur': this.formGroup.get('isAutoNoFaktur').value,
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

}
