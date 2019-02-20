export interface SuperUserState {
	show: boolean;
	method: any;
	url: string;
	data: any;
	callBack: (res: any) => any;
}

export interface SuperUserInfo {
	info: string;
	error: boolean;
}