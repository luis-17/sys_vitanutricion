<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class GrupoAlimento extends CI_Controller {
	public function __construct(){
        parent::__construct();
        // Se le asigna a la informacion a la variable $sessionVP.
        // $this->sessionVP = @$this->session->userdata('sess_vp_'.substr(base_url(),-8,7));
        $this->load->model(array('model_grupoAlimento'));
    }

    public function listar_grupo_alimento_1(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_grupoAlimento->m_cargar_grupo_alimento_1();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => $row['idgrupo1'],
					'descripcion' => $row['descripcion_gr1']
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
    public function listar_grupo_alimento_2(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_grupoAlimento->m_cargar_grupo_alimento_2($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => $row['idgrupo2'],
					'descripcion' => $row['descripcion_gr2'],
					'idgrupo1' => $row['idgrupo1']
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
}