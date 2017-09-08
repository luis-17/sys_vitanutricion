<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class InformeGeneral extends CI_Controller {
	public function __construct(){
        parent::__construct();
        // Se le asigna a la informacion a la variable $sessionVP.
        // $this->sessionVP = @$this->session->userdata('sess_vp_'.substr(base_url(),-8,7));
        $this->load->helper(array('fechas_helper'));
        $this->load->model(array('model_informe_general'));
    }
    public function listar_informe_general(){

    	ini_set('xdebug.var_display_max_depth', 5);
  		ini_set('xdebug.var_display_max_children', 256);
		ini_set('xdebug.var_display_max_data', 1024);

		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['inicio'] = date('Y-m').'-01';
		$allInputs['fin'] = date('Y-m-d');
		//var_dump($allInputs); exit(); 
		$arrListado = array();

		// PACIENTES ATENDIDOS 
		$fPacAte = $this->model_informe_general->cargar_total_pacientes_atendidos($allInputs); 
		if( empty($fPacAte) ){
			$fPacAte['contador'] = 0;
		}
		$arrListado['pac_atendidos'] = array( 
			'cantidad'=> $fPacAte['contador']
		);

		// ATENCIONES REALIZADAS 
		$fAteRealizadas = $this->model_informe_general->cargar_total_atenciones_realizadas($allInputs); 
		if( empty($fAteRealizadas) ){
			$fAteRealizadas['contador'] = 0;
		}
		$arrListado['atenciones_realizadas'] = array( 
			'cantidad'=> $fAteRealizadas['contador']
		);
		$arrData['datos'] = $arrListado;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($arrListado)){ 
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
}