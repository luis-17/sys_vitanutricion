<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profesional extends CI_Controller {
	public function __construct()
    {
        parent::__construct();
        // Se le asigna a la informacion a la variable $sessionVP.
        $this->sessionVP = @$this->session->userdata('sess_vp_'.substr(base_url(),-8,7));
        $this->load->helper(array('fechas','otros','imagen'));        
        $this->load->model(array('model_profesional','model_usuario'));

    }
    // LISTAS, COMBOS Y AUTOCOMPLETES
	public function listar_profesional()
	{
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$paramPaginate = $allInputs['paginate'];
		$lista = $this->model_profesional->m_cargar_profesional($paramPaginate);
		$totalRows = $this->model_profesional->m_count_profesional($paramPaginate);
		$arrListado = array();
		// var_dump($lista); exit();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'idprofesional' => $row['idprofesional'],
					'especialidad' => $row['especialidad'],
					'nombre' => $row['nombre'],
					'apellidos' => $row['apellidos'],
					'correo' => $row['correo'],
					'fecha_nacimiento' => darFormatoDMY($row['fecha_nacimiento']),
					'fecha_nacimiento_for' => darFormatoYMD($row['fecha_nacimiento']),
					'num_colegiatura' => $row['num_colegiatura'],
					'idusuario' => $row['idusuario'],
					'nombre_foto' => $row['nombre_foto'],
					'usuario' => $row['username'],
					'idgrupo' => $row['idgrupo']					
				)
			);
			// var_dump($row['fecha_nacimiento'], darFormatoDMY($row['fecha_nacimiento'])); exit(); 
		}

    	$arrData['datos'] = $arrListado;
    	$arrData['paginate']['totalRows'] = $totalRows;
    	$arrData['message'] = '';
    	$arrData['flag'] = 1;
		if(empty($lista)){
			$arrData['flag'] = 0;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function listar_profesional_cbo(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_profesional->m_cargar_profesional_cbo();
		$arrListado = array();
		foreach ($lista as $row) {
			array_push($arrListado,
				array(
					'id' => $row['idprofesional'],
					'profesional' => $row['profesional'],
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
	public function registrar_profesional()
	{
		//$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al registrar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// AQUI ESTARAN LAS VALIDACIONES
    	$idusuario = $this->input->post('idusuario');
    	if(empty($idusuario)){
    		$arrData['message'] = 'los datos del usuario no son correctos.';
			$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}
    	$allInputs['idusuario'] = $this->input->post('idusuario');
    	$allInputs['idespecialidad'] = $this->input->post('idespecialidad');
    	$allInputs['nombre'] = $this->input->post('nombre');
    	$allInputs['apellidos'] = $this->input->post('apellidos');
    	$allInputs['correo'] = $this->input->post('correo');
    	$allInputs['fecha_nacimiento'] = $this->input->post('fecha_nacimiento');
    	$allInputs['num_colegiatura'] = $this->input->post('num_colegiatura');
    	$allInputs['createdAt'] = date('Y-m-d H:i:s');
    	$allInputs['updatedAt'] = date('Y-m-d H:i:s');
    	$allInputs['Base64Img'] = $this->input->post('myCroppedImage');
    	$allInputs['nombre_foto'] = NULL;

    	if(!empty($allInputs['Base64Img'])){
    		$allInputs['nombre_foto'] = $allInputs['nombre'].date('YmdHis').'.png';
    		subir_imagen_Base64($allInputs['Base64Img'], 'assets/images/dinamic/profesionales/' ,$allInputs['nombre_foto']);
    	}

    	// INICIA EL REGISTRO
		if($this->model_profesional->m_registrar($allInputs)){
			$arrData['message'] = 'Se registraron los datos del profesional correctamente';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}	
	public function subir_foto_profesional(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al subir la foto, inténtelo nuevamente';
    	$arrData['flag'] = 0;

    	if(!empty($allInputs['croppedImage'])){
    		$allInputs['nombre_foto'] = url_title($allInputs['nombre']).date('YmdHis').'.png';

    		subir_imagen_Base64($allInputs['croppedImage'], 'assets/images/dinamic/profesionales/' ,$allInputs['nombre_foto']);
    		if($this->model_profesional->m_editar_foto($allInputs)){
	    		$arrData['message'] = 'La foto se cambió correctamente';
	    		$arrData['flag'] = 1;
	    		$arrData['datos'] = $allInputs['nombre_foto'];
	    	}
    	}

    	$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}	
	public function editar_profesional(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al editar los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	
    	if(empty($allInputs['idusuario'])){
    		$arrData['message'] = 'los datos del usuario no son correctos.';
			$arrData['flag'] = 0;
			$this->output
			    ->set_content_type('application/json')
			    ->set_output(json_encode($arrData));
			return;
    	}    	

		if($this->model_profesional->m_editar($allInputs)){
			$arrData['message'] = 'Se editaron los datos del profesional correctamente ';
    		$arrData['flag'] = 1;
		}
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}	
	public function anular_profesional(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['message'] = 'Error al anular los datos, inténtelo nuevamente';
    	$arrData['flag'] = 0;
    	// var_dump($allInputs); exit();
    	$this->db->trans_start();
		if($this->model_profesional->m_anular($allInputs)){
			if($this->model_usuario->m_anular($allInputs)){
				$arrData['message'] = 'Se anularon los datos correctamente';
	    		$arrData['flag'] = 1;				
			}

		}
		$this->db->trans_complete();		
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}	
}
