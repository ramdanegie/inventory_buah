import { Component, OnInit } from '@angular/core';
import { HttpClient } from '../../../../helper/service/HttpClient';
import { DataHandler } from '../../../../helper/handler/DataHandler';
import { TableHandler } from '../../../../helper/handler/TableHandler';
import { UserLogin } from './user-login.interface';
import { Observable } from 'rxjs/Rx';
import { LazyLoadEvent, Message, ConfirmDialogModule, ConfirmationService, SelectItem } from 'primeng/primeng';
import { AlertService, InfoService, Configuration, LoaderService } from '../../../../helper';
import { FormBuilder, FormGroup, FormControl } from '@angular/forms';


@Component({
	selector: 'app-user-login',
	templateUrl: './user-login.component.html',
	styleUrls: ['./user-login.component.scss'],
	providers: [ConfirmationService]
})
export class UserLoginComponent implements OnInit {
	show:boolean;
	// item: UserLogin = new InisialUserLogin();
	item: any = {};
	formGroup: FormGroup;
	listKelompokUser: SelectItem[];
	// listPegawai: SelectItem[] = [];
	changePassword: boolean = false;
	listData: any[];
	buttonAktif: boolean = true;
	listPegawai: any[];
	disabled: boolean = true;
	constructor(private alertService: AlertService,
		private InfoService: InfoService,
		private httpService: HttpClient,
		private confirmationService: ConfirmationService,
		private dataHandler: DataHandler,
		private fb: FormBuilder,
		private loader : LoaderService
	) { }

	ngOnInit() {
		this.show = true
		this.httpService.showLoader()
		
		this.formGroup = this.fb.group({
			'idUser': new FormControl(null),
			'namaUser': new FormControl(null),
			'kataSandi': new FormControl(null),
			'kdKelompokUser': new FormControl(null),
			'pegawai': new FormControl(null),
			'changePassword': new FormControl(false)

		});
		// this.formGroup.get('kataSandi')['disable']();
		this.get();
		this.getDropdown();
	}

	get() {
		this.httpService.get('master/loginuser/get-daftar-login-user').subscribe(
			data => {
				this.listData = this.dataHandler.get(data);
			}
			,
			error => {
				this.alertService.error('Gagal Menampilkan Data', JSON.stringify(error));
			}
		);
	}

	onRowSelect(event) {
		let selected = event.data
		this.formGroup.get('idUser').setValue(selected.id);
		this.formGroup.get('namaUser').setValue(selected.namauser)
		this.formGroup.get('pegawai').setValue({ 'id': selected.objectpegawaifk, 'namalengkap': selected.namalengkap });
		this.formGroup.get('kdKelompokUser').setValue(selected.objectkelompokuserfk);
		this.formGroup.get('kataSandi')['disable']();
	}

	getPegawaiByName(event) {
		this.httpService.get('master/pegawai/get-pegawai-by-nama/' + event.query).subscribe(data => {
			this.listPegawai = data.data;
		});
	}

	reset() {
		this.formGroup.get('idUser').reset();
		this.formGroup.get('namaUser').reset();
		this.formGroup.get('kdKelompokUser').reset();
		this.formGroup.get('pegawai').reset();
		this.formGroup.get('kataSandi').reset();
		this.formGroup.get('kataSandi')['disable']();
	}
	toggleDisabled() {
		this.formGroup.get('kataSandi')[!this.disabled ? 'disable' : 'enable']();
		//   this.isEditing = !this.isEditing;
		this.disabled = !this.disabled;
	}
	simpan() {
		// this.confirmationService.confirm({
		// 	message: 'Tambahkan data?',
		// 	accept: () => {
		this.httpService.post('master/loginuser/save-login-user', this.formGroup.value).subscribe(response => {
			this.alertService.success('Success', 'Berhasil Tambah Data');
			this.reset();
			this.get()
		}, error => {
			this.alertService.error('Error', 'Terjadi kesalahan');
		});
		// 	}
		// });
	}
	hapus() {
		this.confirmationService.confirm({
			message: 'Yakin mau Menghapus data?',
			accept: () => {
				this.httpService.post('master/loginuser/delete-login-user', this.formGroup.value).subscribe(response => {
					this.alertService.success('Success', 'Berhasil Menghapus Data');
					this.reset();
					this.get()
				}, error => {
					this.alertService.error('Error', 'Terjadi kesalahan');
				});
			}
		});
	}

	getDropdown() {

		this.httpService.get('master/kelompokuser/get-all').subscribe(data => {
			var getData = this.dataHandler.get(data);
			this.listKelompokUser = [];
			this.listKelompokUser.push({ label: '--Pilih--', value: '' });
			// this.listKelompokUser.push({ label: '-', value: '-'});
			getData.forEach(response => {
				this.listKelompokUser.push({ label: response.kelompokuser, value: response.id });
				// JSON.stringify(this.listKelompokUser)
			});
		}, error => {
			this.alertService.error('Error', JSON.stringify(error));
		});
	}

}


