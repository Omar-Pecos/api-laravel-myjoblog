<?php

namespace App\Jobs;

use App\Journey;
use Spipu\Html2Pdf\Html2Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GeneratePdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $ids;
    protected $year;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user,$id,$year)
    {
       
         if ($id == "all"){
                $this->$id = $id;
                $this->year = $year;
        } 
         $this->user = $user;
         $this->ids = json_decode($id,true);

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // recoger los datos a pintar

        if ($this->ids == 'all'){
            $journeys = Journey::where('date','like','%'.$this->year.'-%')->orderBy('user_id')->get();
         }else{
            $journeys = Journey::find($this->ids);
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
                        'all' => $this->ids == 'all' ? true : false,
                        'year' => $this->year

                ])->render();
        
        $html2pdf = new Html2Pdf('P','A4','es','true','UTF-8');
        //$html2pdf->output('pdf_generated.pdf');


        $html2pdf->WriteHTML($content);
        
        $unixtime = time();
        $time = date('d_m_y__H_i',$unixtime);

        /* se podria hacer un foreach de las journeys y sacar el date de la primera -> transformarlo a algo mas amenos y saca el el date de la ultima y poner en el FILENAME tmb mejor asi queda claro de que jornadas es ese pdf*/

        $file_name = $this->user->sub.'_'.$this->user->dni.'_pdf_'.$time.'.pdf';


        // INSERT A EXPORTS !!!!

            DB::table('exports')->insert([
                'user_id' => $this->user->sub,
                'namefile' => $file_name,
                'datetime' => date("Y-m-d H:i:s",$unixtime),
                'type' => $this->user->role
            ]);
        
    //dd(storage_path('app/public/images'));
        $pdf= $html2pdf->Output(storage_path('app/pdf/').$file_name, 'F'); 
    }
}
