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
	selector: 'app-daftar-pembayaran',
	templateUrl: './daftar-pembayaran.component.html',
	styleUrls: ['./daftar-pembayaran.component.scss'],
	providers: [ConfirmationService]
})
export class DaftarPembayaranComponent implements OnInit {

	formGroup: FormGroup;
	now = new Date()
	dataSource: any[];
	dataSource2: any[];
	loading: boolean = false
	listKasir: SelectItem[]
	selectedItem: any;
	nomor: any = undefined;
	tpId: any = undefined;
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
		this.formGroup = this.fb.group({
			'noPembayaran': new FormControl,
			'kdPegawai': new FormControl,
			'pegawaiFk': new FormControl,
			'tglAwal': new FormControl(new Date(this.formatDate(this.now) + ' 00:00')),
			'tglAkhir': new FormControl(this.now),
		});
		this.formGroup.get('pegawaiFk').setValue(this.authGuard.getUserDto().kdPegawai)
		this.getList()
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
	getList() {
		this.httpService.get('transaksi/penerimaankasir/get-combo').subscribe(datt => {
			var getData: any = this.dataHandler.get(datt);
			this.listKasir = [];
			this.listKasir.push({ label: '--Pilih Pegawai --', value: null });
			getData.pegawai.forEach(response => {
				this.listKasir.push({ label: response.namalengkap, value: response.id });
			});

		}, error => {
			this.alertService.error('Error', 'Terjadi kesalahan saat loading data');
		});

	}
	loadGrid() {
		let noPembayaran = this.formGroup.get('noPembayaran').value;
		let tglAkhir = this.formatDateFull(this.formGroup.get('tglAkhir').value);
		let tglAwal = this.formatDateFull(this.formGroup.get('tglAwal').value);
		let kdPegawai = this.formGroup.get('kdPegawai').value;

		// if (kdPegawai)
		// 	kdPegawai = '&kdpegawai=' + kdPegawai
		// else
		// 	kdPegawai = ''

		this.loading = true
		this.httpService.get('transaksi/penerimaankasir/get-penetimaan-kasir?tglAwal=' + tglAwal
			+ '&tglAkhir=' + tglAkhir
			+ '&kdPegawai=' + kdPegawai
		).subscribe(res => {
			this.loading = false
			let data = res.data
			let data2 = res.data2
			if (data.length > 0) {
				for (let i = 0; i < data.length; i++) {
					data[i].no = i + 1
				}
				this.dataSource = data
				this.dataSource2 = data2
			} else {
				this.loading = false
				this.alertService.info('Informasi', 'Data tidak ada')
				this.dataSource = []
			}
		})
	}
	loadGrid2() {
		let noPembayaran = this.formGroup.get('noPembayaran').value;
		let tglAkhir = this.formatDateFull(this.formGroup.get('tglAkhir').value);
		let tglAwal = this.formatDateFull(this.formGroup.get('tglAwal').value);
		let kdPegawai = this.formGroup.get('kdPegawai').value;

		// if (kdPegawai)
		// 	kdPegawai = '&kdpegawai=' + kdPegawai
		// else
		// 	kdPegawai = ''

		this.loading = true
		this.httpService.get('transaksi/penerimaankasir/get-penetimaan-kasir?tglAwal=' + tglAwal
			+ '&tglAkhir=' + tglAkhir
			+ '&kdPegawai=' + kdPegawai
		).subscribe(res => {
			this.loading = false
			let data = res.data
			if (data.length > 0) {
				for (let i = 0; i < data.length; i++) {
					data[i].no = i + 1
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
		let kdPegawai = this.formGroup.get('kdPegawai').value;
		if (kdPegawai == undefined) {
			this.alertService.warn('Peringatan', 'Pilih Nama Kasir terlebih dahulu!!!')
			return
		}
		if (kdPegawai == "") {
			this.alertService.warn('Peringatan', 'Pilih Nama Kasir terlebih dahulu!!!')
			return
		}
		if (kdPegawai == null) {
			this.alertService.warn('Peringatan', 'Pilih Nama Kasir terlebih dahulu!!!')
			return
		}
		this.dataSource2 = []
		this.loadGrid()
	}
	onRowSelect(event) {
		this.dataSource2 = []
		let e = event.data
		this.tpId = e.tpid
		let noPembayaran = this.formGroup.get('noPembayaran').value;
		let tglAkhir = this.formatDateFull(this.formGroup.get('tglAkhir').value);
		let tglAwal = this.formatDateFull(this.formGroup.get('tglAwal').value);
		let kdPegawai = this.formGroup.get('kdPegawai').value;

		this.loading = true
		this.httpService.get('transaksi/penerimaankasir/get-penetimaan-kasir?tglAwal=' + tglAwal
			+ '&tglAkhir=' + tglAkhir
			+ '&kdPegawai=' + kdPegawai
		).subscribe(res => {
			this.loading = false
			let data2 = res.data2
			if (data2.length > 0) {
				for (let i = 0; i < data2.length; i++) {
					data2[i].no = i + 1
				}
				this.dataSource2 = data2
			} else {
				this.loading = false
				this.alertService.info('Informasi', 'Data tidak ada')
				this.dataSource2 = []
			}
		})
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
	penerimaanFix() {
		if (this.selectedItem == undefined) {
			this.alertService.warn('Peringatan', 'Pilih data dulu')
			return
		}
		var cache = {
			0: this.selectedItem.norec,
			1: 'EditTerima',
		}

		this.cacheHelper.set('cacheUbahPenerimaanSupplier', cache);
		this.router.navigate(['/penerimaan-barang-fix'])
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
	simpanSetor() {
		let data = this.dataSource
		if (data.length > 0) {
			for (let i = 0; i < data.length; i++) {
				data[i].no = i + 1
			}
			this.dataSource = data
		}

		let jsonSave = {
			'details': this.dataSource
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
	closing() {
		let detail = []
		let totalpenerimaan = []
		for (let i = 0; i < this.dataSource2.length; i++) {
			const element = this.dataSource2[i];
			detail.push({ 'norecSP': element.norecSP })
		}
		for (let i = 0; i < this.dataSource.length; i++) {
			const element = this.dataSource[i];
			totalpenerimaan = element.totalpenerimaan
		}
		let jsonSave = {
			'pegawaifk': this.formGroup.get('pegawaiFk').value,
			'totalpenerimaan' : totalpenerimaan,
			'detail': detail
		}
		this.confirmationService.confirm({
			message: 'Yakin mau menyimpan data?',
			accept: () => {
				this.httpService.post('transaksi/penerimaankasir/save-closing', jsonSave).subscribe(res => {
					// this.formGroup.reset()
				}, error => {

				})
			}
		})
	}
}
