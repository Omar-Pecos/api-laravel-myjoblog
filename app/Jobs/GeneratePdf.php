<?php

namespace App\Jobs;

use App\Journey;
use Spipu\Html2Pdf\Html2Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GeneratePdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $year;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id,$year)
    {
       
         if ($id == "all"){
                $this->$id = $id;
                $this->year = $year;
        } 

         $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // recoger los datos a pintar

        if ($this->id == 'all'){
            $journeys = Journey::where('date','like','%'.$this->year.'-%')->orderBy('user_id')->get();
         }else{
            $journeys = Journey::where('user_id',$this->id)->get();
         }
        
         foreach ($journeys as $j) {
                $j->user_data = $j->user;
                // $j->load('user');
             }
    

      // dd($journeys);

    //recoger el contenido del otro fichero
        $content = view('print_view',
                    [
                        'journeys' => $journeys,
                        'all' => $this->id == 'all' ? true : false,
                        'year' => $this->year

                ])->render();
        
        $html2pdf = new Html2Pdf('P','A4','es','true','UTF-8');
        //$html2pdf->output('pdf_generated.pdf');


        $html2pdf->WriteHTML($content);
        
        $time = time();
        
    //dd(storage_path('app/public/images'));
        $pdf= $html2pdf->Output(storage_path('app/pdf/').'pdf_time_'.$time.'.pdf', 'F'); // The filename is ignored when you use 'S' as the second parameter.
    }
}
