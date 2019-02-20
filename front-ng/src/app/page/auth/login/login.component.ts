import {Component, OnInit} from '@angular/core';
import {DropdownModule, SelectItem, InplaceModule} from 'primeng/primeng';
import {
  HttpClient,
  UserDto,
  Authentication,
  AuthGuard,
  AlertService,
  InfoService,
  Configuration
} from '../../../helper';
import {Router, ActivatedRoute} from '@angular/router';
import * as $ from 'jquery';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {

  perusahaan: string;
  modul: string;

  model: any = {};
  loading: boolean = false;
  returnUrl: string;
  pilAll: boolean = false;

  urlRes: string;

  koneksi: string = 'Login gagal, periksa koneksi jaringan.';
  userPassPil: string = 'Login gagal, periksa kembali username, password dan pilihan anda.';

  constructor(private http: HttpClient,
              private route: ActivatedRoute,
              private router: Router,
              private authentication: Authentication,
              private authGuard: AuthGuard,
              private alert: AlertService,
              private info: InfoService,
      ) {

    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || 'dashboard';
    this.urlRes = Configuration.get().apiBackend + 'images/';
  }

  ngOnInit() {
    this.pilAll = false;
  }

  loginUser() {
    this.info.hide();
    this.authGuard.setUserDto(null);
    this.loading = true;
    if((this.model.namaUser != null && this.model.namaUser != '') && 
    (this.model.kataSandi != null && this.model.kataSandi != '')) {
      this.authentication
      .login(this.model.namaUser, this.model.kataSandi)
      .subscribe(
        data => {
          this.loading = false;
          if(data != undefined) {
            if (data.token != undefined && data.token != null) {
              this.authGuard.setUserDto(data);
              this.succes()
            }
          }
        },
        error => {
          this.loading = false;
        }
      );
    } else {
      this.alert.error('Error', 'Login gagal, Username atau Password Tidak Boleh Kosong.');
      this.loading = false;
    }
    
  }

  succes() {
    this.authGuard.isLogin();
    this.info.hide();
    this.pilAll = false;
    let routerConfig = this.router.config;
    routerConfig[1] = {
      canActivate: [AuthGuard],
      path: '',
      redirectTo: localStorage.getItem('user.data') != null ? 'dashboard' : 'login',
      pathMatch: 'full'
    };
    this.router.resetConfig(routerConfig);
    this.router.navigate(['dashboard']);
  }

  kembali() {
    this.pilAll = false;
  }


 

  

 
  
}
