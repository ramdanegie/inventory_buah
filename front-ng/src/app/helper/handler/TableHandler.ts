import { Injectable } from '@angular/core';
import { Message } from 'primeng/primeng';
@Injectable()
export class TableHandler {

    // initColumn(column : any[]) {
    initColumn() {
        var cols = [];
        cols = [
            { field: 'KdPegawai', header: 'Kode Pegawai' },
            { field: 'NamaLengkap', header: 'Nama Lengkap' },
            { field: 'ReportDisplay', header: 'Report Display' },
            { field: 'KodeExternal', header: 'Kode Eksternal' },
            { field: 'NamaExternal', header: 'Nama Eksternal' }
        ];

        return cols;
    }
}