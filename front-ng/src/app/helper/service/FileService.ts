import { Injectable } from '@angular/core';
import * as FileSaver from 'file-saver';
import { Configuration } from '../../helper';
import * as XLSX from 'xlsx';
declare var jsPDF: any; // Important
// let jsPDF = require('jspdf');

const EXCEL_TYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=UTF-8';
const EXCEL_EXTENSION = '.xlsx';
const PDF_EXTENSION = '.pdf';

@Injectable()
export class FileService {

  constructor() { }

  // Print HTML
  public printHtml(print_section, title): void {
    let printContents, popupWin;
    printContents = document.getElementById(print_section).innerHTML;
    popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
    popupWin.document.open();
    popupWin.document.write('<html><head><title>' + title + '</title><style>@media print{table{font-size:1vw;}@page {size: landscape;}}table, th, td {border: 1px solid black;border-collapse: collapse;}</style></head><body onload="window.print();window.close()">' + printContents + '</body></html>');
    popupWin.document.close();
  }

  public exportAsExcelFile(json: any[], excelFileName: string): void {
    // console.log(json);
    const worksheet: XLSX.WorkSheet = XLSX.utils.json_to_sheet(json);
    const workbook: XLSX.WorkBook = { Sheets: { 'data': worksheet }, SheetNames: ['data'] };
    const excelBuffer: any = XLSX.write(workbook, { bookType: 'xlsx', type: 'buffer' });
    this.saveAsExcelFile(excelBuffer, excelFileName);
  }

  private saveAsExcelFile(buffer: any, fileName: string): void {
    const data: Blob = new Blob([buffer], {
      type: EXCEL_TYPE
    });
    FileSaver.saveAs(data, fileName + EXCEL_EXTENSION);
  }

  //PDF
  public exportAsPdfFile(title: string, json: any, fileName: string): void {

    var col = [];
    var rows = [];
    var doc = new jsPDF('l', 'mm', [297, 210]);

    // buat Header di dokumen
    for (let key in json[0]) {
      col.push(key);
    }
    // console.log(col);

    // buat isi data di dokumen
    for (var key in json) {
      var temp = json[key];
      var arr = Object.keys(temp)
        .map(function (key) {
          return temp[key]
        });
      rows.push(arr);
    }
    // console.log(rows);

    doc.setFontSize(12);
    doc.text(20, 20, title);
    doc.autoTable(col, rows);
    doc.save(fileName + PDF_EXTENSION);
  }

  public exportDokumen(KdProfile, KdDepartemen, Extension, NamaTable) {
    window.open(Configuration.get().apiBackend + 'list-generic/export/' + KdProfile + '/' + KdDepartemen + '/?param=' + Extension + '&table=' + NamaTable + '&select=*');
  }

}