<?php 

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponser{

	protected $order = 'asc';
	protected $sort_by = 'id';

	// Si implementamos ya las funciones de successResponse y ErrorResponse mejor la vd !! 

	private function successResponse($data,$name,$code){
		
		$datos = [
			'status' =>'success',
			$name =>$data,
			'order' =>$this->order,
			'sort_by' =>$this->sort_by
		];

		return response()->json($datos,$code);
	}

	protected function errorResponse($message,$code){
		return response()->json(['status'=>'error','message' => $message,'code' =>$code],$code);
	}

	protected function showAll(Collection $collection,$name,$code=200){

		/*if ($collections->isEmpty()){
			return $this->successResponse(['data' =>$collections],$code);
		}
		$transformer = $collections->first()->transformer;
	$collections = $this->filterData($collections,$transformer);
		$collections = $this->sortData($collections,$transformer);
		$collections = $this->paginate($collections);
		$collections = $this->transformData($collections,$transformer);
		$collections = $this->cacheResponse($collections);*/
		
		$collection = $this->sortData($collection); // le passaba el transformer para no dar pistas sobre la verdadera estructura de la BBDD

		$collection = $this->myAccessors($collection);				

		$collection = $this->paginate($collection);

		return $this->successResponse($collection,$name,$code);
	}

	protected function showOne(Model $collection,$name,$code=200){
		return $this->successResponse($collection,$name,$code);
	}

	protected function sortData(Collection $collection){

		$order = 'asc';

			if (request()->has('sort_by')){
				$sort_by = request()->sort_by;

					if (request()->has('order')){
						$order = request()->order;
					}

						if ($order == 'asc'){
							$collection = $collection->sortBy->{$sort_by};
						}else if ($order == 'desc'){
							$collection = $collection->sortByDesc->{$sort_by};
						}	
						
						$this->order = $order;
						$this->sort_by = $sort_by;
				}
			
			return $collection;
	   }
	protected function paginate(Collection $collection){

		$rules = [
			'per_page' => 'integer|min:2|max:50',
		];

		Validator::validate(request()->all(),$rules);
		$page = LengthAwarePaginator::resolveCurrentPage();

		$perPage = 10;

		if (request()->has('per_page')){
			$perPage=(int)request()->per_page; 
		}

		$results = $collection->slice(($page-1)* $perPage,$perPage)->values();

		$paginate = new LengthAwarePaginator($results,$collection->count(),$perPage,$page,[
			'path' => LengthAwarePaginator::resolveCurrentPath(),
		]);

		$paginate->appends(request()->all());
		return $paginate;
	}

	protected function myAccessors(Collection $collection){

			if (count($collection) > 0){


					$className = get_class($collection->first());
						$nombreClase = class_basename($className);

						// Accesor DESPS DE FILTRAR ->date
						if ($nombreClase == 'Journey'){
							
							foreach ($collection as $j) {
								
								 $date =  explode("-", $j->date);
						        $string_date = $date[2].'/'.$date[1].'/'.$date[0];
					        	$j->date = $string_date;
							}
						}

						// Accesor DESPS DE FILTRAR ->datetime en exports
						if ($nombreClase == 'Export'){
							foreach ($collection as $e) {
							
									$datetime = explode(" ",$e->datetime);
							        
							         $date =  explode("-", $datetime[0]);
							        $string_date = $date[2].'/'.$date[1].'/'.$date[0];
							       	$e->datetime =  $string_date." ".$datetime[1];
							}
						}
			}

						return $collection;
	}
}