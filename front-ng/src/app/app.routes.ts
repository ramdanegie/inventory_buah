// import {Routes, RouterModule} from '@angular/router';
// import {ModuleWithProviders} from '@angular/core';
// import {DashboardDemoComponent} from './demo/view/dashboarddemo.component';
// import {SampleDemoComponent} from './demo/view/sampledemo.component';
// import {FormsDemoComponent} from './demo/view/formsdemo.component';
// import {DataDemoComponent} from './demo/view/datademo.component';
// import {PanelsDemoComponent} from './demo/view/panelsdemo.component';
// import {OverlaysDemoComponent} from './demo/view/overlaysdemo.component';
// import {MenusDemoComponent} from './demo/view/menusdemo.component';
// import {MessagesDemoComponent} from './demo/view/messagesdemo.component';
// import {MiscDemoComponent} from './demo/view/miscdemo.component';
// import {EmptyDemoComponent} from './demo/view/emptydemo.component';
// import {ChartsDemoComponent} from './demo/view/chartsdemo.component';
// import {FileDemoComponent} from './demo/view/filedemo.component';
// import {UtilsDemoComponent} from './demo/view/utilsdemo.component';
// import {DocumentationComponent} from './demo/view/documentation.component';

// export const routes: Routes = [
//     {path: '', component: DashboardDemoComponent},
//     {path: 'sample', component: SampleDemoComponent},
//     {path: 'forms', component: FormsDemoComponent},
//     {path: 'data', component: DataDemoComponent},
//     {path: 'panels', component: PanelsDemoComponent},
//     {path: 'overlays', component: OverlaysDemoComponent},
//     {path: 'menus', component: MenusDemoComponent},
//     {path: 'messages', component: MessagesDemoComponent},
//     {path: 'misc', component: MiscDemoComponent},
//     {path: 'empty', component: EmptyDemoComponent},
//     {path: 'charts', component: ChartsDemoComponent},
//     {path: 'file', component: FileDemoComponent},
//     {path: 'utils', component: UtilsDemoComponent},
//     {path: 'documentation', component: DocumentationComponent}
// ];

// export const AppRoutes: ModuleWithProviders = RouterModule.forRoot(routes);


// NEWW
import { Router, Routes, RouterModule } from '@angular/router';
import { ModuleWithProviders } from '@angular/core';
import { AlertService, AuthGuard } from './helper';
// import * as pModules from './page/modules';

// import { routeAuth } from './page/auth';
import { routeDemo } from './demo';
import { routeAuth } from './page/auth';
import { routerModule } from './page/modules';
// import { routeMaster } from './page/master';
// import { RouterMaster } from './page/modules';
// import { CoreRouterMaster } from './page/core';

export const routes: Routes = [
    ...routeAuth,
    // ...routeDemo,
    // ...routeMaster,
    ...routerModule,
    // ...CoreRouterMaster
];

export const AppRoutes: ModuleWithProviders = RouterModule.forRoot(routes);
