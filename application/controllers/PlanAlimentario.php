<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PlanAlimentario extends CI_Controller {
	public function __construct(){
        parent::__construct();
        // Se le asigna a la informacion a la variable $sessionVP.
        $this->sessionVP = @$this->session->userdata('sess_vp_'.substr(base_url(),-8,7));
        $this->load->helper(array('fechas_helper','otros_helper'));
        $this->load->model(array('model_plan_alimentario','model_consulta', 'model_paciente'));
        $this->load->library('Fpdfext');
    }

	public function registrar_plan_alimentario(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['flag'] = 0;
		$arrData['message'] = 'Ha ocurrido un error registrando el plan alimentario.';

		/*validacion duplicado*/
		$consulta = $this->model_consulta->m_consultar_atencion($allInputs['consulta']['idatencion']);
		if(!empty($consulta['tipo_dieta'])){
			$arrData['flag'] = 0;
			$arrData['message'] = 'Ya existe un plan alimentario registrado. Intente editarlo.';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
		}

		/*validaciones dia*/
		$unTurnoLleno = FALSE;
		$unTurnoLlenoCompuesto = FALSE;
		$hayUnNoNumerico  = FALSE;
		if($allInputs['forma']== 'dia'){
			foreach ($allInputs['planDias'] as $key => $dia) {
				foreach ($dia['turnos'] as $turno) {
					if($turno['hora']['id']!='--' && $turno['minuto']['id'] != '--' && !empty($turno['indicaciones'])){
						$unTurnoLleno = TRUE;
					}

					if($turno['hora']['id']!='--' && $turno['minuto']['id'] != '--' && count($turno['alimentos'])>0){
						$unTurnoLlenoCompuesto = TRUE;
						foreach ($turno['alimentos'] as $ind => $ali) {
							if(!is_numeric($ali['cantidad'])){
								$hayUnNoNumerico = TRUE;
							}
						}
					}
				}
			}
		}


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

		if($allInputs['tipo']=='compuesto' && $allInputs['forma']== 'dia'){
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

		/*validaciones general*/
		$unTurnoLleno = FALSE;
		$unTurnoLlenoCompuesto = FALSE;
		$hayUnNoNumerico  = FALSE;
		if($allInputs['forma'] == 'general'){
			foreach ($allInputs['planGeneral']['turnos'] as $turno) {
				if($turno['hora']['id']!='--'  && $turno['minuto']['id'] != '--' && !empty($turno['indicaciones'])){
					$unTurnoLleno = TRUE;
				}

				if($turno['hora']['id']!='--'  && $turno['minuto']['id'] != '--' && count(@$turno['alimentos'])>0){
					$unTurnoLlenoCompuesto = TRUE;
					foreach ($turno['alimentos'] as $ind => $ali) {
						if(!is_numeric($ali['cantidad'])){
							$hayUnNoNumerico = TRUE;
						}

						if(!$hayUnNoNumerico){
							foreach ($ali['alternativos'] as $indAlm => $alter) {
								if(!empty($alter['idalimento']) && !is_numeric($alter['cantidad'])){
									$hayUnNoNumerico = TRUE;
								}
							}
						}
					}
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
							'idatencion' => $allInputs['consulta']['idatencion'],
							'iddia' => $dia['id'],
							'idturno' => $turno['id'],
							'indicaciones' => empty($turno['indicaciones'])? null : $turno['indicaciones'],
							'hora' => $hora,
						);

						if(!$this->model_plan_alimentario->m_registrar_dieta_turno($datos)){
							$errorEnCiclo = TRUE;
						}
						if(!$errorEnCiclo){
							if($allInputs['tipo'] == 'compuesto'){
								$idatenciondietaturno = GetLastId('idatenciondietaturno','atencion_dieta_turno');
								//inserto detalle de alimentos
								foreach ($turno['alimentos'] as $alimento) {
									$datos = array(
										'idatenciondietaturno' => $idatenciondietaturno,
										'idalimento' => $alimento['idalimento'],
										'valor' => $alimento['cantidad'],
									);
									if(!$this->model_plan_alimentario->m_registrar_dieta_turno_alimento($datos)){
										$errorEnCiclo = TRUE;
									}
								}
							}
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
						'idatencion' => $allInputs['consulta']['idatencion'],
						'idturno' => $turno['id'],
						'indicaciones' => empty($turno['indicaciones'])? null : $turno['indicaciones'],
						'hora' => $hora,
					);

					if(!$this->model_plan_alimentario->m_registrar_dieta_turno($datos)){
						$errorEnCiclo = TRUE;
					}
					if(!$errorEnCiclo){
						if($allInputs['tipo'] == 'compuesto'){
							$idatenciondietaturno = GetLastId('idatenciondietaturno','atencion_dieta_turno');
							//inserto detalle de alimentos
							foreach ($turno['alimentos'] as $alimento) {
								$datos = array(
									'idatenciondietaturno' => $idatenciondietaturno,
									'idalimento' => $alimento['idalimento'],
									'valor' => $alimento['cantidad'],
								);
								if($this->model_plan_alimentario->m_registrar_dieta_turno_alimento($datos)){
									$idalimentomaster = GetLastId('idatenciondietaalim','atencion_dieta_alim');
									foreach ($alimento['alternativos'] as $alt) {
										if(!empty($alt['idalimento'])){
											$data = array(
												'idatenciondietaalim' => $idalimentomaster,
												'idalimento' => $alt['idalimento'],
											);
											if(!$this->model_plan_alimentario->m_registrar_dieta_turno_alimento_alt($data)){
												$errorEnCiclo = TRUE;
											}
										}
									}
								}else{
									$errorEnCiclo = TRUE;
								}
							}
						}
					}
				}
			}
		}

		$tipo_dieta = '';
		if($allInputs['tipo']=='simple' && $allInputs['forma']== 'general'){
			$tipo_dieta = 'SG';
		}else if($allInputs['tipo']=='simple' && $allInputs['forma']== 'dia'){
			$tipo_dieta = 'SD';
		}else if($allInputs['tipo']=='compuesto' && $allInputs['forma']== 'general'){
			$tipo_dieta = 'CG';
		}else if($allInputs['tipo']=='compuesto' && $allInputs['forma']== 'dia'){
			$tipo_dieta = 'CD';
		}

		$datos = array(
			'tipo_dieta' => $tipo_dieta,
			'indicaciones_dieta' => empty($allInputs['indicaciones']) ? NULL : $allInputs['indicaciones'],
			'idatencion' => $allInputs['consulta']['idatencion']
		);

		if(!$errorEnCiclo && $this->model_consulta->m_actualizar_desde_plan($datos)){
			$arrData['flag'] = 1;
			$arrData['tipo_dieta'] = $tipo_dieta;
			$arrData['indicaciones_dieta'] = empty($allInputs['indicaciones']) ? NULL : $allInputs['indicaciones'];
			$arrData['message'] = 'Se ha registrado el plan alimentario exitosamente.';
		}
		$this->db->trans_complete();

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function cargar_plan_alimentario(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['flag'] = 0;
		$arrData['message'] = 'Ha ocurrido un error consultando el plan alimentario.';
		$arrayPlan = $this->genera_estructura_plan($allInputs);

		$arrData['datos'] = $arrayPlan;
		$arrData['flag'] =1;
		$arrData['message'] = 'Ha sido cargado el plan alimentario.';

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	private function genera_estructura_plan($allInputs){
		$lista = $this->model_plan_alimentario->m_cargar_plan_alimentario($allInputs); 
		$arrayPlan =array();
		foreach ($lista as $key => $row) {
			$arrayPlan[$row['iddia']]['id'] = $row['iddia'];
			$arrayPlan[$row['iddia']]['valoresGlobales'] = array();
			$arrayPlan[$row['iddia']]['nombre_dia'] = strtoupper_total($row['nombre_dia']);
			$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['id'] = $row['idturno'];
			$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['valoresTurno'] = array();
			$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['descripcion'] = strtoupper_total($row['descripcion_tu']);
			$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['indicaciones'] = $row['indicaciones'];

			if($arrayPlan[$row['iddia']] != 1 && ($allInputs['tipo_dieta'] == 'SG' || $allInputs['tipo_dieta'] == 'CG' )){
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['idatenciondietaturno'] = NULL;
			}else{
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['idatenciondietaturno'] = $row['idatenciondietaturno'];
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

			if(!empty($row['idatenciondietaalim'])){
				if($arrayPlan[$row['iddia']] != 1 && ($allInputs['tipo_dieta'] == 'SG' || $allInputs['tipo_dieta'] == 'CG' )){
					$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['idatenciondietaalim'] = NULL;
				}else{
					$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['idatenciondietaalim'] = $row['idatenciondietaalim'];
				}

				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['cantidad'] = (float)$row['valor'];
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['idalimento'] = $row['idalimento'];
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['nombre'] = strtoupper_total($row['nombre']);
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['medida_casera'] = $row['medida_casera'];
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['nombre_compuesto'] = strtoupper_total($row['nombre']) . ' - '. strtoupper_total($row['medida_casera']);
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['calorias'] = (float)$row['calorias'];
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['proteinas'] = (float)$row['proteinas'];
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['grasas'] = (float)$row['grasas'];
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['carbohidratos'] = (float)$row['carbohidratos'];
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['gramo'] = (float)$row['gramo'];
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['ceniza'] = (float)$row['ceniza'];
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['calcio'] = (float)$row['calcio'];
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['fosforo'] = (float)$row['fosforo'];
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['zinc'] = (float)$row['zinc'];
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['hierro'] = (float)$row['hierro'];
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['fibra'] = (float)$row['fibra'];
			}

			if(!empty($row['idatenciondietaalimalter'])){				
				$arrayPlan[$row['iddia']]['turnos'][$row['idturno']]['alimentos'][$row['idatenciondietaalim']]['alternativos'][$row['idatenciondietaalimalter']] = array(
					'idatenciondietaalimalter' => ($arrayPlan[$row['iddia']] != 1 && ($allInputs['tipo_dieta'] == 'SG' || $allInputs['tipo_dieta'] == 'SD' )) ? NULL : $row['idatenciondietaalimalter'],
					'idatenciondietaalim' => $row['idatenciondietaalim'],
					'cantidad' => (float)$row['valor'],
					'idalimento' => $row['idalimento_alter'],
					'nombre' => $row['nombre_alter'],
					'medida_casera' => $row['medida_casera_alter'],
					'nombre_compuesto' => strtoupper_total($row['nombre_alter']) . ' - '. strtoupper_total($row['medida_casera_alter']),
					'calorias' => (float)$row['calorias_alter'],
					'proteinas' => (float)$row['proteinas_alter'],
					'grasas' => (float)$row['grasas_alter'],
					'carbohidratos' => (float)$row['carbohidratos_alter'],
					'gramo' => (float)$row['gramo_alter'],
					'ceniza' => (float)$row['ceniza_alter'],
					'calcio' => (float)$row['calcio_alter'],
					'fosforo' => (float)$row['fosforo_alter'],
					'zinc' => (float)$row['zinc_alter'],
					'hierro' => (float)$row['hierro_alter'],
					'fibra' => (float)$row['fibra_alter'],
				);
			}
		}
		$arrayPlan = array_values($arrayPlan);
		return $arrayPlan;
	}

	public function actualizar_plan_alimentario(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['flag'] = 0;
		$arrData['message'] = 'Ha ocurrido un error actualizando el plan alimentario.';

		$unTurnoLleno = FALSE;
		$unTurnoLlenoCompuesto = FALSE;
		$hayUnNoNumerico = FALSE;
		if($allInputs['forma']== 'dia'){
			foreach ($allInputs['planDias'] as $key => $dia) {
				foreach ($dia['turnos'] as $turno) {
					if($turno['hora']['id']!='--' && $turno['minuto']['id'] != '--' && !empty($turno['indicaciones'])){
						$unTurnoLleno = TRUE;
					}

					if($turno['hora']['id']!='--' && $turno['minuto']['id'] != '--' && !empty($turno['alimentos']) && count($turno['alimentos'])>0){
						$unTurnoLlenoCompuesto = TRUE;
						foreach ($turno['alimentos'] as $ind => $ali) {
							if(!is_numeric($ali['cantidad'])){
								$hayUnNoNumerico = TRUE;
							}
						}
					}
				}
			}
		}

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

		if($allInputs['tipo']=='compuesto' && $allInputs['forma']== 'dia'){
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

		/*validaciones general*/
		$unTurnoLleno = FALSE;
		$unTurnoLlenoCompuesto = FALSE;
		$hayUnNoNumerico = FALSE;
		if($allInputs['forma'] == 'general'){
			foreach ($allInputs['planGeneral']['turnos'] as $turno) {
				if($turno['hora']['id']!='--'  && $turno['minuto']['id'] != '--' && !empty($turno['indicaciones'])){
					$unTurnoLleno = TRUE;
				}

				if($turno['hora']['id']!='--'  && $turno['minuto']['id'] != '--' && !empty($turno['alimentos']) && count($turno['alimentos'])>0){
					$unTurnoLlenoCompuesto = TRUE;
					foreach ($turno['alimentos'] as $ind => $ali) {
						if(!is_numeric($ali['cantidad'])){
							$hayUnNoNumerico = TRUE;
						}

						if(!$hayUnNoNumerico){
							foreach ($ali['alternativos'] as $indAlmi=> $alter) {
								if(!empty($alter['idalimento']) && !is_numeric($alter['cantidad'])){
									$hayUnNoNumerico = TRUE;
								}
							}
						}
					}
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

		$consulta = $this->model_consulta->m_consultar_atencion($allInputs['consulta']['idatencion']);

		/*actualizacion de datos*/
		$errorEnCiclo = FALSE;
		$this->db->trans_start();


		$this->model_plan_alimentario->m_anular_todo_dieta_turno($allInputs['consulta']);
		if($allInputs['forma'] == 'dia'){
			foreach ($allInputs['planDias'] as $key => $dia) {
				foreach ($dia['turnos'] as $turno) {
					if(
						($allInputs['tipo'] == 'simple' && $turno['hora']['value']!='--'  && $turno['minuto']['value'] != '--' && !empty($turno['indicaciones']))
						|| ($allInputs['tipo'] == 'compuesto' && $turno['hora']['value']!='--'  && $turno['minuto']['value'] != '--' && !empty($turno['alimentos']) && count($turno['alimentos'])>0)
					){

						if($consulta['tipo_dieta'] == 'CD' || $consulta['tipo_dieta'] == 'CG'){
							if(!empty($turno['idatenciondietaturno'])){
								$this->model_plan_alimentario->m_anular_todo_dieta_alimento($turno);
							}
						}

						if($turno['tiempo']['value']=='pm'){
							$hora = (((int)$turno['hora']['value']) + 12) .':'.$turno['minuto']['value'].':00';
						}else{
							$hora = $turno['hora']['value'].':'.$turno['minuto']['value'].':00';
						}

						if(empty($turno['idatenciondietaturno'])){
							$datos = array(
								'idatencion' => $allInputs['consulta']['idatencion'],
								'iddia' => $dia['id'],
								'idturno' => $turno['id'],
								'indicaciones' => empty($turno['indicaciones'])? null : $turno['indicaciones'],
								'hora' => $hora,
							);

							if(!$this->model_plan_alimentario->m_registrar_dieta_turno($datos)){
								$errorEnCiclo = TRUE;
							}

							$idatenciondietaturno = GetLastId('idatenciondietaturno','atencion_dieta_turno');
						}else{
							$datos = array(
								'idatenciondietaturno' => $turno['idatenciondietaturno'],
								'idatencion' => $allInputs['consulta']['idatencion'],
								'iddia' => $dia['id'],
								'idturno' => $turno['id'],
								'indicaciones' => empty($turno['indicaciones'])? null : $turno['indicaciones'],
								'hora' => $hora,
							);

							if(!$this->model_plan_alimentario->m_actualizar_dieta_turno($datos)){
								$errorEnCiclo = TRUE;
							}

							$idatenciondietaturno = $turno['idatenciondietaturno'];
						}

						if(!$errorEnCiclo){
							if($allInputs['tipo'] == 'compuesto'){
								//inserto detalle de alimentos
								$datosTurno = array(
									'idatenciondietaturno' => $idatenciondietaturno
								);

								if(!empty($turno['idatenciondietaturno'])){
									$this->model_plan_alimentario->m_anular_todo_dieta_alimento($datosTurno);
								}

								foreach ($turno['alimentos'] as $alimento) {
									if(!empty($alimento['idatenciondietaalim']))
										$this->model_plan_alimentario->m_anular_todo_dieta_alimento_alter($alimento);

									if(empty($alimento['idatenciondietaalim'])){
										$datos = array(
											'idatenciondietaturno' => $idatenciondietaturno,
											'idalimento' => $alimento['idalimento'],
											'valor' => $alimento['cantidad'],
										);
										if(!$this->model_plan_alimentario->m_registrar_dieta_turno_alimento($datos)){
											$errorEnCiclo = TRUE;
										}
									}else{
										$datos = array(
											'idatenciondietaalim' => $alimento['idatenciondietaalim'],
											'idatenciondietaturno' => $idatenciondietaturno,
											'idalimento' => $alimento['idalimento'],
											'valor' => $alimento['cantidad'],
										);
										if(!$this->model_plan_alimentario->m_actualizar_dieta_alimento($datos)){
											$errorEnCiclo = TRUE;
										}
									}
								}
							}
						}
					}
				}
			}
		}

		if($allInputs['forma'] == 'general'){
			foreach ($allInputs['planGeneral']['turnos'] as $turno) {
				if(
					($allInputs['tipo'] == 'simple' && $turno['hora']['value']!='--'  && $turno['minuto']['value'] != '--' && !empty($turno['indicaciones']))
					|| ($allInputs['tipo'] == 'compuesto' && $turno['hora']['value']!='--'  && $turno['minuto']['value'] != '--' && !empty($turno['alimentos']) && count($turno['alimentos'])>0)
				){
					if($consulta['tipo_dieta'] == 'CD' || $consulta['tipo_dieta'] == 'CG'){
						if(!empty($turno['idatenciondietaturno'])){
							$this->model_plan_alimentario->m_anular_todo_dieta_alimento($turno);
						}
					}

					if($turno['tiempo']['value']=='pm'){
						$hora = (((int)$turno['hora']['value']) + 12) .':'.$turno['minuto']['value'].':00';
					}else{
						$hora = $turno['hora']['value'].':'.$turno['minuto']['value'].':00';
					}

					if(empty($turno['idatenciondietaturno'])){

						$datos = array(
							'idatencion' => $allInputs['consulta']['idatencion'],
							'idturno' => $turno['id'],
							'indicaciones' => empty($turno['indicaciones'])? null : $turno['indicaciones'],
							'hora' => $hora,
						);

						if(!$this->model_plan_alimentario->m_registrar_dieta_turno($datos)){
							$errorEnCiclo = TRUE;
						}

						$idatenciondietaturno = GetLastId('idatenciondietaturno','atencion_dieta_turno');
					}else{
						$datos = array(
							'idatenciondietaturno' => $turno['idatenciondietaturno'],
							'idatencion' => $allInputs['consulta']['idatencion'],
							'idturno' => $turno['id'],
							'indicaciones' => empty($turno['indicaciones'])? null : $turno['indicaciones'],
							'hora' => $hora,
						);

						if(!$this->model_plan_alimentario->m_actualizar_dieta_turno($datos)){
							$errorEnCiclo = TRUE;
						}

						$idatenciondietaturno = $turno['idatenciondietaturno'];
					}

					if(!$errorEnCiclo){
						if($allInputs['tipo'] == 'compuesto'){
							//inserto detalle de alimentos
							$datosTurno = array(
								'idatenciondietaturno' => $idatenciondietaturno
							);

							if(!empty($turno['idatenciondietaturno'])){
								$this->model_plan_alimentario->m_anular_todo_dieta_alimento($datosTurno);
							}

							foreach ($turno['alimentos'] as $alimento) {
								if(!empty($alimento['idatenciondietaalim']))
									$this->model_plan_alimentario->m_anular_todo_dieta_alimento_alter($alimento);

								if(empty($alimento['idatenciondietaalim'])){
									$datos = array(
										'idatenciondietaturno' => $idatenciondietaturno,
										'idalimento' => $alimento['idalimento'],
										'valor' => $alimento['cantidad'],
									);
									if($this->model_plan_alimentario->m_registrar_dieta_turno_alimento($datos)){
										$idalimentomaster = GetLastId('idatenciondietaalim','atencion_dieta_alim');

									}else{
										$errorEnCiclo = TRUE;
									}
								}else{
									$datos = array(
										'idatenciondietaalim' => $alimento['idatenciondietaalim'],
										'idatenciondietaturno' => $idatenciondietaturno,
										'idalimento' => $alimento['idalimento'],
										'valor' => $alimento['cantidad'],
									);
									if($this->model_plan_alimentario->m_actualizar_dieta_alimento($datos)){
										$idalimentomaster = $alimento['idatenciondietaalim'];

									}else{
										$errorEnCiclo = TRUE;
									}
								}

								if(!$errorEnCiclo){
									foreach ($alimento['alternativos'] as $alt) {
										if(!empty($alt['idalimento'])){
											if(empty($alt['idatenciondietaalimalter'])){
												$data = array(
													'idatenciondietaalim' => $idalimentomaster,
													'idalimento' => $alt['idalimento'],
												);
												if(!$this->model_plan_alimentario->m_registrar_dieta_turno_alimento_alt($data)){
													$errorEnCiclo = TRUE;
												}
											}else{
												$data = array(
													'idatenciondietaalimalter' => $alt['idatenciondietaalimalter'],
													'idatenciondietaalim' => $idalimentomaster,
													'idalimento' => $alt['idalimento'],
												);
												if(!$this->model_plan_alimentario->m_actualizar_dieta_turno_alimento_alter($data)){
													$errorEnCiclo = TRUE;
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		$tipo_dieta = '';
		if($allInputs['tipo']=='simple' && $allInputs['forma']== 'general'){
			$tipo_dieta = 'SG';
		}else if($allInputs['tipo']=='simple' && $allInputs['forma']== 'dia'){
			$tipo_dieta = 'SD';
		}else if($allInputs['tipo']=='compuesto' && $allInputs['forma']== 'general'){
			$tipo_dieta = 'CG';
		}else if($allInputs['tipo']=='compuesto' && $allInputs['forma']== 'dia'){
			$tipo_dieta = 'CD';
		}

		$datos = array(
			'tipo_dieta' => $tipo_dieta,
			'indicaciones_dieta' => empty($allInputs['indicaciones']) ? NULL : $allInputs['indicaciones'],
			'idatencion' => $allInputs['consulta']['idatencion']
		);

		if(!$errorEnCiclo && $this->model_consulta->m_actualizar_desde_plan($datos)){
			$arrData['flag'] = 1;
			$arrData['tipo_dieta'] = $tipo_dieta;
			$arrData['indicaciones_dieta'] = $allInputs['indicaciones'];
			$arrData['message'] = 'Se ha actualizado el plan alimentario exitosamente.';
		}
		$this->db->trans_complete();

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	private function headerPlan($paciente, $consulta, $configuracion){
		$this->pdf->Image('assets/images/dinamic/' . $configuracion['logo_imagen'],8,8,0,25);
		$this->pdf->SetFont('Arial','',14);
		$this->pdf->SetTextColor(83,83,83);
		$this->pdf->Cell(0,5,utf8_decode('PLAN DE ALIMENTACIÓN') ,0,1,'C');
		$this->pdf->Ln(4);
		$this->pdf->SetFont('Arial','',12);
	    $this->pdf->Cell(0,5,'Nombre: ' . ucwords(strtolower_total(utf8_decode(strtolower_total($paciente['paciente'])))),0,1,'R');
	    // $this->pdf->Ln(1);

    	$fecha = date('d/m/Y',strtotime($consulta['fecha_atencion']));
	    $this->pdf->Cell(0,5,'Fecha: '.$fecha,0,1,'R');

	    if($consulta['tipo_dieta'] == 'SD' || $consulta['tipo_dieta'] == 'CD'){
	    	$this->pdf->SetFont('Arial','B',14);
	    	$this->pdf->Cell(0,5,'DIETA SEMANAL',0,1,'C');
	    }
	    $this->pdf->Ln(10);
	}

	private function footerPlan($consulta, $margen, $configuracion){
		// $this->pdf->SetLeftMargin(0);
		// $this->pdf->SetRightMargin(0);
		// $this->pdf->SetY(-23);

	 	//    $this->pdf->SetTextColor(83,83,83);
		// $this->pdf->SetFillColor(204,211,211);
		// $this->pdf->SetFont('Arial','I',11);
		// $y = $this->pdf->GetY();
		// $this->pdf->Rect(0, $y, $this->pdf->GetPageWidth(), 25, 'F');
		// $texto = '* Recomendaciones: ' . ucfirst(strtolower($consulta['indicaciones_dieta']));

		// $this->pdf->SetLeftMargin(10);
		// $this->pdf->SetRightMargin(70);
		// $this->pdf->SetXY(10,$y+3);
		// //$this->pdf->MultiCell(0, 25, utf8_decode($texto), 0, 'L', false);
		// $this->pdf->Write(5, utf8_decode($texto));

		// $this->pdf->SetRightMargin(10);
		// $this->pdf->SetFont('Arial','',17);
		// $this->pdf->SetTextColor(0,0,0);
		// $this->pdf->SetXY(155,$y+1);
		// $this->pdf->Cell(60, 12, utf8_decode('PRÓXIMA CITA:'), 0, 'C', false);

		// if(empty($consulta['prox_cita'])){
		// 	$this->pdf->SetXY(167,$y+8);
		// 	$this->pdf->Cell(60, 12, 'no tiene', 0, 'C', false);
		// }else{
		// 	$this->pdf->SetXY(163,$y+8);
		// 	$this->pdf->Cell(60, 12, utf8_decode(date('d/m/Y',strtotime($consulta['prox_cita']))), 0, 'C', false);
		// }
		$this->pdf->SetDrawColor(156,156,156);
		$this->pdf->SetLineWidth(0);
		$posYRectangulos = 20; // ($this->pdf->GetPageHeight()/2) + 15;
		$anchoColumnas = ($this->pdf->GetPageWidth()-16)/3;
		$anchoObsevaciones = ($anchoColumnas * 2) - 5;
		$anchoProxCita =  $anchoColumnas;
		$altoColumnas = ($this->pdf->GetPageHeight()/2) - 60;

		$texto = ucfirst(strtolower($consulta['indicaciones_dieta'])); 

		//observaciones
		$this->pdf->Rect($margen, $posYRectangulos, $anchoObsevaciones, $altoColumnas, 'D');
		$this->pdf->SetFont('Arial','B',15);
		$this->pdf->SetXY($margen+5, $posYRectangulos+5);
		$this->pdf->Cell($anchoObsevaciones-10,6,utf8_decode('RECOMENDACIONES: '  ) ,0,1,'L');
		$this->pdf->SetFont('Arial','I',10);
		$this->pdf->SetXY($margen+5, $posYRectangulos+5+7);
		$this->pdf->MultiCell($anchoObsevaciones-10,5,utf8_decode('* ' .$texto) ,0,'L');

		//prox cita
		$posXProxCita = $margen + $anchoObsevaciones + 5;
		$this->pdf->Rect($posXProxCita, $posYRectangulos, $anchoProxCita, $altoColumnas, 'D');
		$this->pdf->SetFont('Arial','B',15);
		$this->pdf->SetXY($posXProxCita+5, $posYRectangulos+5);
		$this->pdf->Cell($anchoProxCita-10, 12, utf8_decode('PRÓXIMA CITA:'), 0, 1, 'C', false);

		$this->pdf->Image('assets/images/icons/calendario.png',$posXProxCita+10,null,$anchoProxCita-20);

		$posY = $this->pdf->GetY();
		if(!empty($consulta['prox_cita'])){
			$this->pdf->SetXY($posXProxCita, $posY-26);
			$this->pdf->SetFont('Arial','B',55);
			$dia_fecha = date('d', strtotime($consulta['prox_cita']));
			$this->pdf->Cell($anchoProxCita, 12, $dia_fecha, 0, 1, 'C', false);
			$this->pdf->SetX($posXProxCita);
			$this->pdf->SetFont('Arial','',16);
			$this->pdf->Cell($anchoProxCita, 12, formatoSoloMes($consulta['prox_cita']), 0, 1, 'C', false);
		}else{
			$this->pdf->SetXY($posXProxCita, $posY-22);
			$this->pdf->SetFont('Arial','B',18);
			$this->pdf->Cell($anchoProxCita, 12, 'NO TIENE', 0, 1, 'C', false);
		}

		//empresa
		$this->pdf->SetFont('Arial','',13);
		$this->pdf->Ln();
		$this->pdf->SetXY($posXProxCita,$posY+5);
		$this->pdf->Cell($anchoProxCita,6,$configuracion['pagina_web'],0,1,'C',FALSE);
		$this->pdf->SetX($posXProxCita);
		$this->pdf->Cell($anchoProxCita,6,'cel.: ' . $configuracion['celular'],0,1,'C',FALSE);
		$this->pdf->SetX($posXProxCita);
		$this->pdf->Cell($anchoProxCita,6,$configuracion['correo'],0,1,'C',FALSE);

		//profesional
		$this->pdf->SetLeftMargin(14);
		$this->pdf->SetY($posYRectangulos + $altoColumnas+10);
		$this->pdf->SetFont('Arial','',12);
		$profesional = 'Lic. ' . ucwords(strtolower_total(utf8_decode($consulta['nombre'] . ' ' . $consulta['apellidos'] )));
		$this->pdf->MultiCell(0,6,$profesional,0,'L',FALSE);
		$this->pdf->Cell(0,6,'CNP: ' . $consulta['num_colegiatura'],0,1,'L',FALSE);
		$this->pdf->Image('assets/images/dinamic/' . $configuracion['logo_imagen'],152,112,0,25); 
	}
	// correo 
	public function generar_pdf_plan(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = '';
    	$arrData['flag'] = 1;
    	$enviarCorreo = FALSE; 
    	$consulta = $this->model_consulta->m_consultar_atencion($allInputs['consulta']['idatencion']);
    	$paciente = $this->model_paciente->m_cargar_paciente_por_id($consulta);

    	if(empty($consulta['tipo_dieta'])){
    		$arrData['flag'] = 0;
			$arrData['message'] = 'No ha sido generado el Plan Alimentario.';
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}

    	if(!empty($allInputs['salida']) && $allInputs['salida']=='correo'){
    		if(empty($allInputs['emails'])){
    			$arrData['flag'] = 0;
    			$arrData['message'] = 'Debe ingresar correos validos.';
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));
				return;
    		}

    		$arrayMails = explode(',', $allInputs['emails']);
    		$hayError = false;
    		foreach ($arrayMails as $key => $mail) {
    			if(!comprobar_email($mail)){
    				$hayError = TRUE;
    			}
    		}

    		if($hayError){
    			$arrData['flag'] = 0;
    			$arrData['message'] = 'Debe ingresar correos validos.';
				$this->output
				    ->set_content_type('application/json')
				    ->set_output(json_encode($arrData));		
				return;
    		}

    		if(!$hayError){
    			$enviarCorreo = TRUE;
    		}
    	}

    	$configuracion = GetConfiguracion(); 
    	$arrayPlan = $this->genera_estructura_plan($allInputs['consulta']);

    	$this->pdf = new Fpdfext();
		$this->pdf->AddPage();
    	$this->pdf->SetMargins(0, 10, 10);
    	$this->pdf->SetAutoPageBreak(false);

    	//header
    	$this->headerPlan($paciente, $consulta, $configuracion);

	    //body	    
		if($consulta['tipo_dieta'] == 'SG' || $consulta['tipo_dieta'] == 'CG'){ // SIMPLE GENERAL - COMPUESTO GENERAL 
			$plan = $arrayPlan[1];
			$altoBloque =  ($this->pdf->GetPageHeight() - (18+23)) / 3; //total alto pagina - header - footer
			foreach ($plan['turnos'] as $key => $turno) {
				if($turno['id'] % 2 != 0){
					$this->pdf->SetTextColor(255,255,255);
					if($turno['id']==1){
						$r = 0;
						$g = 156;
						$b = 222;
						$this->pdf->SetFillColor($r,$g,$b);
					}
					if($turno['id']==3){
						$this->pdf->SetY($altoBloque+18+8);
						$r = 106;
						$g = 220;
						$b = 0;
						$this->pdf->SetFillColor($r,$g,$b);
					}
					if($turno['id']==5){
						$this->pdf->SetY(($altoBloque*2)+18);
						$r = 255;
						$g = 0;
						$b = 100;
						$this->pdf->SetFillColor($r,$g,$b);
					}
					$this->pdf->SetFont('Arial','B',14);
					$this->pdf->SetWidths(array(42, 30));
					// var_dump($turno); exit();
					$arrTurno = array(
						'data'=> array(
							utf8_decode('       ' . ucwords(strtoupper_total(utf8_decode($turno['descripcion'])))),
							utf8_decode('   '.$turno['hora'].':'.$turno['min'].' '.$turno['tiempo'].'.') 
						),
						'textColor'=> array(
							array('r'=> 255, 'g'=> 255, 'b'=> 255),
							array('r'=> 83, 'g'=> 83, 'b'=> 83 )
						),
						'fontSize'=> array(
							array('family'=> NULL, 'weight'=> NULL, 'size'=> 14),
							array('family'=> NULL, 'weight'=> NULL, 'size'=> 10 )
						),
						'bgColor'=> array(
							array('r'=> $r, 'g'=> $g, 'b'=> $b ), 
							array('r'=> 255, 'g'=> 255, 'b'=> 255 ) 
						)
					);
					// $data,$fill=FALSE,$border=0,$arrBolds=FALSE,$heigthCell=FALSE,$arrTextColor=FALSE,$arrBGColor=FALSE,$arrImage=FALSE,$bug=FALSE,$fontSize=FALSE
					$this->pdf->Row($arrTurno['data'],true,0,FALSE,6,$arrTurno['textColor'],$arrTurno['bgColor'],FALSE,FALSE,$arrTurno['fontSize']);
			    	$this->pdf->Ln(1);
			    	$this->pdf->SetTextColor(83,83,83);
			    	$this->pdf->SetFillColor(255,255,255);
			    	$this->pdf->SetFont('Arial','',11);
					$this->pdf->SetLeftMargin(10);
			    	if($consulta['tipo_dieta'] == 'SG'){ 
			    		if(!empty($turno['indicaciones'])){ 
			    			// $this->pdf->MultiCell(0,5,/*chr(127) .' '.*/ucfirst(strtolower_total(utf8_decode($turno['indicaciones']))),0,1,'L',true);
			    			$this->pdf->MultiCell(0,6,utf8_decode($turno['indicaciones']),0,1,'L',true);
			    		}
			    	}

			    	if($consulta['tipo_dieta'] == 'CG'){
			    		if(!empty($turno['alimentos'])){
				    		foreach ($turno['alimentos'] as $ind => $alm) {
				    			$alm_nombre = $alm['cantidad']. ' ' . $alm['medida_casera'] . ' ' .$alm['nombre'];
								$text = '';
				    			if(!empty($alm['alternativos'])){
				    				foreach ($alm['alternativos'] as $index => $alm_alter) {
				    					$text .= ' o ' . $alm_alter['cantidad']. ' ' . $alm_alter['medida_casera'] . ' ' .$alm_alter['nombre'];
				    				}
				    			}
				    			$this->pdf->MultiCell(0,5,chr(127). ' '.ucfirst(strtolower_total(utf8_decode($alm_nombre . $text))),0,1,'L',true);
				    		}
			    		}
			    	}

			    	$this->pdf->SetLeftMargin(0);
			    	$this->pdf->Ln(2);
				}else{
			    	$this->pdf->SetLeftMargin(10);
			    	$this->pdf->SetTextColor(83,83,83);
			    	$this->pdf->SetFillColor(255,255,255);

			    	if($consulta['tipo_dieta'] == 'SG'){ // SIMPLE GENERAL 
			    		$this->pdf->SetDrawColor($r,$g,$b);
			    		$this->pdf->SetLineWidth(1.2);
			    		//var_dump( $this->pdf->GetX(),$this->pdf->GetY() ); exit(); 
			    		$this->pdf->Line($this->pdf->GetX(),$this->pdf->GetY(),200,$this->pdf->GetY());
			    		$this->pdf->SetWidths(array(80, 30));
			    		$arrTurno = array(
							'data'=> array(
								utf8_decode('  ' . ucwords(strtoupper_total(($turno['descripcion'])))) .' - '.$turno['hora'].':'.$turno['min'].' '.$turno['tiempo'].'.',
								' '
							)
						);
						$this->pdf->Ln(4);
						// $data,$fill=FALSE,$border=0,$arrBolds=FALSE,$heigthCell=FALSE,$arrTextColor=FALSE,$arrBGColor=FALSE,$arrImage=FALSE,$bug=FALSE,$arrFontSize=FALSE
						
						$this->pdf->Row($arrTurno['data'],true,0,FALSE,6); 
						$this->pdf->SetFont('Arial','',11);
			    		$this->pdf->MultiCell(0, 7, '    '.utf8_decode($turno['indicaciones']));
			    	}

			    	if($consulta['tipo_dieta'] == 'CG'){ // COMPUESTO GENERAL 
			    		$text_total = '';
			    		if(!empty($turno['alimentos'])){
				    		foreach ($turno['alimentos'] as $ind => $alm) {
				    			$text = $alm['cantidad'] . ' ' .$alm['medida_casera'] . ' ' . $alm['nombre'];				    			
				    			if(!empty($alm['alternativos'])){
				    				foreach ($alm['alternativos'] as $index => $alm_alter) {
				    					$text .= ' o ' . $alm_alter['cantidad'] . ' '. $alm_alter['medida_casera'] . ' ' . $alm_alter['nombre'];
				    				}
				    			}

				    			$text_total .= $text . ', ';
				    		}
			    		}

			    		if(strlen($text_total) > 3){
			    			$text_total = substr($text_total, 0, -2);
			    		}

				    	// $this->pdf->MultiCell(0,5,utf8_decode(strtoupper_total($turno['descripcion'])) . ': ' . ucfirst(strtolower_total(utf8_decode($text_total))),0,1,'L',true);
				    	$this->pdf->MultiCell(0,5,utf8_decode(strtoupper_total($turno['descripcion'])) . ': ' . utf8_decode($text_total),0,1,'L',true);
			    	}

			    	$this->pdf->Ln(2);
			    	$this->pdf->SetLeftMargin(0);
			    	$this->pdf->Ln(5);
				}
			}

			//footer 
			$this->pdf->AddPage('P','A4');
			$this->pdf->SetMargins(8, 8, 8);
	    	$this->pdf->SetAutoPageBreak(false);
	    	// $configuracion = GetConfiguracion();
    		$this->footerPlan($consulta,8,GetConfiguracion());
		}

		if($consulta['tipo_dieta'] == 'SD' || $consulta['tipo_dieta'] == 'CD'){
			$anchoBloque =  $this->pdf->GetPageWidth() / 3;
			$anchoCeldaBloque =  ($this->pdf->GetPageWidth()-24) / 3;
			$altoBloque =  ($this->pdf->GetPageHeight() - (25+21)) / 3;
			$yInicial = $this->pdf->GetY();
			$this->pdf->SetLeftMargin(0);
			$this->pdf->SetRightMargin(0);
			$posX = 0;
			foreach ($arrayPlan as $indDia => $dia) {
				$this->pdf->SetTextColor(255,255,255);
				if($dia['id']==1 || $dia['id']==2 || $dia['id']==3){
					$this->pdf->SetFillColor(0,156,222);
					$posY = $yInicial;
					$this->pdf->SetXY($posX, $posY);
				}

				if($dia['id']==4 || $dia['id']==5 || $dia['id']==6){
					$this->pdf->SetFillColor(106,220,0);
					$posY = $yInicial + $altoBloque;
					$this->pdf->SetXY($posX, $posY);
				}

				if($dia['id']==7){
					$this->pdf->SetFillColor(255,0,100);
					$posY = $yInicial + ($altoBloque*2);
					$this->pdf->SetXY($posX, $posY);
				}

				$this->pdf->SetFont('Arial','B',15);

		    	$this->pdf->Cell($anchoBloque,7,ucwords(strtolower_total(utf8_decode($dia['nombre_dia']))),0,1,'C',true);
		    	$this->pdf->Ln(3);
		    	if($dia['id']==1 || $dia['id']==4 || $dia['id']==7){
		    		$this->pdf->SetX($posX+8);
		    	}else{
		    		$this->pdf->SetX($posX+4);
		    	}
		    	
				$this->pdf->SetFont('Arial','',9);
				$colorTurno = 0;
				$xInicial = $this->pdf->GetX();
		    	foreach ($dia['turnos'] as $indTurno => $turno) {
		    		$this->pdf->SetX($xInicial);
		    		if($colorTurno % 2 == 0){
		    			$this->pdf->SetTextColor(0,0,0);
		    		}else{
		    			$this->pdf->SetTextColor(83,83,83);
		    		}

		    		
		    		if($consulta['tipo_dieta'] == 'SD'){		    			
		    			$this->pdf->cell(2,4,chr(127).' ',0,0,'L',FALSE);
		    			$text = ucwords(strtolower_total(utf8_decode($turno['descripcion']) .': ' . utf8_decode($turno['indicaciones'])));
		    			$this->pdf->MultiCell($anchoCeldaBloque-5,4,$text,0,'L',FALSE);
		    		}

		    		if($consulta['tipo_dieta'] == 'CD'){
		    			$text = '';
		    			if(!empty($turno['alimentos'])){
				    		foreach ($turno['alimentos'] as $ind => $alm) {
				    			$text .= $alm['cantidad'] . ' ' . $alm['medida_casera'] . ' ' . $alm['nombre'];
				    			if(!empty($alm['alternativos'])){
				    				foreach ($alm['alternativos'] as $index => $alm_alter) {
				    					$text .= ' o ' . $alm_alter['cantidad'] . ' ' . $alm_alter['medida_casera'] . ' ' . $alm_alter['nombre'];
				    				}
				    			}
				    			$text .= ' + ';
				    		}
			    		}

			    		$this->pdf->cell(2,4,chr(127).' ',0,0,'L',FALSE);		    			
			    		$result = (strlen($text)>0) ? substr($text,0,-3) : '' ;
		    			$text_final = ucwords(strtolower_total(utf8_decode($turno['descripcion']) .': ' . utf8_decode($result)));
		    			$this->pdf->MultiCell($anchoCeldaBloque-5,4,$text_final,0,'L',FALSE);
		    		}
		    		$this->pdf->Ln(1);
		    		$colorTurno++;
		    	}

		    	$posX += $anchoBloque;
		    	if($dia['id'] % 3 == 0){
		    		$posX = 0;
		    	}

		    	if($dia['id']==7){ 
					//footer /*recomendaciones*/ 
					// $this->pdf->AddPage('P','A4');
					// $this->pdf->SetMargins(8, 8, 8);
			  //   	$this->pdf->SetAutoPageBreak(false);

					$this->pdf->SetTextColor(255,255,255);
					$this->pdf->SetXY($posX,$yInicial + ($altoBloque*2));
					$this->pdf->SetFont('Arial','I',12);
					$this->pdf->Cell($anchoBloque,7,'* Recomendaciones',0,1,'C',true);
					$this->pdf->Ln(5);
					$this->pdf->SetX($posX);
					$this->pdf->SetLeftMargin($posX + 6);
					$this->pdf->SetRightMargin( $posX + $anchoBloque - 6);
					$this->pdf->SetTextColor(0,0,0);
					$indicaciones = ucfirst(strtolower_total(utf8_decode($consulta['indicaciones_dieta'])));
					$this->pdf->MultiCell($anchoCeldaBloque-6,4,$indicaciones,0,'L',FALSE);

					$this->pdf->SetY(-20);
					$this->pdf->SetFont('Arial','',14);
					$profesional = 'Lic. ' . ucwords(strtolower_total(utf8_decode($consulta['nombre'] . ' ' . $consulta['apellidos'] )));
					$this->pdf->MultiCell($anchoBloque,7,$profesional,0,'L',FALSE);
					$this->pdf->Cell($anchoBloque,7,'CNP: ' . $consulta['num_colegiatura'],0,1,'L',FALSE);

					/*otros datos*/
					$posX += $anchoBloque;
					$this->pdf->SetXY($posX,$yInicial + ($altoBloque*2));
					$this->pdf->Cell($anchoBloque,7,'',0,1,'C',true);
					$this->pdf->Ln(5);
					$this->pdf->SetX($posX);
					$this->pdf->SetTextColor(0,0,0);
					$this->pdf->SetFont('Arial','',20);
					$this->pdf->Cell($anchoBloque, 12, utf8_decode('PRÓXIMA CITA:'), 0, 1, 'C', false);
					$this->pdf->SetX($posX);
					$this->pdf->Image('assets/images/icons/calendario.png',$posX+10,null,$anchoBloque-20);

					if(!empty($consulta['prox_cita'])){
						$this->pdf->SetXY($posX, $this->pdf->GetY() - 28);
						$this->pdf->SetFont('Arial','B',55);
						$dia_fecha = date('d', strtotime($consulta['prox_cita']));
						$this->pdf->Cell($anchoBloque, 12, $dia_fecha, 0, 1, 'C', false);
						$this->pdf->SetX($posX);
						$this->pdf->SetFont('Arial','',16);
						$this->pdf->Cell($anchoBloque, 12, formatoSoloMes($consulta['prox_cita']), 0, 1, 'C', false);
					}else{
						$this->pdf->SetXY($posX, $this->pdf->GetY() - 25);
						$this->pdf->SetFont('Arial','B',20);
						$this->pdf->Cell($anchoBloque, 12, 'NO TIENE', 0, 1, 'C', false);
					}

					$this->pdf->SetFont('Arial','',12);
					$this->pdf->SetXY($posX,-16);
					$this->pdf->Cell($anchoBloque,5,$configuracion['pagina_web'],0,1,'C',FALSE);
					$this->pdf->SetX($posX);
					$this->pdf->Cell($anchoBloque,5,'cel.: ' . $configuracion['celular'],0,1,'C',FALSE);
					$this->pdf->SetX($posX);
					$this->pdf->Cell($anchoBloque,5,$configuracion['correo'],0,1,'C',FALSE);
				}
			}
		}

    	//salida
		$timestamp = date('YmdHis');
		$nombreArchivo = 'assets/images/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf';
		$result = $this->pdf->Output( 'F', $nombreArchivo);

		$arrData['urlTempPDF'] = $nombreArchivo;

		if($enviarCorreo){
			$nombrePaciente = ucwords(strtolower_total($paciente['paciente']));
			if(!$this->enviar_correo_pdf_plan($configuracion,$nombrePaciente,$consulta,$nombreArchivo,$arrayMails)){
				$arrData['flag'] = 0;
				$arrData['message'] = 'Ha ocurrido un error enviando el Plan Alimentario'; 
			}else{
				$arrData['flag'] = 1;
				$arrData['message'] = 'Se envió el Plan Alimentario correctamente'; 
			}
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	private function enviar_correo_pdf_plan($configuracion, $paciente, $consulta, $nombreArchivo, $listaCorreos){
		$cuerpo = '<p><b>PLAN ALIMENTARIO - '. strtoupper_total( darFechaCumple($consulta['fecha_atencion'])) .'</b></p>
				   <p>Hola, '.$paciente.'</p>
				   <p> Te envío el detalle de tu dieta en el archivo adjunto.</p>
				   <p>Nos vemos en tu próxima cita. </p>
				   <p>Saludos.</p>';
		$asunto = 'DESCARGA TU PLAN ALIMENTARIO AQUI.'; 
		$setFromAleas = $configuracion['empresa'];
		$this->load->library('My_PHPMailer');
		date_default_timezone_set('UTC');

		$mail = new PHPMailer();
		$mail->IsSMTP(true);
		//$mail->SMTPDebug = 1;
		$mail->SMTPAuth = ($configuracion['smtp_auth'] == 1) ? TRUE : FALSE;
		$mail->SMTPSecure = $configuracion['smtp_secure'];
		$mail->Host = $configuracion['smtp_host'];
		$mail->Port = $configuracion['smtp_port'];
		$mail->Username =  $configuracion['smtp_username'];
		$mail->Password = $configuracion['smtp_password'];
		$mail->SetFrom($configuracion['smtp_username'],$setFromAleas);
		$mail->AddReplyTo($configuracion['smtp_username'],$setFromAleas);
		$mail->Subject = $asunto;
		$mail->IsHTML(true);
		$mail->AltBody = $cuerpo;
		$mail->MsgHTML($cuerpo);
		$mail->CharSet = 'UTF-8';
		$mail->addStringAttachment(file_get_contents($nombreArchivo), 'PlanAlimentario.pdf');

		foreach ($listaCorreos as $key => $email) {
			$mail->addAddress($email);						
		} 

		return $mail->Send();
	}
}
