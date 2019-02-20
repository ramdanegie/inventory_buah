export { LoginComponent } from './login/login.component';
export { SuperUserComponent } from './super-user/super-user.component';


import * as pAuth from './';

export const pagesAuth = [
	pAuth.LoginComponent,
	pAuth.SuperUserComponent
];

export const pageAuthServices = [
];

export const routeAuth = [
	{path:'login', component : pAuth.LoginComponent} 
];