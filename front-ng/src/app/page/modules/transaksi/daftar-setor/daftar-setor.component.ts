import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
import { Observable } from 'rxjs/Rx';
import { LazyLoadEvent, Message, ConfirmDialogModule, ConfirmationService, SelectItem } from 'primeng/primeng';
import { AlertService, InfoService, Configuration, LoaderService, CacheService, AuthGuard } from '../../../../helper';
import { FormBuilder, FormGroup, FormControl } from '@angular/forms';
import { Router } from '@angular/router';
import * as moment from 'moment'

@Component({
	selector: 'app-daftar-setor',
	templateUrl: './daftar-setor.component.html',
	styleUrls: ['./daftar-setor.component.scss'],
	providers: [ConfirmationService]
})
export class DaftarSetorComponent implements OnInit {
	formGroup: FormGroup;
	now = new Date()
	dataSource: any[];
	loading: boolean = false
	listPegawai: SelectItem[]
	selectedItem: any;
	items: any
	namaProfile: any
	alamatProfile: any
	dataSourcePrint: any[]
	isPreview: boolean = false
	displayDialog: boolean = false
	dataSourcePembayaran: any[]
	totalPembayaran: any = 0
	temPembayaran: any = []
	norecTransaksi: any = null
	listTipePembayaran: SelectItem[]
	tempDataGrid: any = []
	noPembayaran: any
	tglPembayaran: any
	penerimaPembayaran: any
	listBayar: any = []
	subtotalPembayaran: any = 0
	totalbayarNa: any
	listKeterangan: SelectItem[]
	norecSetoran: any
	totalAll: any
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
		this.formGroup = this.fb.group({
			'cariNoSetor': new FormControl(null),
			'cariPenyetor': new FormControl(null),
			'cariJenis': new FormControl(null),
			'cariPenerima': new FormControl(null),
			'tglAwal': new FormControl(new Date(this.formatDate(this.now) + ' 00:00')),
			'tglAkhir': new FormControl(this.now),
			'cariKeterangan': new FormControl(null),
			'terbilang': new FormControl(null),
			'tipeBayar': new FormControl(null),
			'nominal': new FormControl(null),
			'kdPegawaiPenerima': new FormControl(null),
			'tglSetor': new FormControl(new Date()),
			'keterangan': new FormControl(null),
			'jumlahSetor': new FormControl(null),
			'penyetor': new FormControl(null),
		});
		this.formGroup.get('kdPegawaiPenerima').setValue(this.authGuard.getUserDto().kdPegawai)
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

		let noSetor = this.formGroup.get('cariNoSetor').value;
		let penyetor = this.formGroup.get('cariPenyetor').value;
		let jenis = this.formGroup.get('cariJenis').value;
		let penerima = this.formGroup.get('cariPenerima').value;

		let tglAkhir = this.formatDateFull(this.formGroup.get('tglAkhir').value);
		let tglAwal = this.formatDateFull(this.formGroup.get('tglAwal').value);
		let keterangan = this.formGroup.get('cariKeterangan').value;

		if (noSetor)
			noSetor = '&noSetor=' + noSetor
		else
			noSetor = ''

		if (penyetor)
			penyetor = '&penyetor=' + penyetor
		else
			penyetor = ''

		if (jenis)
			jenis = '&jenis=' + jenis
		else
			jenis = ''

		if (penerima)
			penerima = '&penerima=' + penerima
		else
			penerima = ''

		if (keterangan)
			keterangan = '&keterangan=' + keterangan.id
		else
			keterangan = ''

