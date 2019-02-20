import { Inject, Injectable } from '@angular/core';
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { Observable } from 'rxjs';
import { Subject } from 'rxjs/Subject';
import { HttpClient, UserDto, AlertService } from '../';

@Injectable()
export class AuthGuard implements CanActivate {

    private subject = new Subject<boolean>();
    private tokenSubject = new Subject<UserDto>();
    private curLogin: boolean;
    private userDto: UserDto;

    constructor(private router: Router) {
        let mana = localStorage.getItem('user.data');
        if (mana !== undefined && mana !== null) {
            this.userDto = JSON.parse(mana);
        }
    }

    canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot) {
        this.isLogin();
        if (this.curLogin) {
            return true;
        } else {
            this.router.navigate(['login']/*, { queryParams: { returnUrl: state.url }}*/);
            return false;
        }
    }

    setUserDto(userDto: UserDto) {
        this.userDto = userDto;
    }

    getUserDto() {
        return this.userDto;
    }

    // getRuangan(){
    //     return this.userDto.ruangan;
    // }

    // getIdRuangan(){
    //     let mana = localStorage.getItem('idRuangan');
    //     if (mana === undefined || mana === null){
    //         return undefined;
    //     }   

    //     return JSON.parse(mana);
    // }

    // setIdRuangan(idRuangan : string){
    //     localStorage.setItem('idRuangan',idRuangan);
    // }

    checkLogin() {
        if (this.userDto != undefined && this.userDto != null) {
            this.curLogin = true;
        } else {
            this.curLogin = false;
        }
        this.tokenSubject.next(this.userDto);
        return this.curLogin;
    }

    isLogin() {
        this.checkLogin();
        this.subject.next(this.curLogin);
    }

    getInfoToken(): Observable<UserDto> {
        return this.tokenSubject.asObservable();
    }

    getInfoLogin(): Observable<boolean> {
        return this.subject.asObservable();
    }
}