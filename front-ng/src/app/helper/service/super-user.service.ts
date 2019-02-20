import { Injectable } from '@angular/core';
import { Router, NavigationStart } from '@angular/router';
import { Observable } from 'rxjs';
import { Subject } from 'rxjs/Subject';
import { SuperUserState, SuperUserInfo } from '../';


@Injectable()
export class SuperUserService {

  private subject = new Subject<SuperUserState>();
  private infoSubject = new Subject<SuperUserInfo>();

  method: any;
  url: string;
  data: any;
  info: string;
  callBack: (res: any) => any;

  setMethod(method: any) {
    this.method = method;
  }

  setData(data: any) {
    this.data = data;
  }

  setCallBack(callBack: any) {
    this.callBack = callBack;
  }

  setUrl(url: string) {
    this.url = url;
  }

  setInfo(str: string) {
    this.info = str;
    this.infoSubject.next(<SuperUserInfo>{ info: this.info, error: true });
  }

  show() {
    this.subject.next(<SuperUserState>{ show: true, method: this.method, data: this.data, url: this.url, callBack: this.callBack });
  }

  hide() {
    this.subject.next(<SuperUserState>{ show: false, method: this.method, data: this.data, url: this.url, callBack: this.callBack });
  }

  getState(): Observable<SuperUserState> {
    return this.subject.asObservable();
  }

  getStateInfo(): Observable<SuperUserInfo> {
    return this.infoSubject.asObservable();
  }

}