<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Grupo extends CI_Controller {
	public function __construct(){
        parent::__construct();
        // Se le asigna a la informacion a la variable $sessionVP.
        
        $this->load->model(array('model_grupo'));
        $this->sessionVP = @$this->session->userdata('sess_vp_'.substr(base_url(),-8,7));
    }

    public function listar_grupo(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		// var_dump($this->sessionVP); exit(); 
		$arrGrupos = array(
			'key_admin',
			'key_prof'
		);
		if( $this->sessionVP['key_grupo'] == 'key_root' ){ 
			$arrGrupos[] = $this->sessionVP['key_grupo'];
		}
		$lista = $this->model_grupo->m_cargar_grupo($arrGrupos);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => $row['idgrupo'],
					'descripcion' => $row['nombre_gr']
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