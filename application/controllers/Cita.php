<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cita extends CI_Controller {
	public function __construct(){
        parent::__construct();
        // Se le asigna a la informacion a la variable $sessionVP.
        $this->sessionVP = @$this->session->userdata('sess_vp_'.substr(base_url(),-8,7));
        $this->load->helper(array('fechas_helper', 'otros_helper'));
        $this->load->model(array('model_cita','model_consulta'));
    }

    public function listar_citas(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_cita->m_cargar_citas($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			$es_unica = ($this->model_cita->m_cuenta_citas($row['fecha'],$row['hora_desde']) == 1) ? TRUE : FALSE;

			if(empty($row['idatencion'])){
				$clases = 'b-l b-2x b-primary';
			}else{
				$clases = 'b-l b-2x b-success';
			}

			if($es_unica){
				$clases .= ' unico';
			}

			$className = array($clases);
			
			array_push($arrListado,
				array(
					'id' => $row['idcita'],
					'hora_desde_sql' => $row['hora_desde'],
					'hora_hasta_sql' => $row['hora_hasta'],
					'hora_desde' => strtotime($row['hora_desde']),
					'hora_hasta' => strtotime($row['hora_hasta']),
					'estado_ci' => $row['estado_ci'],
					'fecha' => $row['fecha'],
					'cliente' => array(
							'idcliente' => $row['idcliente'],
							'cod_historia_clinica' => $row['cod_historia_clinica'],
							'nombre' => $row['nombre'],
							'apellidos' => $row['apellidos'],
							'sexo' => $row['sexo'],
							'email' => $row['email'],
							'estatura' => (float)$row['estatura'],
							'edad' => (int)devolverEdad($row['fecha_nacimiento']),
							'paciente' => $row['nombre'] . ' ' . $row['apellidos'],
						),
					'profesional' => array(
							'idprofesional' => $row['idprofesional'],
							'profesional' => $row['profesional'],
						),
					'ubicacion' => array(
							'id' => $row['idubicacion'],
							'descripcion' => $row['descripcion_ub'],
						),
					'atencion' => array(
							'idatencion' => (int)$row['idatencion'],
							'fecha_atencion' => $row['fecha_atencion'],
							'diagnostico_notas' => $row['diagnostico_notas'],
							'indicaciones_dieta' => $row['indicaciones_dieta'],
							'tipo_dieta' => $row['tipo_dieta'],
							'paciente' => $row['nombre'] . ' ' . $row['apellidos'],
						),
					'className' => $className,
					'start' => $row['fecha'] .' '. $row['hora_desde'],
					'end' => $row['fecha'] .' '. $row['hora_hasta'],
					//'title' => $row['nombre'] . ' ' . $row['apellidos'],
					'title' => darFormatoHora($row['hora_desde']). ' - ' . darFormatoHora($row['hora_hasta']) . ' | ' . $row['nombre'] . ' ' . $row['apellidos'],
					'allDay' => FALSE,
					'durationEditable' => FALSE,
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

	public function ver_popup_formulario(){
		$this->load->view('cita/cita_formView');
	}
	public function listar_proximas_citas()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$allInputs['numeroCitas'] = 10; 
		$lista = $this->model_cita->m_cargar_proximas_citas($allInputs);
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'idcita' => $row['idcita'],
					'dia' => $row['fecha'],
					'hora' => $row['hora_desde'],
					'paciente' => strtoupper($row['nombre'].' '.$row['apellidos']),
					'canal' => strtoupper($row['descripcion_ub'])
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
	public function registrar_cita(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['flag'] = 0;
		$arrData['message'] = 'Ha ocurrido un error registrando la cita.';

		/*aqui van las validaciones*/
		if(empty($allInputs['cliente']['idcliente'])){
			$arrData['flag'] = 0;
			$arrData['message'] = 'Debe seleccionar un paciente.';
			$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
		}

		if(empty($allInputs['ubicacion']['id'])){
			$arrData['flag'] = 0;
			$arrData['message'] = 'Debe seleccionar una ubicacion.';
			$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
		}

		if(empty($allInputs['fecha'])){
			$arrData['flag'] = 0;
			$arrData['message'] = 'Debe seleccionar una fecha.';
			$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
		}

		if(empty($allInputs['hora_desde']) || empty($allInputs['hora_hasta'])){
			$arrData['flag'] = 0;
			$arrData['message'] = 'Debe seleccionar horas validas.';
			$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
		}

		if(strtotime($allInputs['hora_desde_str']) >= strtotime($allInputs['hora_hasta_str'])){
			$arrData['flag'] = 0;
			$arrData['message'] = 'Debe seleccionar un rango de horas valido.';
			$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
		}

		$hora_inicio_calendar = strtotime('07:00:00');
		$hora_fin_calendar = strtotime('23:00:00');		

		if(strlen($allInputs['hora_desde_str']) == 7){
			$horadesde = '0' . strtotime(substr($allInputs['hora_desde_str'], 0,4) . ':00');
		}else{
			$horadesde = strtotime(substr($allInputs['hora_desde_str'], 0,5) . ':00');
		}

		if(strlen($allInputs['hora_hasta_str']) == 7){
			$horahasta = '0' . strtotime(substr($allInputs['hora_hasta_str'], 0,4) . ':00');
		}else{
			$horahasta = strtotime(substr($allInputs['hora_hasta_str'], 0,5) . ':00');
		}
		
		if(!($horadesde  >= $hora_inicio_calendar &&  $horahasta <= $hora_fin_calendar)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'Debe seleccionar un rango de horas permitido.';
			$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
		}

		$data = array(
			'idcliente' => $allInputs['cliente']['idcliente'],
			'idubicacion' => $allInputs['ubicacion']['id'],
			'idprofesional' => $this->sessionVP['idprofesional'],
			'fecha' => Date('Y-m-d',strtotime($allInputs['fecha'])),
			'hora_desde' => Date('H:i:s',$horadesde),
			'hora_hasta' => Date('H:i:s',$horahasta),
			'createdat' => date('Y-m-d H:i:s'),
			'updatedat' => date('Y-m-d H:i:s')
			);

		if($this->model_cita->m_registrar($data)){
			$idcita = GetLastId('idcita', 'cita');
			if(!empty($allInputs['consultaOrigen']) && !empty($allInputs['consultaOrigen']['idatencion'])){
				//idproxcita en atencion
				if($this->model_consulta->m_act_idproxcita($allInputs['consultaOrigen']['idatencion'], $idcita)){
					$arrData['flag'] = 1;
					$arrData['message'] = 'Cita registrada.';
				}
			}else{
				$arrData['flag'] = 1;
				$arrData['message'] = 'Cita registrada.';
			}
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function drop_cita(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['flag'] = 0;
		$arrData['message'] = 'Ha ocurrido un error actualizando la cita';

		$cita = $this->model_cita->m_consulta_cita($allInputs['event']['id']);

		//print_r($allInputs);
		$nuevaFecha = date('Y-m-d',strtotime($allInputs['event']['start']));
		$nuevaHora= date('H:i:s',strtotime($allInputs['event']['start']));
		//print_r($nuevaFecha);

		$interval = $allInputs['event']['hora_hasta'] - $allInputs['event']['hora_desde'];
		$nuevaHoraInicio = strtotime($allInputs['event']['start']);
		$nuevaHoraFin = $nuevaHoraInicio + $interval;
		//print_r($nuevaHoraInicio . ' - ' . $nuevaHoraFin);
		$data = array(
			'hora_desde' => Date('H:i:s',$nuevaHoraInicio),
			'hora_hasta' => Date('H:i:s',$nuevaHoraFin),
			'fecha' => $nuevaFecha,
			'updatedat' => date('Y-m-d H:i:s')
			);
		$this->db->trans_start();
		if($this->model_cita->m_actualizar($data, $allInputs['event']['id'])){
			if(!empty($cita['idatencion'])){
				$datos = array(
					'fecha' => $nuevaFecha,
					'idatencion' => $cita['idatencion']
				);
				if($this->model_consulta->m_act_fecha_atencion($datos)){
					$arrData['flag'] = 1;
					$arrData['message'] = 'Consulta actualizada.';
				}
			}else{
				$arrData['flag'] = 1;
				$arrData['message'] = 'Cita actualizada.';
			}
		}
		$this->db->trans_complete();

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function actualizar_cita(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['flag'] = 0;
		$arrData['message'] = 'Ha ocurrido un error actualizando la cita.';

		if(empty($allInputs['ubicacion']['id'])){
			$arrData['flag'] = 0;
			$arrData['message'] = 'Debe seleccionar una ubicacion.';
			$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
		}

		if(empty($allInputs['fecha'])){
			$arrData['flag'] = 0;
			$arrData['message'] = 'Debe seleccionar una fecha.';
			$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
		}

		if(empty($allInputs['hora_desde']) || empty($allInputs['hora_hasta'])){
			$arrData['flag'] = 0;
			$arrData['message'] = 'Debe seleccionar horas validas.';
			$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
		}

		if(strtotime($allInputs['hora_desde_str']) >= strtotime($allInputs['hora_hasta_str'])){
			$arrData['flag'] = 0;
			$arrData['message'] = 'Debe seleccionar un rango de horas valido.';
			$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
		}

		$hora_inicio_calendar = strtotime('07:00:00');
		$hora_fin_calendar = strtotime('23:00:00');		

		if(strlen($allInputs['hora_desde_str']) == 7){
			$horadesde = '0' . strtotime(substr($allInputs['hora_desde_str'], 0,4) . ':00');
		}else{
			$horadesde = strtotime(substr($allInputs['hora_desde_str'], 0,5) . ':00');
		}

		if(strlen($allInputs['hora_hasta_str']) == 7){
			$horahasta = '0' . strtotime(substr($allInputs['hora_hasta_str'], 0,4) . ':00');
		}else{
			$horahasta = strtotime(substr($allInputs['hora_hasta_str'], 0,5) . ':00');
		}
		
		if(!($horadesde  >= $hora_inicio_calendar &&  $horahasta <= $hora_fin_calendar)){
			$arrData['flag'] = 0;
			$arrData['message'] = 'Debe seleccionar un rango de horas permitido.';
			$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
		}

		$data = array(
			'idcliente' => $allInputs['cliente']['idcliente'],
			'idubicacion' => $allInputs['ubicacion']['id'],
			'idprofesional' => $this->sessionVP['idprofesional'],
			'fecha' => Date('Y-m-d',strtotime($allInputs['fecha'])),
			'hora_desde' => Date('H:i:s',$horadesde),
			'hora_hasta' => Date('H:i:s',$horahasta),
			'createdat' => date('Y-m-d H:i:s'),
			'updatedat' => date('Y-m-d H:i:s')
			);
		
		if($this->model_cita->m_actualizar($data, $allInputs['id'])){
			$arrData['flag'] = 1;
			$arrData['message'] = 'Cita actualizada.';
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function anular_cita(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['flag'] = 0;
		$arrData['message'] = 'Ha ocurrido un error actualizando la cita.';

		$cita = $this->model_cita->m_consulta_cita($allInputs['id']);
		if(!empty($cita['idatencion'])){
			$arrData['flag'] = 0;
			$arrData['message'] = 'Solo puede anular citas sin atenciones.';
			$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
		    return;
		}

		if($this->model_cita->m_anular($allInputs['id'])){
			$arrData['flag'] = 1;
			$arrData['message'] = 'Cita anulada.';
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

}
