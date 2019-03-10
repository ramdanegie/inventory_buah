import { Injectable } from '@angular/core';
import { Http, Headers, Response } from '@angular/http';
import { Observable } from 'rxjs/Observable';
import { Router } from '@angular/router';
import { Configuration, MessageService, AuthGuard, InfoService, UserDto } from '../';
import { AlertService } from '../../helper';

import 'rxjs/add/operator/map'

@Injectable()
export class Authentication {

  authDto: any;
  superDto: any;

  constructor(private http: Http, private info: InfoService, private alert: AlertService, ) {
  }

  delete_cookie(name: string) {
    var today = new Date();
    var expr = new Date(today.getTime() + (-1 * 24 * 60 * 60 * 1000));
    document.cookie = name + '=;expires=' + (expr.toUTCString());
  }

  private createUserDto(user: any): UserDto {
    let userDTO: UserDto;

    let iKdUser = user.kdUser;
    let iKdPegawai = user.pegawai.id;

    userDTO = {
      id: user.kdUser,
      token: user.token,//user[Configuration.get().headerToken],
      waktuLogin: new Date(),
      namaUser: user.namaUser,
      kdUser: iKdUser,
      encrypted: user.kataSandi,
      idPegawai: iKdPegawai,
      kdPegawai: iKdPegawai,
      pegawai: user.pegawai,
      namaPerusahaan: '',
      kelompokUser: user.kelompokUser,
      profile: {
        namaProfile: 'King Banana',
        alamatProfile: 'Jln. Jakarta No.01, DKI Jakarta'
      }
    };
    return userDTO;
  }

  login(id: string, password: string) {
    window.localStorage.clear();

    this.delete_cookie('authorization');
    this.delete_cookie('statusCode');
    this.delete_cookie('io');

    if (!Date.now) {
      Date.now = function now() {
        return new Date().getTime();
      };
    }

    return this.http.post(Configuration.get().apiBackend + 'auth/sign-in', {
      namaUser: id.trim(),
      kataSandi: password.trim()
    })
      .map((response: Response) => {
        // console.log(response);
        let temp_response = JSON.parse(response["_body"]);

        if (temp_response.code == 200) {
          let user = response.json();
          this.authDto = user;
          if (user) {
            let userDTO = this.createUserDto(user);
            localStorage.setItem('user.data', JSON.stringify(userDTO));
            return userDTO;
          }
          return user;
        } else {
          if (temp_response.code == 500) {
            this.alert.error('Error', 'Login gagal, Username atau Password Salah.');
          } else {
            this.alert.error('Error', 'Login gagal, Periksa Koneksi Jaringan.');
          }
        }
      }, error => {
        this.alert.error('Error', 'Login gagal, Username atau Password Salah.');

      });
  }

  logProfile(authDto: any) {
    // console.log(authDto);
    return this.http.post(Configuration.get().apiBackend + 'register/set-profile', authDto)
      .map((response: Response) => {
        let user = response.json();
        this.authDto = user;
        if (user) {
          let userDTO = this.createUserDto(user);
          localStorage.setItem('user.data', JSON.stringify(userDTO));
          return userDTO;
        }
        return user;
      });
  }

  logModulApp(authDto: any) {
    return this.http.post(Configuration.get().apiBackend + 'register/set-modul-aplikasi', authDto)
      .map((response: Response) => {
        let user = response.json();
        this.authDto = user;
        if (user) {
          let userDTO = this.createUserDto(user);
          localStorage.setItem('user.data', JSON.stringify(userDTO));
          return userDTO;
        }
        return user;
      });
  }

  logRuangan(authDto: any) {
    return this.http.post(Configuration.get().apiBackend + 'register/set-ruangan', authDto)
      .map((response: Response) => {
        let user = response.json();
        this.authDto = user;
        if (user) {
          let userDTO = this.createUserDto(user);
          localStorage.setItem('user.data', JSON.stringify(userDTO));
          return userDTO;
        }
        return user;
      });
  }


  loginSuperUser(id: string, password: string) {
    return this.http.post(Configuration.get().authLogin + '/auth/sign-in/login',
      { namaUser: id.trim(), kataSandi: password.trim() })
      .map((response: Response) => {
        let user = response.json();
        if (user && user[Configuration.get().headerToken]) {
          let userDTO = this.createUserDto(user);
          return userDTO;
        }
        return user;
      });
  }

  logout(authGuard: AuthGuard, info: InfoService, router: Router) {
    var data = JSON.parse(localStorage.getItem('user.data'));
    return this.http.post(Configuration.get().apiBackend + 'auth/sign-out', data)
      .map((response: Response) => {

        let user = response.json();
        return user;
      }).subscribe(
        result => {
          if (result.code == 200) {
            localStorage.removeItem('user.data');
            authGuard.setUserDto(null);
            authGuard.isLogin();
            router.navigate(['login']);
            this.alert.success('Logout', 'Berhasil Logout.');
          } else {
            this.alert.error('Logout', 'Logout gagal, Periksa Koneksi Jaringan.');
          }

        },
        error => {
          this.alert.error('Logout', 'Logout gagal, Periksa Koneksi Jaringan.');
        }
      );
  }
}
