import { Injectable, OnDestroy } from '@angular/core';
import {
  Http,
  RequestOptions,
  RequestOptionsArgs,
  URLSearchParams,
  Headers,
  Response,
  ResponseContentType
} from '@angular/http';
import { Subscription } from 'rxjs/Subscription';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/finally';

import { Router } from '@angular/router';

import {
  Configuration,
  AuthGuard,
  UserDto,
  AlertService,
  InfoService,
  LoaderService,
  SuperUserService,
  SuperUserState
} from '../';

@Injectable()
export class HttpClient implements OnDestroy {

  infoToken: Subscription;
  userDto: UserDto;
  serviceData: any;
  method: any;
  url: string;
  data: any;
  errorMessage: any;
  callBack: (res: any) => any;

  superUserToken: string;
  isSuperUserReq: boolean = false;

  listSucces = [200, 201, 202, 203, 204, 205, 206, 207, 208, 209, 210]

  constructor(private http: Http,
    private alert: AlertService,
    private info: InfoService,
    private loader: LoaderService,
    private superUser: SuperUserService,
    private router: Router) {
  }

  ngOnDestroy() {
    this.infoToken.unsubscribe();
    this.infoToken = null;
  }

  navigateURL() {
    this.router.navigate(['login']);
  }

  setAuthGuard(authGuard: AuthGuard) {
    if (this.infoToken !== undefined && this.infoToken !== null) {
      this.infoToken.unsubscribe();
      this.infoToken = null;
    }
    this.infoToken = authGuard.getInfoToken().subscribe(userDto => this.userDto = userDto);
  }

  beforeUploadFile(event) {
    if (this.userDto !== undefined && this.userDto !== null) {
      event.xhr.setRequestHeader('X-url', window.location.hash);
      event.xhr.setRequestHeader('X-token', this.userDto.token);
      event.xhr.setRequestHeader('token', this.userDto.token);

    }
  }

  showLoader(): void {
    this.loader.show();
  }

  hideLoader(): void {
    this.loader.hide();
  }

  createAuthorizationHeader(headers: Headers, customToken: string = null) {
    headers.set('Content-Type', 'application/json');
    headers.set('Accept', 'application/json');
    if (this.userDto !== undefined && this.userDto !== null) {
      headers.set('X-id-user', this.userDto.kdUser);
      headers.set('X-user', this.userDto.namaUser);
      headers.set('X-id-pegawai', this.userDto.kdPegawai);
      headers.set('X-url', window.location.hash);
      if (customToken === undefined || customToken == null) {
        headers.set('X-token', this.userDto.token);
      } else {
        headers.set('X-token', customToken);
      }
    }
  }
  createAuthorizationHeaderUpload(headers: Headers, customToken: string = null) {
    headers.set('Accept', 'application/json');
    if (this.userDto !== undefined && this.userDto !== null) {
      headers.set('X-id-user', this.userDto.kdUser);
      headers.set('X-user', this.userDto.namaUser);
      headers.set('X-id-pegawai', this.userDto.kdPegawai);
      headers.set('X-url', window.location.hash);
      if (customToken === undefined || customToken == null) {
        headers.set('X-token', this.userDto.token);
      } else {
        headers.set('X-token', customToken);
      }
    }
  }

  getForced(url, callBack: (res: any) => any = null, auth: boolean = false, tokenSuper: string = null) {
    this.method = 0;
    this.url = url;
    this.isSuperUserReq = auth;
    this.callBack = callBack;
    this.showLoader();

    const headers = new Headers();
    if (this.userDto !== undefined && this.userDto !== null) {
      if (auth) {
        headers.set('X-token', tokenSuper);
      } else {
        headers.set('X-token', this.userDto.token);
      }

    }

    headers.set('Content-Type', 'application/pdf');
    headers.set('Accept', 'application/pdf');
    const options = new RequestOptions({ headers: headers });
    return this.http.get(url, {
      headers: headers, withCredentials: true,
      responseType: ResponseContentType.Blob
    })
      .map((res: Response) => {
        if (callBack !== undefined && callBack !== null) {
          callBack(res.blob());
        }
        return res.blob();
      })
      .catch(error => {
        this.handleError(error);
        return this.reject();
      })
      .finally(() => {
        this.hideLoader();
      });
  }

  get(url, callBack: (res: any) => any = null, auth: boolean = false, tokenSuper: string = null) {
    this.method = 1;
    this.url = url;
    this.isSuperUserReq = auth;
    this.callBack = callBack;
    this.showLoader();

    const headers = new Headers();
    this.createAuthorizationHeader(headers, tokenSuper);
    const options = new RequestOptions({ headers: headers });
    return this.http.get(Configuration.get().apiBackend + url
      , { headers: headers, withCredentials: false }
    )
      .map((res: Response) => {
        if (callBack !== undefined && callBack !== null) {
          callBack(res.json());
        }
        return res.json();
      })
      .catch(error => {
        this.handleError(error);
        return this.reject();
      })
      .finally(() => {
        this.hideLoader();
      });
  }

