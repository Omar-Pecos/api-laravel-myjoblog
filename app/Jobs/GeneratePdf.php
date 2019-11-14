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
    protected $identificador = '';
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user,$id,$year,$identificador)
    {
        $this->identificador = $identificador;
         $this->user = $user;
         if ($id == "all"){
                $this->ids = $id;
                $this->year = $year;
        }else{
             $this->ids = json_decode($id,true);
        }
        
           
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
                // todas las jornadas de 1 user
                if ($this->identificador != 0){

                    //$user = User::find($this->identificador);

                    /*$journeys = DB::table('journeys')
                        ->where('user_id','=',$this->identificador)
                        ->where('date', 'like', '%' . $this->year . '%')
                        ->get();*/

                    $journeys = Journey::where('user_id',$this->identificador)
                                ->where('date','like','%'.$this->year.'-%')
                                ->orderBy('created_at')
                                ->get();
                    
                            //$journeys = $user->journeys;
   
                                //->whereYear('date', $this->year)
                                
                }else{
                    $journeys = Journey::where('date','like','%'.$this->year.'-%')
                        ->orderBy('user_id')
                        ->orderBy('created_at')
                            ->get();
                }
         }else{
                    $journeys = Journey::find($this->ids);
         }
        
        
        
        
          if (count($journeys) > 0)  {

             $string_initial = '';
             $string_final = '';
                //  VOLVER A PONER DE - - -  
                    foreach($journeys as $key => $j) {
                        
                        if ($key === 0){
                            $initial_date =  explode("-", $j->date);
                            $string_initial = $initial_date[2].'-'.$initial_date[1].'-'.$initial_date[0];
                            //$string_initial = $initial_date[0].'-'.$initial_date[1].'-'.$initial_date[2];
                        }

                        if ($key === count($journeys)-1){
                             $final_date =  explode("-", $j->date);
                            $string_final = $final_date[2].'-'.$final_date[1].'-'.$final_date[0];
                            // $string_final = $final_date[0].'-'.$final_date[1].'-'.$final_date[2];
                        }

                         $date =  explode("-", $j->date);
                          $string_date = $date[2].'/'.$date[1].'/'.$date[0];
                          $j->date = $string_date;

                         $j->user_data = $j->user;
                    } 
           
                }


               
  
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
        // $time = date('d_m_y__H_i',$unixtime);
        //$file_name = $this->user->sub.'_'.$this->user->dni.'_pdf_'.$time.'.pdf';

        $file_name = '';

        if ($this->ids == 'all'){

                if ($this->identificador != 0){
                    $file_name = 'myjoblog_ID_'.$this->identificador.'_jornadas'.$this->year.'_'.$string_initial.'_al_'.$string_final.'.pdf';
                }
                else{
                     $file_name = 'myjoblog_Todos_jornadas'.$this->year.'_'.$string_initial.'_al_'.$string_final.'.pdf';
                }
               
        }else{
            $file_name = 'myjoblog_'.$this->user->dni.'_'.$string_initial.'_al_'.$string_final.'.pdf';
        }
        
        $type = '';

         if ($this->ids == 'all'){
            $type = 'generated';
        }else{
           $type = 'auto';
        }


        // INSERT A EXPORTS !!!!

            DB::table('exports')->insert([
                'user_id' => $this->user->sub,
                'namefile' => $file_name,
                'datetime' => date("Y-m-d H:i:s",$unixtime),
                'type' => $type
            ]);
        
    //dd(storage_path('app/public/images'));
        $pdf= $html2pdf->Output(storage_path('app/pdf/').$file_name, 'F'); 
    }
}
