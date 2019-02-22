export { UserDto } from './dto/userDto';

export { InfoMsg } from './component/info/info.interface';
export { InfoService } from './component/info/info.service';
export { InfoComp } from './component/info/info.component';
export { ReportComponent } from './component/report/report.component'

export { MessageService } from './service/message.service';

export { SuperUserState, SuperUserInfo } from './service/super-user.interface';
export { SuperUserService } from './service/super-user.service';


export { AlertMsg } from './component/alert/alert.interface';
export { AlertService } from './component/alert/alert.service';
export { AlertComp } from './component/alert/alert.component';

export { LoaderService } from './component/loader/loader.service';
export { LoaderComp } from './component/loader/loader.component';
export { LoaderState } from './component/loader/loader.interface';

export { HttpClient } from './service/HttpClient';
export { AuthGuard } from './service/auth.guard.service';
export { Authentication } from './service/authentication.service';
export { FileService } from './service/FileService';
export { HelperService } from './service/HelperService';

export { CacheService } from './service/cache.service';


export { Configuration } from './config';


import * as helper from './';

export const helperComponent = [
        helper.AlertComp,
        helper.InfoComp,
        helper.LoaderComp,
        helper.ReportComponent
];

export const helperServices = [
        helper.SuperUserService,
        helper.MessageService,
        helper.HttpClient,
        helper.AuthGuard,
        helper.AlertService,
        helper.InfoService,
        helper.Authentication,
        helper.FileService,
        helper.LoaderService,
        helper.HelperService,
        helper.CacheService

];

