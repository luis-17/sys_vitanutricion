<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Alimentos extends CI_Controller {
	public function __construct()
    {
        parent::__construct();
        // Se le asigna a la informacion a la variable $sessionVP.
        // $this->sessionVP = @$this->session->userdata('sess_vp_'.substr(base_url(),-8,7));
        $this->load->helper(array('fechas','otros_helper'));
        $this->load->model(array('model_alimentos'));

    }

	public function listar_alimentos()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_alimentos->m_cargar_alimentos($paramPaginate);
		$totalRows = $this->model_alimentos->m_count_alimentos($paramPaginate);
		$arrListado = array();
		// var_dump($lista); exit();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'idalimento' => $row['idalimento'],
					'idgrupo1' => $row['idgrupo1'],
					'idgrupo2' => $row['idgrupo2'],
					'grupo1' => $row['descripcion_gr1'],
					'grupo2' => $row['descripcion_gr2'],
					'nombre' => $row['nombre'],
					'calorias' => $row['calorias'],
					'proteinas' => $row['proteinas'],
					'grasas' => $row['grasas'],
					'carbohidratos' => $row['carbohidratos'],
					'estado_ali' => $row['estado_ali'],
					'medida_casera' => strtoupper($row['medida_casera']),
					'gramo' => $row['gramo'],
					'ceniza' => $row['ceniza'],
					'calcio' => $row['calcio'],
					'fosforo' =>$row['fosforo'],
					'zinc' => $row['zinc'],
					'hierro' => $row['hierro'],
					'fibra' => $row['fibra'],
				)
			);
		}

    	$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows['contador'];
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function lista_alimentos_autocomplete(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true); // var_dump($allInputs); exit();
		$lista = $this->model_alimentos->m_cargar_alimentos_cbo($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'idalimento' => $row['idalimento'],
					'idgrupo1' => $row['idgrupo1'],
					'idgrupo2' => $row['idgrupo2'],
					'nombre' => strtoupper_total($row['nombre']) ,
					'calorias' => (float)$row['calorias'],
					'proteinas' => (float)$row['proteinas'],
					'grasas' => (float)$row['grasas'],
					'carbohidratos' => (float)$row['carbohidratos'],
					'estado_ali' => $row['estado_ali'],
					'medida_casera' => strtoupper_total($row['medida_casera']),
					'gramo' => (float)$row['gramo'],
					'ceniza' => (float)$row['ceniza'],
					'calcio' => (float)$row['calcio'],
					'fosforo' =>(float)(float) $row['fosforo'],
					'zinc' => (float)$row['zinc'],
					'hierro' => (float)$row['hierro'],
					'nombre_compuesto' => strtoupper_total($row['nombre']) . ' - '. strtoupper_total($row['medida_casera']),
					'fibra' => (float)$row['fibra'],
				)
			);
		}
    	$arrData['datos'] = $arrListado;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	// MANTENIMIENTO
	public function registrar_alimento()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit();
		if($this->model_alimentos->m_registrar($allInputs)){
			$arrData['message'] = 'Se registraron los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function editar_alimento()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit();
		if($this->model_alimentos->m_editar($allInputs)){
			$arrData['message'] = 'Se editaron los datos correctamente ' . date('H:n:s');
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function anular_alimento()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al anular los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit();
		if($this->model_alimentos->m_anular($allInputs)){
			$arrData['message'] = 'Se anularon los datos correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}
