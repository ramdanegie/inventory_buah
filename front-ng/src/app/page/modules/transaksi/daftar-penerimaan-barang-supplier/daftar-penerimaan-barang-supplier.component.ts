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
	selector: 'app-daftar-penerimaan-barang-supplier',
	templateUrl: './daftar-penerimaan-barang-supplier.component.html',
	styleUrls: ['./daftar-penerimaan-barang-supplier.component.scss'],
	providers: [ConfirmationService]
})
export class DaftarPenerimaanBarangSupplierComponent implements OnInit {

	formGroup: FormGroup;
	now = new Date()
	dataSource: any[];
	loading: boolean = false
	listPegawai: SelectItem[]
	selectedItem: any;
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
		this.formGroup = this.fb.group({
			'noPenerimaan': new FormControl(null),
			'noFaktur': new FormControl(null),
			'namaSupplier': new FormControl(null),
			'kdPegawai': new FormControl(null),
			'tglAwal': new FormControl(new Date(this.formatDate(this.now) + ' 00:00')),
			'tglAkhir': new FormControl(this.now),
		});
		this.getList()
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
		let noPenerimaan = this.formGroup.get('noPenerimaan').value;
		let noFaktur = this.formGroup.get('noFaktur').value;
		let namaSupplier = this.formGroup.get('namaSupplier').value;
		let tglAkhir = this.formatDateFull(this.formGroup.get('tglAkhir').value);
		let tglAwal = this.formatDateFull(this.formGroup.get('tglAwal').value);
		let kdPegawai = this.formGroup.get('kdPegawai').value;

		if (noPenerimaan)
			noPenerimaan = '&nopenerimaan=' + noPenerimaan
		else
			noPenerimaan = ''

		if (noFaktur)
			noFaktur = '&nofaktur=' + noFaktur
		else
			noFaktur = ''

		if (namaSupplier)
			namaSupplier = '&namasupplier=' + namaSupplier
		else
			namaSupplier = ''

		if (kdPegawai)
			kdPegawai = '&kdpegawai=' + kdPegawai
		else
			kdPegawai = ''

		this.loading = true
		this.httpService.get('transaksi/penerimaan/get-daftar-penerimaan?tglAwal=' + tglAwal
			+ '&tglAkhir=' + tglAkhir
			+ noPenerimaan + noFaktur + namaSupplier + kdPegawai
		).subscribe(res => {
			this.loading = false
			let data = res.data
			if (data.length > 0) {
				for (let i = 0; i < data.length; i++) {
					data[i].total = this.formatRupiah(data[i].total, 'Rp. ');
					for (let j = 0; j < data[i].details.length; j++) {
						const element = data[i].details[j]
						// element.qtypenerimaan  = this.formatRupiah(element.qtypenerimaan, '');
						element.hargapenerimaan = this.formatRupiah(element.hargapenerimaan, 'Rp. ');
						element.totalpenerimaan = this.formatRupiah(element.totalpenerimaan, 'Rp. ');
						element.hargajual = this.formatRupiah(element.hargajual, 'Rp. ');
					}
				}
				this.dataSource = data
			} else {
				this.loading = false
				this.alertService.info('Informasi', 'Data tidak ada')
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

		}, error => {
			this.alertService.error('Error', 'Terjadi kesalahan saat loading data');
		});

	}
	ubahPenerimaan() {
		if (this.selectedItem == undefined) {
			this.alertService.warn('Peringatan', 'Pilih data dulu')
			return
		}
		var cache = {
			0: this.selectedItem.norec,
			1: 'EditTerima',
		}

		this.cacheHelper.set('cacheUbahPenerimaanSupplier', cache);
		this.router.navigate(['/penerimaan-barang-supplier'])
	}
	hapusPenerimaan() {
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
				this.httpService.post('transaksi/penerimaan/delete-penerimaan', obj).subscribe(res => {
					this.loadGrid()
				}, error => {

				})
			}
		})
	}
}
