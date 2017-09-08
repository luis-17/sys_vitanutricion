<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PlanPlantilla extends CI_Controller {
	public function __construct(){
        parent::__construct();
        // Se le asigna a la informacion a la variable $sessionVP.

        $this->sessionVP = @$this->session->userdata('sess_vp_'.substr(base_url(),-8,7));
        $this->load->helper(array('fechas_helper','otros_helper'));
        $this->load->model(array('model_plan_plantilla'));
    }
    public function listar_plan_plantilla_cbo(){
		// var_dump('aqui'); exit(); 
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		if( empty($allInputs['tipo']) ){ 
			$arrData['message'] = 'No seleccióno ningun tipo de plantilla.';
    		$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return; 
		}
		if( $allInputs['tipo'] == 'general' ){ 
			$allInputs['tipo'] = 1;
		}
		if( $allInputs['tipo'] == 'dia' ){ 
			$allInputs['tipo'] = 2;
		}
		
		$lista = $this->model_plan_plantilla->m_cargar_plan_plantilla_cbo($allInputs);
		$arrListado = array();		
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => $row['idplantilladieta'],
					'descripcion' => strtoupper($row['nombre_pd']) 
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
	public function registrar_plan_plantilla()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['flag'] = 0;
		$arrData['message'] = 'Ha ocurrido un error registrando el plan alimentario.'; 

		if($allInputs['tipo']=='simple' && $allInputs['forma']== 'dia'){
			if(!$unTurnoLleno){
				$arrData['flag'] = 0;
				$arrData['message'] = 'Debe ingresar al menos las indicaciones de un turno.';
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}
		}

		/*validaciones general*/
		$unTurnoLleno = FALSE;
		$unTurnoLlenoCompuesto = FALSE;
		$hayUnNoNumerico  = FALSE;
		if($allInputs['forma'] == 'general'){
			foreach ($allInputs['planGeneral']['turnos'] as $turno) {
				if($turno['hora']['id']!='--'  && $turno['minuto']['id'] != '--' && !empty($turno['indicaciones'])){
					$unTurnoLleno = TRUE;
				}
			}
		}

		if($allInputs['tipo']=='simple' && $allInputs['forma']== 'general'){
			if(!$unTurnoLleno){
				$arrData['flag'] = 0;
				$arrData['message'] = 'Debe ingresar al menos las indicaciones de un turno.';
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}
		}

		if($allInputs['tipo']=='compuesto' && $allInputs['forma']== 'general'){
			if(!$unTurnoLlenoCompuesto){
				$arrData['flag'] = 0;
				$arrData['message'] = 'Debe ingresar al menos las indicaciones de un turno.';
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}

			if($hayUnNoNumerico){
				$arrData['flag'] = 0;
				$arrData['message'] = 'Debe ingresar campos de cantidad validos.';
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
			}
		}

		/*registro de datos*/
		$errorEnCiclo = FALSE;
		$this->db->trans_start();
		// REGISTRO DE CABECERA DE PLANTILLA 
		$tipoPD = NULL;
		if($allInputs['forma'] == 'dia'){
			$tipoPD = 2;
		}
		if($allInputs['forma'] == 'general'){
			$tipoPD = 1;
		}
		$fPlantilla = array(
			'nombre' => $allInputs['fPlantilla']['nombre'],
			'descripcion' => empty($allInputs['fPlantilla']['descripcion']) ? NULL : $allInputs['fPlantilla']['descripcion'],
			'tipo' => $tipoPD 
		);
		if($this->model_plan_plantilla->m_registrar_plan_plantilla($fPlantilla)){
			$idplantilladieta = GetLastId('idplantilladieta','plantilla_dieta'); 
			if($allInputs['forma'] == 'dia'){ 
				foreach ($allInputs['planDias'] as $key => $dia) {
					foreach ($dia['turnos'] as $turno) {
						if(
							($allInputs['tipo'] == 'simple' && $turno['hora']['value']!='--'  && $turno['minuto']['value'] != '--' && !empty($turno['indicaciones']))
							|| ($allInputs['tipo'] == 'compuesto' && $turno['hora']['value']!='--'  && $turno['minuto']['value'] != '--' && count(@$turno['alimentos'])>0)
						){
							if($turno['tiempo']['value']=='pm'){
								$hora = (((int)$turno['hora']['value']) + 12) .':'.$turno['minuto']['value'].':00';
							}else{
								$hora = $turno['hora']['value'].':'.$turno['minuto']['value'].':00';
							}

							$datos = array(
								'idplantilladieta' => $idplantilladieta,
								'iddia' => $dia['id'],
								'idturno' => $turno['id'],
								'indicaciones' => empty($turno['indicaciones'])? null : $turno['indicaciones'],
								'hora' => $hora
							);

							if(!$this->model_plan_plantilla->m_registrar_plan_plantilla_turno($datos)){
								$errorEnCiclo = TRUE;
							}
						}
					}
				}
			}
			if($allInputs['forma'] == 'general'){
				foreach ($allInputs['planGeneral']['turnos'] as $turno) {
					if(
						($allInputs['tipo'] == 'simple' && $turno['hora']['value']!='--'  && $turno['minuto']['value'] != '--' && !empty($turno['indicaciones']))
						|| ($allInputs['tipo'] == 'compuesto' && $turno['hora']['value']!='--'  && $turno['minuto']['value'] != '--' && count(@$turno['alimentos'])>0)
					){
						if($turno['tiempo']['value']=='pm'){
							$hora = (((int)$turno['hora']['value']) + 12) .':'.$turno['minuto']['value'].':00';
						}else{
							$hora = $turno['hora']['value'].':'.$turno['minuto']['value'].':00';
						}

						$datos = array(
							'idplantilladieta' => $idplantilladieta,
							'idturno' => $turno['id'],
							'indicaciones' => empty($turno['indicaciones'])? null : $turno['indicaciones'],
							'hora' => $hora,
						);

						if(!$this->model_plan_plantilla->m_registrar_plan_plantilla_turno($datos)){
							$errorEnCiclo = TRUE;
						}
					}
				}
			}
		}
		
		// $tipo_dieta = '';
		// if($allInputs['tipo']=='simple' && $allInputs['forma']== 'general'){
		// 	$tipo_dieta = 'SG';
		// }else if($allInputs['tipo']=='simple' && $allInputs['forma']== 'dia'){
		// 	$tipo_dieta = 'SD';
		// }else if($allInputs['tipo']=='compuesto' && $allInputs['forma']== 'general'){
		// 	$tipo_dieta = 'CG';
		// }else if($allInputs['tipo']=='compuesto' && $allInputs['forma']== 'dia'){
		// 	$tipo_dieta = 'CD';
		// }

		// $datos = array(
		// 	'tipo_dieta' => $tipo_dieta,
		// 	'indicaciones_dieta' => empty($allInputs['indicaciones']) ? NULL : $allInputs['indicaciones'],
		// 	'idatencion' => $allInputs['consulta']['idatencion']
		// );

		if(!$errorEnCiclo){
			$arrData['flag'] = 1;
			// $arrData['tipo_dieta'] = $tipo_dieta;
			$arrData['message'] = 'Se ha generado la plantilla exitosamente.';
		}
		$this->db->trans_complete();

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	public function listar_plan_plantilla()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['flag'] = 0;
		$arrData['message'] = 'Ha ocurrido un error consultando el plan alimentario.';
		$arrayPlan = $this->genera_estructura_plantilla($allInputs);

		$arrData['datos'] = $arrayPlan;
		$arrData['flag'] =1;
		$arrData['message'] = 'La plantilla ha sido importada.';

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	private function genera_estructura_plantilla($allInputs)
	{
		// var_dump($allInputs); exit(); idplantilladietaturno
		$lista = $this->model_plan_plantilla->m_cargar_plan_plantilla($allInputs); 
		$arrayPlan = array();
		foreach ($lista as $key => $row) { 
			$arrayPlan[$row['iddia']]['id'] = $row['iddia'];
			$arrayPlan[$row['iddia']]['valoresGlobales'] = array();
			$arrayPlan[$row['iddia']]['nombre_dia'] = strtoupper_total($row['nombre_dia']);
			$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['id'] = $row['idturno'];
			$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['valoresTurno'] = array();
			$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['descripcion'] = strtoupper_total($row['descripcion_tu']);
			$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['indicaciones'] = $row['indicaciones'];

			if($arrayPlan[$row['iddia']] != 1 && ($allInputs['tipo_dieta'] == 'SG' || $allInputs['tipo_dieta'] == 'CG' )){
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['idplantilladietaturno'] = NULL;
			}else{
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['idplantilladietaturno'] = $row['idplantilladietaturno'];
			}

			if(! empty($row['hora'])){
				$hora_string = darFormatoHora($row['hora']);
				$array = explode(" ", $hora_string);
				$tiempo_str = $array[1];

				$array = explode(':', $array[0]);
				$hora_str = $array[0];
				$min_str = $array[1];

				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['tiempo'] = $tiempo_str;
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['hora'] = $hora_str;
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['min'] = $min_str;
			}else{
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['tiempo'] = 'am';
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['hora'] = '--';
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['min'] = '--';
			} 
		}
		$arrayPlan = array_values($arrayPlan);
		return $arrayPlan;
	}
}
?>