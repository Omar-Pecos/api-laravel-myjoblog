
<!DOCTYPE html>
<html lang="es">
<head>
	<title>Generar Pdf en Laravel</title>
	<meta charset="UTF-8">
	<meta name="viewport" content= "width=device-width, initial-scale=1.0"> 
		
</head>
<body class="body">

	<style type="text/css">
					#divcontenedor{					
						background-image:url(http://webtime.com.devel/img/bgverde60.png);
						width: 100%;
						height: 100%;

						  /* Center and scale the image nicely */
						  background-position: center;
						  background-repeat: no-repeat;
						  background-size: cover;
						/*background: linear-gradient(45deg, rgba(2,0,36,1) 0%, rgba(255,255,255,1) 20%, rgba(255,255,255,1) 80%, rgba(2,0,36,1) 100%);*/
					}

					h1{
						
					}
					h2{
						
					}

					/*Estilo cabecera*/
					#cabecera{
						background-color: #000000; 
					}
					#brand{
						margin-top: 25px;
						margin-left: 30px;
						color: white;
					}
					#imagen{
						float: right;
						margin-right: 10px;
					}
					/* estilo de tablas */
					table{
						width: 100%;
						border-collapse: collapse;
						margin-right: 15px;
					}
					#filacabecera{
						background-color:  #5cb85c;
						color: white;
					}
					
					th,td{
						  text-align: center;
						  vertical-align: middle;

						 padding: 15px;
						}

					
				</style>		


		<div id="divcontenedor">
			  <!-- Mi cabecera -->
			  <div id="cabecera">
					<img width="75" id="imagen" src="http://webtime.com.devel/img/logocode.png">
					<h3 id="brand">&nbsp;&nbsp;Opv Web Developer</h3>
				</div>


			<!-- get info del user si all = false OSEA INFO de uno solo -->

			<?php  

				if ($all == false){
						$user = $journeys[0]->user_data;
					} 

				$num =  count($journeys);

				$red = 'red';
				$blue = 'blue';
				$green = 'green';

				$a = ['#ff0000','#00ff00','#0000ff'];

			?>

			@if($all == false)
				<h1>Registro Semanal Jornadas</h1>
	    			<h2>Jornadas desde {{$journeys[0]->date}} 
	    						hasta
	    				 {{$journeys[$num-1]->date}}</h2>
	    			<h3>{{$user->name}} {{$user->surname}} - {{$user->dni}}</h3>
	    	@else
	    		<h1>Registro Anual Jornadas - {{$year}}</h1>
	    			<h2>Jornadas desde {{$journeys[0]->date}} 
	    						hasta
	    				 {{$journeys[$num-1]->date}}</h2>

	    	@endif


			    	<table>
			    		<tr id="filacabecera">
			    			<th>Usuario #</th>
			    			<th>Fecha</th>
			    			<th>Duracion (s)</th>
			    			<th>Firma</th>
			    		</tr>

							<?php	$ran_color =  $a[mt_rand(0, count($a) - 1)];
									$user_id_actual = $journeys[0]->user_id;
							 ?>

				    		@foreach ($journeys as $j)
					    			<?php

					    				if ($j->user_id != $user_id_actual){
					    			  			$ran_color =  $a[mt_rand(0, count($a) - 1)];
					    			  			$user_id_actual = $j->user_id;
					    				}

					    			  ?>
							    <tr>
							    	@if($all == false)
								    	<td style="border: 1px solid <?php echo $ran_color; ?> ;">
								    		{{$j->user_id}} - {{$user->name}} {{$user->surname}} <br>
								    		DNI {{$user->dni}}
								    	</td>
								    @else
								    	<td style="border: 1px solid <?php echo $ran_color; ?> ;">
								    		{{$j->user_id}} - {{$j->user_data->name}} {{ $j->user_data->surname}} <br>
								    		DNI {{ $j->user_data->dni }}
								    	</td>
								    @endif
							    	<td style="border: 1px solid <?php echo $ran_color; ?> ;">
							    		<b>{{$j->date}}</b> <br>
							    		Inicio <b>{{$j->initial_time}}</b><br>
							    		Final <b>{{$j->final_time}}</b>			    	
							    	</td style="border: 1px solid <?php echo $ran_color; ?> ;">
							    	<td style="border: 1px solid <?php echo $ran_color; ?> ;">{{$j->time}} horas</td>
							    	<td style="border: 1px solid <?php echo $ran_color; ?> ;">
							    		<img width="340" src="http://webtime.com.devel/storage/images/{{$j->signature}}">
							    	</td>
				    			</tr>
							@endforeach
			    		
			    	</table>

    	</div>
</body>
</html>