		this.loading = true
		this.httpService.get('transaksi/setoran/get-daftar-setor?tglAwal=' + tglAwal
			+ '&tglAkhir=' + tglAkhir
			+ noSetor + penerima + penyetor + jenis + keterangan
		).subscribe(res => {
			this.loading = false
			this.tempDataGrid = res.data
			let data = res.data

			if (data.length > 0) {

				for (let i = 0; i < data.length; i++) {
					data[i].jml = data[i].ttldebitkredit
					// data[i].ttldebitkredit = this.formatRupiah(data[i].ttldebitkredit, 'Rp. ');
					if (data[i].jenisdebitkredit == 'd') {
						data[i].debitjml = data[i].ttldebitkredit
						data[i].debit = this.formatRupiah(data[i].ttldebitkredit, 'Rp. ');
						data[i].kreditjml = 0
						data[i].kredit = this.formatRupiah(0, 'Rp. ');
					}
					if (data[i].jenisdebitkredit == 'k') {
						data[i].kreditjml = data[i].ttldebitkredit
						data[i].kredit = this.formatRupiah(data[i].ttldebitkredit, 'Rp. ');
						data[i].debitjml = 0
						data[i].debit = this.formatRupiah(0, 'Rp. ');
					}
				}
				this.totalAll = 0
				for (let i = 0; i < data.length; i++) {
					const element = data[i];
					this.totalAll = this.totalAll + parseFloat(element.debitjml) - parseFloat(element.kreditjml)

				}
				this.totalAll = this.formatRupiah(this.totalAll, 'Rp. ')
				this.dataSource = data
				this.dataSourcePrint = data
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
		// this.cetakBukti()
	}
	getList() {
		this.httpService.get('transaksi/setoran/get-combo').subscribe(data => {
			var getData: any = this.dataHandler.get(data);
			this.listPegawai = [];
			this.listPegawai.push({ label: '--Pilih Pegawai --', value: null });
			getData.pegawai.forEach(response => {
				this.listPegawai.push({ label: response.namalengkap, value: response.id });
			});
			this.listKeterangan = [];
			this.listKeterangan.push({ label: '--Pilih Keterangan --', value: null });
			getData.keterangansetor.forEach(response => {
				this.listKeterangan.push({ label: response.keterangansetor, value: response });
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
		this.norecSetoran = this.selectedItem.norec
		this.formGroup.get('tglSetor').setValue(new Date(this.selectedItem.tgl))
		this.formGroup.get('keterangan').setValue({
			id: this.selectedItem.keteranganfk,
			jenisdebitkredit: this.selectedItem.jenisdebitkredit,
			keterangansetor: this.selectedItem.keterangansetor,
			statusenabled: true
		})
		this.formGroup.get('jumlahSetor').setValue(this.selectedItem.ttldebitkredit)
		this.formGroup.get('kdPegawaiPenerima').setValue(this.selectedItem.pegawaipenerimafk)
		this.formGroup.get('penyetor').setValue(this.selectedItem.pegawaisetorfk)
		this.displayDialog = true


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
	hapus() {
		if (this.selectedItem == undefined) {
			this.alertService.warn('Peringatan', 'Pilih data dulu')
			return
		}
		let obj = {
			'norecSetoran': this.selectedItem.norec
		}
		this.confirmationService.confirm({
			message: 'Yakin mau menghapus data?',
			accept: () => {
				this.httpService.post('transaksi/setoran/hapus-setoran', obj).subscribe(res => {
					this.loadGrid()
				}, error => {

				})
			}
		})
	}
	onChangeJml(value: number) {

		this.httpService.get('generic/get-terbilang/' + value).subscribe(data => {
			this.formGroup.get('terbilang').setValue(data)
		})
	}
	bayar() {
		if (this.selectedItem == undefined) {
			this.alertService.warn('Peringatan', 'Pilih data dulu')
			return
		}
		if (this.selectedItem.nopembayaran != '-') {
			this.alertService.error('Peringatan', 'Transaksi Sudah Dibayar')
			return
		}
		this.displayDialog = true
		let subTotal: any = 0;
		for (let i = this.tempDataGrid.length - 1; i >= 0; i--) {
			subTotal = subTotal + parseFloat(this.tempDataGrid[i].totalall)
		}
		this.formGroup.get('totalTagihan').setValue(subTotal)
		this.formGroup.get('nominal').setValue(subTotal)
		let totaltagihan = this.formGroup.get('totalTagihan').value

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
		this.formGroup.get('tglSetor').setValue(new Date())
		this.formGroup.get('keterangan').reset()
		this.formGroup.get('jumlahSetor').reset()
		this.formGroup.get('terbilang').reset()
		this.formGroup.get('penyetor').reset()
		this.selectedItem = undefined
	}
	savePembayaran() {

		let json = this.formGroup.value
		if (this.norecSetoran) {
			json.norecSetoran = this.norecSetoran
		} else
			json.norecSetoran = null
		json.asalsetorfk = null
		json.tglSetor = moment(json.tglSetor).format('YYYY-MM-DD HH:mm')
		this.httpService.post('transaksi/setoran/save-setoran-manual', json).subscribe(res => {
			this.tutupPembayaran()
			this.loadGrid()
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

	cetakBukti() {
		if (this.selectedItem == undefined) {
			this.alertService.warn('Peringatan', 'Pilih data dulu')
			return
		}
		// if (this.selectedItem.nopembayaran == '-') {
		//   this.alertService.error('Peringatan', 'Transaksi Belum Dibayar')
		//   return
		// }
		this.httpService.get('transaksi/pembayaran/get-bayar-penerimaan-by-no?nopembayaran=' + this.selectedItem.nopembayaran).subscribe(e => {
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
					element.hargadiskon = parseFloat('0').toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,")
					element.qty = parseFloat(element.qty).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,")
				}
				this.listBayar = e.data
				this.noPembayaran = e.data[0].nopembayaran
				this.penerimaPembayaran = e.data[0].namalengkap
				this.tglPembayaran = e.data[0].tglpembayaran
				this.totalbayarNa = parseFloat(e.data[0].totalbayar).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,")

			}
		})
	}

	loadHtmlPrint(): void {
		if (this.selectedItem.nopembayaran == '-') {
			this.alertService.error('Peringatan', 'Transaksi Belum Dibayar')
			return
		}
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
	cetak(): void {
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
							font-size:10px;
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
