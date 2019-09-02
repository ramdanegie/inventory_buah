import { Component, OnInit } from '@angular/core';
import { ConfirmationService, SelectItem } from 'primeng/primeng';
import { AlertService } from '../../../../demo';
import { InfoService, HttpClient, LoaderService, CacheService, AuthGuard } from '../../../../helper';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { FormBuilder, FormGroup, FormGroupDirective, FormControl } from '@angular/forms';
import { Router } from '@angular/router';

@Component({
	selector: 'app-daftar-closing-pembayaran',
	templateUrl: './daftar-closing-pembayaran.component.html',
	styleUrls: ['./daftar-closing-pembayaran.component.scss'],
	providers: [ConfirmationService]
})
export class DaftarClosingPembayaranComponent implements OnInit {
	formGroup: FormGroup;
	loading: boolean = false
	dataSource: any[];
	now = new Date()
	selectedItem: any
	listPegawai: SelectItem[]
	displayDialog :boolean
	constructor(private alertService: AlertService,
		private InfoService: InfoService,
		private httpService: HttpClient,
		private confirmationService: ConfirmationService,
		private dataHandler: DataHandler,
		private fb: FormBuilder,
		private loader: LoaderService,
		private router: Router,
		private cacheHelper: CacheService,
		private authGuard: AuthGuard) { }

	ngOnInit() {
		this.formGroup = this.fb.group({
			'noclosing': new FormControl,
			'tglAwal': new FormControl(new Date(this.formatDate(this.now) + ' 00:00')),
			'tglAkhir': new FormControl(this.now),
			'kdPegawaiPenerima': new FormControl
		});
		this.httpService.get('transaksi/penerimaan/get-list-data').subscribe(data => {
			var getData: any = this.dataHandler.get(data);
			this.listPegawai = [];
			this.listPegawai.push({ label: '--Pilih --', value: null });
			getData.pegawai.forEach(response => {
				this.listPegawai.push({ label: response.namalengkap, value: response.id });
			});


		}, error => {
			this.alertService.error('Error', 'Terjadi kesalahan saat loading data');
		});

		this.loadGrid()
	}
	onRowSelect(event) {
		this.selectedItem = event.data
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
	cari() {
		this.loadGrid()
	}
	loadGrid() {
		let noPembayaran = this.formGroup.get('noclosing').value;
		let tglAkhir = this.formatDateFull(this.formGroup.get('tglAkhir').value);
		let tglAwal = this.formatDateFull(this.formGroup.get('tglAwal').value);
		this.loading = true
		this.httpService.get('transaksi/setoranpenjualan/get-data-closing?tglAwal=' + tglAwal
			+ '&tglAkhir=' + tglAkhir
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
		},error =>{
			this.loading = false
			this.alertService.info('Informasi', 'Data tidak ada')
			this.dataSource = []
		})
	}
	showSetor(){
		let dataSetor = []
		for (let i = 0; i < this.dataSource.length; i++) {
			const element = this.dataSource[i];
			if(element.nosetor == null){
				dataSetor.push(element)
			}
		}
		if (dataSetor.length == 0) {
			this.alertService.info('Peringatan', 'Semua data semua sudah di setor')
			return
		}
	}
	simpanSetoran() {
		if (this.formGroup.get('kdPegawaiPenerima').value == undefined) {
			this.alertService.info('Peringatan', 'Penerima belum di pilih')
			return
		}
		
		let dataSetor = []
		for (let i = 0; i < this.dataSource.length; i++) {
			const element = this.dataSource[i];
			if(element.nosetor == null){
				dataSetor.push(element)
			}
		}
		if (dataSetor.length == 0) {
			this.alertService.info('Peringatan', 'Semua data semua sudah di setor')
			return
		}
		var data = {
			'data':dataSetor,
			'penerimafk': this.formGroup.get('kdPegawaiPenerima').value,
		}
		// this.confirmationService.confirm({
		// 	message: 'Yakin mau setor semua ?',
		// 	accept: () => {
		this.httpService.post('transaksi/setoran/save-setoran-dari-closing', data).subscribe(res => {
			this.displayDialog = false
			this.loadGrid()
			
		}, error => {

		})
		// }
		// })
	}
}
