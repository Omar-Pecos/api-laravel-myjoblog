<?php

namespace App\Http\Controllers;

use App\Journey;
use Illuminate\Http\Request;
use Spipu\Html2Pdf\Html2Pdf;

class PdfController extends Controller
{
    public function print_pdf(){

    	// recoger los datos a pintar
    	$journeys = Journey::find([36, 37, 38]);
	

    //recoger el contenido del otro fichero
    	$content = view('print_view',
    			['journeys' => $journeys])->render();
    	
    	$html2pdf = new Html2Pdf('P','A4','es','true','UTF-8');
    	//$html2pdf->output('pdf_generated.pdf');


		$html2pdf->WriteHTML($content);
    	
		$time = date('H:m:i',time());
		
	//dd(storage_path('app/public/images'));
    	$pdf= $html2pdf->Output(storage_path('storage/app/pdf/').'pdf_time_'.$time.'.pdf', 'F'); // The filename is ignored when you use 'S' as the second parameter.

    	echo "Pdf generado de time ".$time."!!! ";
		    	/*//respuesta para visualizarlo !!!
				return response($pdf)
				                  ->header('Content-Type', 'application/pdf')
				                  ->header('Content-Length', strlen($pdf))
				                  ->header('Content-Disposition', 'inline; filename="example.pdf"');
				*/
		}

	public function Callqueue(){
		//
	}
}
