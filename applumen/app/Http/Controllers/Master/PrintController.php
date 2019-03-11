<?php
/**
 * Created by IntelliJ IDEA.
 * User: Egie Ramdan
 * Date: 05/03/2019
 * Time: 20.39
 */
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Model\Master\ProdukControler;
use App\Model\Transaksi\Struk_T;
use App\Model\Transaksi\Transaksi_T;
use Codedge\Fpdf\Fpdf\Fpdf;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use App\Traits\Core;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jimmyjs\PdfReportGenerators\PdfReportGenerator;
use Jimmyjs\ReportGenerator\ReportMedia\PdfReport;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class  PrintController extends Controller
{

	public function print(Request $request){

		try {
//			$profile = CapabilityProfile::load("simple");
			$ip = '127.0.0.1'; // IP Komputer kita atau printer lain yang masih satu jaringan
			$printer = 'EPSON LX-310'; // Nama Printer yang di sharing
			$connector = new WindowsPrintConnector("smb://" . $ip . "/" . $printer);
			$printer = new Printer($connector);
			$text = '<!DOCTYPE html>
<html>

<head>
  <title>King Banana</title>
  <style>
    .table_style {
      font-family: arial, sans-serif;
      border-collapse: collapse;
      width: 100%;
    }

    .td_style,
    .th_style {
      border-top: 1px solid #dddddd;
      text-align: left;
      padding: 8px;
    }

    .tr_style:nth-child(even) {
      background-color: #dddddd;
    }
  </style>
</head>

<body>

  <h2 style="text-align:center">King Banana</h2>
  <p style="text-align:center">Jln. Jakarta No 6 Jakarta Selatan.</p>
  <p style="font-weight:bold "> Faktur Pembayaran</p>
  <table style="border:0px;  width: 100%;margin-bottom:15px;margin-top:-10px">
    <tr>
      <th style="text-align:left">No Struk # : ST90172817</th>
      <th style="text-align:right"> 23-03-2019</th>
    </tr>
    <tr>
      <th style="text-align:left">Petugas : Admin </th>
    </tr>
  </table>

  <table class="table_style">
    <tr class="tr_style">
      <th class="th_style">Produk</th>
      <th class="th_style">Harga</th>
      <th class="th_style">Jumlah</th>
      <th class="th_style">Diskon</th>
      <th class="th_style">Total</th>
    </tr>
    <tr class="tr_style">
      <td class="td_style">Jeruk</td>
      <td class="td_style"> 1000</td>
      <td class="td_style">10</td>
      <td class="td_style">0</td>
      <td class="td_style"> 10000</td>
    </tr>
    <tr class="tr_style">
      <td class="td_style">Mangga</td>
      <td class="td_style">5000</td>
      <td class="td_style">10</td>
      <td class="td_style">10000</td>
      <td class="td_style"> 40000</td>
    </tr>
    <tr class="tr_style">
      <td class="td_style">Jambu</td>
      <td class="td_style">4000</td>
      <td class="td_style">1</td>
      <td class="td_style">0</td>
      <td class="td_style"> 4000</td>
    </tr>
  </table>
  <table style="border:0px;  width: 30%;margin-bottom:15px;margin-top:15px;margin-right:110px" align="right">
    <tr>
      <th style="text-align:left">Subtotal</th>
      <th style="text-align:right"> 54000</th>

    </tr>
    <tr>
      <th style="text-align:left">Pajak </th>
      <th style="text-align:right"> 0</th>
    </tr>
    <tr>
      <th style="text-align:left">Total </th>
      <th style="text-align:right"> 54000</th>
    </tr>

    <tr>
      <th style="text-align:left">Tunai </th>
      <th style="text-align:right"> 100000</th>
    </tr>
    <tr>
      <th style="text-align:left">Kembali </th>
      <th style="text-align:right"> 46000</th>
    </tr>
  </table>
</body>

</html>';
			$printer -> text("Hello World!");
			$printer -> cut();
			$printer -> close();
			$response  = ['success'=>'true'];
		} catch (Exception $e) {
			$response = ['success'=>'false'];
		}

		return response()
			->json($response);
	}
	public function displayReport(Request $request)
	{
		// Retrieve any filters
		$fromDate = $request->input('from_date');
		$toDate = $request->input('to_date');
		$sortBy = $request->input('sort_by');

		// Report title
		$title = 'Transaksi Penjualan';
		$fromDate = '2019-01-18';
		$toDate= '2019-05-18';
		// For displaying filters description on header
		$meta = [
			'Registered on' =>$fromDate . ' To ' .$toDate,
//			'Sort By' => $sortBy
		];

		// Do some querying..


		$sortBy ='notransaksi';

		$queryBuilder = Transaksi_T::select(['notransaksi', 'hargajual', 'tgltransaksi']) // Do some querying..
		->whereBetween('tgltransaksi', [$fromDate, $toDate])
		  	->orderBy($sortBy);
//		return response()->json($queryBuilder);
		// Set Column to be displayed
		$columns = [
			'No Transaksi' => 'notransaksi',
			'Tgl Transaksi' => 'tgltransaksi',
			'Total' => 'hargajual',
			'Status' =>'norec'

		];
//		return $columns;
		/*
			Generate Report with flexibility to manipulate column class even manipulate column value (using Carbon, etc).

			- of()         : Init the title, meta (filters description to show), query, column (to be shown)
			- editColumn() : To Change column class or manipulate its data for displaying to report
			- showTotal()  : Used to sum all value on specified column on the last table (except using groupBy method). 'point' is a type for displaying total with a thousand separator
			- groupBy()    : Show total of value on specific group. Used with showTotal() enabled.
			- limit()      : Limit record to be showed
			- make()       : Will producing DomPDF instance so you could do any other DomPDF method such as stream() or download()
		*/
		$pdf = new PdfReport();
		return $pdf->of($title, $meta, $queryBuilder, $columns)
			->editColumn('Tgl Transaksi', [
				'displayAs' => function ($result) {
					return $result->tgltransaksi;
				}
			])
			->editColumn('Total', [
				'class' => 'right bold',
				'displayAs' => function ($result) {
					return $result->hargajual;
				}
			])
			->editColumn('Status', [
				'class' => 'right bold'
			])
			->showTotal([
				'Total' => 'hargajual'
			])
			->limit(20)
			->make()
			->stream(); // or download() to download pdf}


	}
	public  function  pdf2(){
		$html = '<html><body>'
			. '<p>Put your html here, or generate it with your favourite '
			. 'templating system.</p>'
			. '</body></html>';
		return \PDF2::load($html, 'A4', 'portrait')->show();
	}

	public function pdf3()
	{
//		$pdf = new Dompdf();
//		$pdf->loadHtml('<h1>Test</h1>');
//		$pdf->stream('tes', array(
//			"Attachment" => false,
//		));

//		$pdf = new Fpdf('L','mm',array(120,50));
//		$pdf->AddPage();
//		$pdf->SetFont('Courier', 'B', 20);
//		$pdf->Cell(50, 25, 'Hello World!');
////		$pdf->Image(__DIR__.'/qrcode.png',"5","10","30","30","png");
//		$pdf->Output('I','');
//		exit;
//		return exit(0);
		$pdf = new Dompdf();
//		$pdf = App::make('dompdf.wrapper');
		$pdf->loadHtml('<h1 style="color: black">Test</h1>');
		$pdf->setPaper('letter', 'Lanscape');
		$pdf->render();
		$pdf->stream("dompdf_out.pdf", array("Attachment" => 0));
//		return response()->download($pdf);
		exit (0);
	}
}