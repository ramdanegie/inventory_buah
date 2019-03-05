import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule, NoopAnimationsModule } from '@angular/platform-browser/animations';
import { LocationStrategy, HashLocationStrategy } from '@angular/common';
import { AppRoutes } from './app.routes';


import { AppComponent } from './app.component';
import { DashboardDemoComponent } from './demo/view/dashboarddemo.component';
import { SampleDemoComponent } from './demo/view/sampledemo.component';
import { FormsDemoComponent } from './demo/view/formsdemo.component';
import { DataDemoComponent } from './demo/view/datademo.component';
import { PanelsDemoComponent } from './demo/view/panelsdemo.component';
import { OverlaysDemoComponent } from './demo/view/overlaysdemo.component';
import { MenusDemoComponent } from './demo/view/menusdemo.component';
import { MessagesDemoComponent } from './demo/view/messagesdemo.component';
import { MiscDemoComponent } from './demo/view/miscdemo.component';
import { EmptyDemoComponent } from './demo/view/emptydemo.component';
import { ChartsDemoComponent } from './demo/view/chartsdemo.component';
import { FileDemoComponent } from './demo/view/filedemo.component';
import { UtilsDemoComponent } from './demo/view/utilsdemo.component';
import { DocumentationComponent } from './demo/view/documentation.component';

import { CarService } from './demo/service/carservice';
import { CountryService } from './demo/service/countryservice';
import { EventService } from './demo/service/eventservice';
import { NodeService } from './demo/service/nodeservice';
// import { TableModule } from 'primeng/components/table/table';
import { MatFormFieldModule } from '@angular/material/form-field';
import { PrimeNgModule, AppComponents, materialModule } from '.';
import { HelperService, helperComponent, helperServices } from './helper';
import { DataHandler } from './helper/handler/DataHandler';
import { MatProgressBarModule, MatStepLabel } from '@angular/material';
import { RouterModule } from '@angular/router';
import { demos, demoServices } from './demo';
import { Http, HttpModule } from '@angular/http';
import { pagesAuth } from './page/auth';
import { ComponentMaster, ServiceMaster } from './page/modules';
import { ProdukComponent } from './page/modules/master/produk/produk.component';
// import { ChartModule } from 'angular-highcharts';
//import { ChartModule, HIGHCHARTS_MODULES } from 'angular-highcharts';
//import * as more from 'highcharts/highcharts-more.src';
//import * as exporting from 'highcharts/modules/exporting.src';

// import { LoadingPageModule, MaterialBarModule } from 'angular-loading-page';
@NgModule({
    imports: [
        MatFormFieldModule,
        BrowserModule,
        FormsModule,
        AppRoutes,
        HttpClientModule,
        BrowserAnimationsModule,
        NoopAnimationsModule,
        ...PrimeNgModule,
        ...materialModule,
        MatProgressBarModule,
        RouterModule,
        ReactiveFormsModule,
        HttpModule,
        //ChartModule,
        // LoadingPageModule, MaterialBarModule,
    ],
    declarations: [
        ...AppComponents,
        ...demos,
        ...helperComponent,
        ...ComponentMaster,
        // ...CoreComponentMaster,
        // ...globalComps,
        ...pagesAuth,
        ProdukComponent,

  
        // ...pagesMaster,
        // ...demos,
        // ...ComponentMaster,
        // ...CoreComponentMaster,
        // AppComponent,
        // AppMenuComponent,
        // AppSubMenuComponent,
        // AppSideBarComponent,
        // AppSideBarTabContentComponent,
        // AppTopBarComponent,
        // AppFooterComponent,
        // DashboardDemoComponent,
        // SampleDemoComponent,
        // FormsDemoComponent,
        // DataDemoComponent,
        // PanelsDemoComponent,
        // OverlaysDemoComponent,
        // MenusDemoComponent,
        // MessagesDemoComponent,
        // MessagesDemoComponent,
        // MiscDemoComponent,
        // ChartsDemoComponent,
        // EmptyDemoComponent,
        // FileDemoComponent,
        // UtilsDemoComponent,
        // DocumentationComponent
    ],
    providers: [
        {
            provide: LocationStrategy,
            useClass: HashLocationStrategy
        },
        //{
         //   provide: HIGHCHARTS_MODULES, useFactory: () => [ more, exporting ] 
        //},
        CarService,
        CountryService,
        EventService,
        NodeService,
        HelperService ,
        demoServices,
        ...helperServices,
        DataHandler,
        ServiceMaster   ],
    bootstrap: [AppComponent]
})
export class AppModule { }
