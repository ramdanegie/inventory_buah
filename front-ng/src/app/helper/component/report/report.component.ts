import { Component, OnInit } from '@angular/core';
import { DialogModule } from 'primeng/primeng';
import { HttpClient } from '../../../helper';
import $ from 'jquery';
import PDFObject from 'pdfobject';

@Component({
  selector: 'pdf-report',
  template: '<p-dialog header="Laporan" [(visible)]="display"' + 
  '  width="700" responsive="true" showEffect="fade" [modal]="true"> ' +
     '<div id="report-pdf" style="height: 100px;">Report Here</div>'+
     ' </p-dialog>'
})
export class ReportComponent implements OnInit {

  display:boolean;
  
	private options:any;
	private myPDF:any;
	private urlPDF:string;
  private http: HttpClient;

  	constructor() {

      this.options = {
                forcePDFJS: true,
                PDFJS_URL:'',
                pdfOpenParams: {
                  navpanes: 0,
                  toolbar: 0,
                  statusbar: 0,
                  view: "FitV"
              }
            };
    }

  	ngOnInit() {}

    setHttpClient(http: HttpClient){
      this.http = http;
    }


    private embedPDF(divId:string, embed:boolean, height:number){
      if (embed){
          $(divId).height(height);
      } else {
          var w = $(window).height(); 
          $(divId).height(w - height);
      }
      
      this.display = true;
      this.myPDF = PDFObject.embed(this.urlPDF, divId, this.options);
    }


    private downloadPDF(url:string, divId:string = '#report-pdf', embed : boolean = false, height:number = 240){

      this.urlPDF = url;
      this.options.PDFJS_URL = this.urlPDF;
      console.log(this.urlPDF);
      this.embedPDF(divId, embed, height);

      // this.http.getForced(url).subscribe(data =>  {        
      //   this.urlPDF = window.URL.createObjectURL(data);
      //   this.options.PDFJS_URL = this.urlPDF;
      //   console.log(this.urlPDF);
      //   this.embedPDF(divId, embed, height);
      // });
    }

  	closePopUpPDFReport(){
  		this.display = false;
  	}

  	openPopUpPDFReport(){
  		this.display = true;
  	}

  	generatePopUpPDFReport(urlPDF:string){
      this.downloadPDF(urlPDF);
  	}

    
  	showEmbedPDFReport(urlPDF:string, divId:string, height:number = 240){
      this.downloadPDF(urlPDF,divId,true,height);
  	}

  	getUrlPdf(){
  		return this.urlPDF;
  	}

  	setOptions(options:any){
  		this.options = options;
  	}

  	setPdfOpenParams(params:any){
  		this.options.pdfOpenParams = params;
  	}

  	getOptions(){
  		return this.options;
  	}

}
