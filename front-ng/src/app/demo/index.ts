export { DashboardDemoComponent } from './view/dashboarddemo.component';
export { SampleDemoComponent } from './view/sampledemo.component';
export { FormsDemoComponent } from './view/formsdemo.component';
export { DataDemoComponent } from './view/datademo.component';
export { PanelsDemoComponent } from './view/panelsdemo.component';
export { OverlaysDemoComponent } from './view/overlaysdemo.component';
export { MenusDemoComponent } from './view/menusdemo.component';
export { MessagesDemoComponent } from './view/messagesdemo.component';
export { MiscDemoComponent } from './view/miscdemo.component';
export { EmptyDemoComponent } from './view/emptydemo.component';
export { ChartsDemoComponent } from './view/chartsdemo.component';
export { FileDemoComponent } from './view/filedemo.component';
export { UtilsDemoComponent } from './view/utilsdemo.component';
export { DocumentationComponent } from './view/documentation.component';

export { CarService } from './service/carservice';
export { CountryService } from './service/countryservice';
export { EventService } from './service/eventservice';
export { NodeService } from './service/nodeservice';
export { AlertService } from '../helper';

import * as demo from './';

export const demos = [
    demo.DashboardDemoComponent,
    demo.SampleDemoComponent,
    demo.FormsDemoComponent,
    demo.DataDemoComponent,
    demo.PanelsDemoComponent,
    demo.OverlaysDemoComponent,
    demo.MenusDemoComponent,
    demo.MessagesDemoComponent,
    demo.MiscDemoComponent,
    demo.EmptyDemoComponent,
    demo.ChartsDemoComponent,
    demo.FileDemoComponent,
    demo.UtilsDemoComponent,
    demo.DocumentationComponent
];

export const demoServices = [
    demo.CarService,
    demo.CountryService,
    demo.NodeService,
    demo.EventService,
    demo.AlertService
];

import { AuthGuard } from '../helper';

let session = JSON.parse(localStorage.getItem('user.data'));
export const routeDemo = [
    // {
    //     // canActivate: [AuthGuard],

    //     //  redirectTo: localStorage.getItem('user.data') != null ? 'dashboard-'+session.kdModulAplikasi.toLowerCase() : 'login',
    //     path: '',
    //     component: demo.DashboardDemoComponent,
    //     pathMatch: 'full'
    // },
    { canActivate: [AuthGuard], path: 'sample', component: demo.SampleDemoComponent },
    { canActivate: [AuthGuard], path: 'forms', component: demo.FormsDemoComponent },
    { canActivate: [AuthGuard], path: 'data', component: demo.DataDemoComponent },
    { canActivate: [AuthGuard], path: 'panels', component: demo.PanelsDemoComponent },
    { canActivate: [AuthGuard], path: 'overlays', component: demo.OverlaysDemoComponent },
    { canActivate: [AuthGuard], path: 'menus', component: demo.MenusDemoComponent },
    { canActivate: [AuthGuard], path: 'messages', component: demo.MessagesDemoComponent },
    { canActivate: [AuthGuard], path: 'misc', component: demo.MiscDemoComponent },
    {
        // canActivate: [AuthGuard], 
        path: 'empty', component: demo.EmptyDemoComponent
    },
    { canActivate: [AuthGuard], path: 'charts', component: demo.ChartsDemoComponent },
    { canActivate: [AuthGuard], path: 'file', component: demo.FileDemoComponent },
    { canActivate: [AuthGuard], path: 'utils', component: demo.UtilsDemoComponent },
    { canActivate: [AuthGuard], path: 'documentation', component: demo.DocumentationComponent }
];