  capitalizeContentJSON(data) {
    for (var key in data) {
      // console.log(key);
      if (data.hasOwnProperty(key)) {
        if (data[key] == null || data[key] == '') {
          // DO NOTHING
        } else {
          if (data[key] instanceof Array) {
            for (let a = 0; a < data[key].length; a++) {
              for (var abc in data[key][a]) {
                if (data[key][a].hasOwnProperty(abc)) {
                  if (data[key][a][abc] == null || data[key][a][abc] == '') {
                    // DO NOTHING
                  } else {
                    data[key][a][abc] = data[key][a][abc].toString().toUpperCase();
                  }
                }
              }
              this.capitalizeContentJSON(data[key][a]);
            }
          } else {
            data[key] = data[key].toString().toUpperCase();
          }
        }
      }
    }
  }

  post(url, data, callBack: (res: any) => any = null, auth: boolean = false, tokenSuper: string = null) {
    this.createAuthorizationHeader
    this.method = 2;
    this.url = url;
    this.data = data;
    this.isSuperUserReq = auth;
    this.callBack = callBack;
    this.showLoader();

    const headers = new Headers();
    this.createAuthorizationHeader(headers, tokenSuper);
    const options = new RequestOptions({ headers: headers, withCredentials: false });
    // console.log(JSON.stringify(data));
    return this.http.post(Configuration.get().apiBackend + url, data, options)
      .map((res: Response) => {
        this.responseSuccess(res);
        if (callBack !== undefined && callBack !== null) {
          callBack(res.json());
        }
        return res.json();
      })
      .catch(error => {
        this.handleError(error);
        return this.reject();
      })
      .finally(() => {
        this.hideLoader();
      });
  }

  update(url, data, callBack: (res: any) => any = null, auth: boolean = false, tokenSuper: string = null) {
    this.method = 3;
    this.url = url;
    this.data = data;
    this.isSuperUserReq = auth;
    this.callBack = callBack;
    this.showLoader();

    const headers = new Headers();
    this.createAuthorizationHeader(headers, tokenSuper);
    const options = new RequestOptions({ headers: headers, withCredentials: false });
    // console.log(JSON.stringify(data));
    return this.http.put(Configuration.get().apiBackend + url, data, options)
      .map((res: Response) => {
        // debugger;
        // this.alert.info('Ubah', 'Data berhasil diubah.');
        if (callBack !== undefined && callBack !== null) {
          callBack(res.json());
        }
        return res.json();
      })
      .catch(error => {
        this.handleError(error);
        return this.reject();
      })
      .finally(() => {
        this.hideLoader();
      });
  }


  delete(url, callBack: (res: any) => any = null, auth: boolean = false, tokenSuper: string = null) {
    this.method = 4;
    this.url = url;
    this.isSuperUserReq = auth;
    this.callBack = callBack;
    this.showLoader();

    const headers = new Headers();
    this.createAuthorizationHeader(headers, tokenSuper);
    const options = new RequestOptions({ headers: headers, withCredentials: false });
    return this.http.delete(Configuration.get().apiBackend + url, options)
      .map((res: Response) => {
        this.alert.info('Hapus', 'Data berhasil dihapus.');
        if (callBack !== undefined && callBack !== null) {
          callBack(res.json());
        }
        return res.json();
      })
      .catch(error => {
        this.handleError(error);
        return this.reject();
      })
      .finally(() => {
        this.hideLoader();
      });
  }

  upload(url, formdata: any) {
    const headers = new Headers();
    headers.append('Accept', 'application/json');
    const options = new RequestOptions({ headers: headers });
    return this.http.post(Configuration.get().apiBackend + url, formdata, options).catch(error => {
      this.handleError(error);
      return this.reject();
    });
  }
  uploadFile(url, formdata: any, tokenSuper: string = null) {
    const headers = new Headers();
    this.createAuthorizationHeaderUpload(headers, tokenSuper);
    const options = new RequestOptions({ headers: headers });
    return this.http.post(Configuration.get().apiBackend + url, formdata, options).catch(error => {
      this.handleError(error);
      return this.reject();
    });
  }
  upload_v2(url, formdata: any, callBack: (res: any) => any = null, auth: boolean = false, tokenSuper: string = null) {
    // console.log(data);
    // this.capitalizeContentJSON(data);
    this.createAuthorizationHeaderUpload
    this.method = 2;
    this.url = url;
    this.data = formdata;
    this.isSuperUserReq = auth;
    this.callBack = callBack;
    this.showLoader();

    const headers = new Headers();
    this.createAuthorizationHeaderUpload(headers, tokenSuper);
    const options = new RequestOptions({ headers: headers, withCredentials: false });
    console.log(JSON.stringify(formdata));
    return this.http.post(Configuration.get().apiBackend + url, formdata, options)
      .map((res: Response) => {
        if (callBack !== undefined && callBack !== null) {
          callBack(res.json());
        }
        return res.json();
      })
      .catch(error => {
        this.handleError(error);
        return this.reject();
      })
      .finally(() => {
        this.hideLoader();
      });
  }

  private reject() {
    return Promise.reject('koneksi terputus');
  }

  private handleError(error: Response | any) {

    // console.log(error);
    if (error.status == 0) {
      if (this.isSuperUserReq) {
        this.superUser.setInfo('Kesalahan : Maaf, koneksi ke server terputus, silahkan coba lagi.');
      } else {
        this.alert.warn('Peringatan', 'Maaf, koneksi ke server terputus, silahkan coba lagi.');
      }
    } else if (error.status == 500) {

      if (error._body == '') {
        this.errorMessage = {};
        let errorText = 'Error tidak diketahui';
        if (this.isSuperUserReq) {
          this.superUser.setInfo('Kesalahan : ' + errorText);
        } else {
          this.alert.warn('Kesalahan', errorText);
        }
      } else {
        this.errorMessage = JSON.parse(error._body)
        // let errorText = '';
        // for (let i = 0; i < this.errorMessage.errors.length; i++) {
        //   errorText += this.errorMessage.errors[i].error;
        // }
        if (this.isSuperUserReq) {
          this.superUser.setInfo('Kesalahan : ' + this.errorMessage.message );
        } else {
          this.alert.warn('Kesalahan', this.errorMessage.message);
        }
      }

    } else if (error.status == 401) {
      if (error.headers.get('RequireSupervisor') == 'true') {
        if (this.isSuperUserReq) {
          this.superUser.setInfo('Peringatan : Tidak punya hak akses, silahkan hubungi administrator.');
        } else {
          this.superUser.setMethod(this.method);
          this.superUser.setData(this.data);
          this.superUser.setUrl(this.url);
          this.superUser.setCallBack(this.callBack);
          this.superUser.show();
        }
      } else {
        this.alert.warn('Peringatan', 'Tidak punya hak akses, silahkan coba login ulang atau hubungi administrator.');
      }
    } else if (error.status == 403) {
      if (this.isSuperUserReq) {
        this.superUser.setInfo('Peringatan : Maaf, sesi sudah berakhir, silahkan login ulang.');
      } else {
        this.alert.error('Kesalahan', 'Maaf, sesi sudah berakhir, silahkan login ulang.');
      }
    } else if (error.status == 404) {
      if (this.isSuperUserReq) {
        this.superUser.setInfo('Peringatan : Maaf, halaman API tidak ditemukan.');
      } else {
        this.alert.error('Kesalahan', 'Maaf, halaman API tidak ditemukan.');
      }
    } else if (error.status == 503 || error.status == 504) {
      if (this.isSuperUserReq) {
        this.superUser.setInfo('Peringatan : Maaf, server time out, tidak dapat melayani permintaan. Silahkan Ulangi.');
      } else {
        this.alert.warn('Peringatan', 'Maaf, server time out, tidak dapat melayani permintaan. Silahkan Ulangi.');
      }
    } else if (error.status == 400 || error.status == 400) {
      if (JSON.parse(error._body).batal !== undefined) {
        this.alert.warn('Error', JSON.parse(error._body).message);
      } else {
        // if (this.isSuperUserReq) {
        //   this.superUser.setInfo('Maaf Data Sudah Ada');
        // } else {
        //   this.alert.warn('Maaf', 'Data Sudah Ada');
        // }
        this.alert.error('Error', JSON.parse(error._body).message);
      }
    }
    /* else {
     this.alert.error('Kesalahan', 'Maaf, ada kesalahan.');
     }*/
  }

  private responseSuccess(res: Response | any) {
    if (this.listSucces.includes(res.status)) {
      let message = JSON.parse(res._body).message
      if (message) {
        this.alert.success('Sukses', message);
      } else {
        this.alert.success('Sukses', 'Data berhasil disimpan');
      }
    }
  }
}

/// LIST ERROR
// 401 Unauthorized
// 403 Forbidden (session expired)
// 404 Page Not Found
// 405 Method (POST, PUT, DELETE)
// 500 Server Error
// 0 Koneksi error
// 200-299 OK
