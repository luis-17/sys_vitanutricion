<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Consulta extends CI_Controller {
	public function __construct(){
        parent::__construct();
        // Se le asigna a la informacion a la variable $sessionVP.
        $this->sessionVP = @$this->session->userdata('sess_vp_'.substr(base_url(),-8,7));
        $this->load->helper(array('fechas_helper','otros_helper'));
        $this->load->model(array('model_consulta', 'model_cita', 'model_paciente'));
        $this->load->library('Fpdfext');
    }

	public function registrar_consulta(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['flag'] = 0;
		$arrData['message'] = 'Ha ocurrido un error registrando la consulta.';

		/*aqui van las validaciones*/

		/*registro de datos*/
		$this->db->trans_start();
		if($this->model_consulta->m_registrar($allInputs)){
			$idatencion = GetLastId('idatencion', 'atencion');
			$datos = array (
				'idcita' => $allInputs['cita']['id'],
				'fecha' => date('Y-m-d', strtotime($allInputs['consulta']['fecha_atencion'])),
			);
			if($this->model_cita->m_act_fecha_cita($datos)){
				$arrData['flag'] = 1;
				$arrData['message'] = 'La consulta ha sido registrada.';
				$arrData['idatencion'] = $idatencion;
			}
		}
		$this->db->trans_complete();

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function actualizar_consulta(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['flag'] = 0;
		$arrData['message'] = 'Ha ocurrido un error actualizando la consulta.';
		// var_dump($allInputs); exit();
		if(empty($allInputs['cita']['id'])){
			$allInputs['cita']['id'] = $allInputs['consulta']['idcita'];
		}
		$this->db->trans_start();
		if($this->model_consulta->m_actualizar($allInputs)){
			$datos = array (
				'idcita' => $allInputs['cita']['id'],
				'fecha' => date('Y-m-d', strtotime($allInputs['consulta']['fecha_atencion'])),
			);
			if($this->model_cita->m_act_fecha_cita($datos)){
				$arrData['flag'] = 1;
				$arrData['message'] = 'La consulta ha sido actualizada.';
			}
		}
		$this->db->trans_complete();

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function anular_consulta(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['flag'] = 0;
		$arrData['message'] = 'Ha ocurrido un error actualizando la consulta.';
		// var_dump($allInputs['atencion']['idatencion']); exit();

		if($this->model_consulta->m_anular($allInputs['atencion']['idatencion'])){
			$arrData['flag'] = 1;
			$arrData['message'] = 'Consulta anulada.';
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function cargar_consulta(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['flag'] = 0;
		$arrData['message'] = 'No se encontro la atencion.';
		// var_dump($allInputs); exit();
		$row = $this->model_consulta->m_consultar_atencion($allInputs['atencion']['idatencion']);

		if(!empty($row['idatencion'])){
				$atencion = array(
					'idcliente' 			=> $row['idcliente'],
					'idcita' 				=> $row['idcita'],
					'idatencion' 			=> $row['idatencion'],
					'si_embarazo' 			=> $row['si_embarazo'] == 1 ? TRUE:FALSE,
					'peso' 					=> (float)$row['peso'],
					'porc_masa_grasa' 		=> (float)$row['porc_masa_grasa'],
					'porc_masa_libre' 		=> (float)$row['porc_masa_libre'],
					'porc_masa_muscular' 	=> (float)$row['porc_masa_muscular'],
					'kg_masa_muscular' 		=> (float)$row['kg_masa_muscular'],
					'porc_agua_corporal' 	=> (float)$row['porc_agua_corporal'],
					'kg_agua_corporal' 		=> (float)$row['kg_agua_corporal'],
					'puntaje_grasa_visceral'=> (float)$row['puntaje_grasa_visceral'],
					/*'porc_grasa_visceral' 	=> (float)$row['porc_grasa_visceral'],
					'kg_grasa_visceral' 	=> (float)$row['kg_grasa_visceral'],*/
					'cm_pecho' 				=> (float)$row['cm_pecho'],
					'cm_antebrazo' 			=> (float)$row['cm_antebrazo'],
					'cm_cintura' 			=> (float)$row['cm_cintura'],
					'cm_abdomen' 			=> (float)$row['cm_abdomen'],
					'cm_cadera_gluteo' 		=> (float)$row['cm_cadera_gluteo'],
					'cm_muslo' 				=> (float)$row['cm_muslo'],
					'cm_hombros' 			=> (float)$row['cm_hombros'],
					'cm_biceps_relajados' 	=> (float)$row['cm_biceps_relajados'],
					'cm_biceps_contraidos' 	=> (float)$row['cm_biceps_contraidos'],
					'cm_muneca' 			=> (float)$row['cm_muneca'],
					'cm_rodilla' 			=> (float)$row['cm_rodilla'],
					'cm_gemelos' 			=> (float)$row['cm_gemelos'],
					'cm_tobillo' 			=> (float)$row['cm_tobillo'],
					'cm_tricipital' 		=> (float)$row['cm_tricipital'],
					'cm_bicipital' 			=> (float)$row['cm_bicipital'],
					'cm_subescapular' 		=> (float)$row['cm_subescapular'],
					'cm_axilar' 			=> (float)$row['cm_axilar'],
					'cm_pectoral' 			=> (float)$row['cm_pectoral'],
					'cm_suprailiaco' 		=> (float)$row['cm_suprailiaco'],
					'cm_supraespinal' 		=> (float)$row['cm_supraespinal'],
					'cm_abdominal' 			=> (float)$row['cm_abdominal'],
					'cm_pierna' 			=> (float)$row['cm_pierna'],
					'diagnostico_notas' 	=> $row['diagnostico_notas'],
					'resultados_laboratorio'=> $row['resultados_laboratorio'],
					'fecha_atencion' 		=> $row['fecha_atencion'],
					'estado_atencion' 		=> $row['estado_atencion'],
					'kg_masa_grasa' 		=> (float)$row['kg_masa_grasa'],
					'kg_masa_libre' 		=> (float)$row['kg_masa_libre'],
					'indicaciones_dieta' 	=> $row['indicaciones_dieta'],
					'tipo_dieta' 			=> $row['tipo_dieta'],

					'grasa_para_objetivo' 			=> (float)$row['grasa_para_objetivo'],
					'masa_muscular_para_objetivo' 	=> (float)$row['masa_muscular_objetivo']
				);

			$arrData['flag'] = 1;
			$arrData['message'] = 'Se encontro la atencion.';
			$arrData['datos'] = $atencion;
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function listar_ultima_consulta(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData['flag1'] = 0;
		$arrData['flag2'] = 0;
		$row = $this->model_consulta->m_cargar_ultima_atencion($allInputs['idcliente']);
		$antecedentes = $this->model_paciente->m_cargar_ultimos_antecedentes_paciente($allInputs);
		// var_dump($antecedentes); exit();
		if(!empty($row)){
			$arrData['flag1'] = 1;
		}
		if(!empty($antecedentes)){
			$arrData['flag2'] = 1;
		}
		$arrData['datos'] = $row;
		$arrData['antecedentes'] = $antecedentes;
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function listar_consultas_paciente(){
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$lista = $this->model_consulta->m_cargar_atenciones_paciente($allInputs['idcliente'],TRUE);
		$arrCabecera = array();
		$arrListado = array();
		foreach ($lista as $key => $row) {
			// array_push($arrListado, array(
			// 	'idatencion' => $row['idatencion'], 
			// 	'fecha_atencion' => $row['fecha_atencion'], listaAguaCorporal
			// 	)
			// );
			$arrListado['peso'][] = array('id' =>$key ,'valor' => $row['peso']);
			$arrListado['masa_grasa'][] = array('id' =>$key ,'valor' => $row['porc_masa_grasa']);
			$arrListado['masa_libre'][] = array('id' =>$key ,'valor' => $row['porc_masa_libre']);

			$arrListado['porc_agua'][] = array('id' => $key, 'valor' => $row['porc_agua_corporal']);
			$arrListado['agua_corporal'][] = array('id' => $key, 'valor' => $row['kg_agua_corporal']);
			$arrListado['porc_masa'][] = array('id' =>$key ,'valor' => $row['porc_masa_muscular']);
			$arrListado['masa_muscular'][] = array('id' =>$key ,'valor' => $row['kg_masa_muscular']);
			$arrListado['porc_grasa'][] = array('id' => $key, 'valor' => $row['porc_grasa_visceral']);
			//$arrListado['grasa_visceral'][] = array('id' => $key, 'valor' => $row['kg_grasa_visceral']);
			$arrListado['cm_pecho'][] = array('id' => $key, 'valor' => $row['cm_pecho']);
			$arrListado['cm_antebrazo'][] = array('id' => $key, 'valor' => $row['cm_antebrazo']);
			$arrListado['cm_cintura'][] = array('id' => $key, 'valor' => $row['cm_cintura']);
			$arrListado['cm_abdomen'][] = array('id' => $key, 'valor' => $row['cm_abdomen']);
			$arrListado['cm_cadera_gluteo'][] = array('id' => $key, 'valor' => $row['cm_cadera_gluteo']);
			$arrListado['cm_muslo'][] = array('id' => $key, 'valor' => $row['cm_muslo']);
			$arrListado['cm_hombros'][] = array('id' => $key, 'valor' => $row['cm_hombros']);
			$arrListado['cm_biceps_relajados'][] = array('id' => $key, 'valor' => $row['cm_biceps_relajados']);
			$arrListado['cm_biceps_contraidos'][] = array('id' => $key, 'valor' => $row['cm_biceps_contraidos']);
			$arrListado['cm_muneca'][] = array('id' => $key, 'valor' => $row['cm_muneca']);
			$arrListado['cm_rodilla'][] = array('id' => $key, 'valor' => $row['cm_rodilla']);
			$arrListado['cm_gemelos'][] = array('id' => $key, 'valor' => $row['cm_gemelos']);
			$arrListado['cm_tobillo'][] = array('id' => $key, 'valor' => $row['cm_tobillo']);
			$arrListado['cm_tricipital'][] = array('id' => $key, 'valor' => $row['cm_tricipital']);
			$arrListado['cm_bicipital'][] = array('id' => $key, 'valor' => $row['cm_bicipital']);
			$arrListado['cm_subescapular'][] = array('id' => $key, 'valor' => $row['cm_subescapular']);
			$arrListado['cm_axilar'][] = array('id' => $key, 'valor' => $row['cm_axilar']);
			$arrListado['cm_pectoral'][] = array('id' => $key, 'valor' => $row['cm_pectoral']);
			$arrListado['cm_suprailiaco'][] = array('id' => $key, 'valor' => $row['cm_suprailiaco']);
			$arrListado['cm_supraespinal'][] = array('id' => $key, 'valor' => $row['cm_supraespinal']);
			$arrListado['cm_abdominal'][] = array('id' => $key, 'valor' => $row['cm_abdominal']);
			$arrListado['cm_pierna'][] = array('id' => $key, 'valor' => $row['cm_pierna']);
			$arrListado['diagnostico_notas'][] = array('id' => $key, 'valor' => $row['diagnostico_notas']);
			$arrListado['puntaje_grasa_visceral'][] = array('id' => $key, 'valor' => $row['puntaje_grasa_visceral']); 
			$arrListado['imc'][] = array(
				'id' => $key,
				'valor' => round($row['peso']*10000/($allInputs['estatura']*$allInputs['estatura']),2)
			);

			$arrCabecera[] =array(
				'idatencion' => $row['idatencion'],
				'fecha'=> DarFormatoDMY($row['fecha_atencion'])
			);
		}
		// var_dump($arrListado); exit();
		if(empty($lista)){
			$arrData['flag'] = 0;
		}else{
			$arrData['flag'] = 1;
		}
		$arrData['cabecera'] = $arrCabecera;
		$arrData['datos'] = $arrListado;
		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}

	public function imprimir_consulta(){
		$this->load->library('Ci_pchart');
		$allInputs = json_decode(trim($this->input->raw_input_stream),true);
		$arrData = array();
		$arrData['message'] = '';
    	$arrData['flag'] = 1;
    	$enviarCorreo = FALSE; 
    	// VALIDACION 
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

    	// DATOS
    	$configuracion = GetConfiguracion();
		$consulta = $this->model_consulta->m_consultar_atencion($allInputs['consulta']['idatencion']);
		$atenciones = $this->model_consulta->m_cargar_atenciones_paciente($consulta['idcliente'], FALSE, FALSE, $consulta['fecha_atencion']);
		//var_dump($consulta);
		$paciente = $this->model_paciente->m_cargar_paciente_por_id($consulta);
		/*var_dump($paciente);
		exit();*/

    	$this->pdf = new Fpdfext();
    	$this->pdf->AddPage('P','A4');
    	$this->pdf->SetMargins(8, 8, 8);
    	$this->pdf->SetAutoPageBreak(false);
		$this->pdf->SetFont('Arial','',13);		

		/*header*/
		$this->pdf->Image('assets/images/dinamic/' . $configuracion['logo_imagen'],8,8,0,20);
		$this->pdf->Cell(0,6,utf8_decode('ID: ' . str_pad($consulta['idcita'], 5, "0", STR_PAD_LEFT)),0,1,'R');
		$this->pdf->Cell(0,6,utf8_decode('Fecha: ' . date('d/m/Y',strtotime($consulta['fecha_atencion']))) ,0,1,'R');
		/*paciente*/
		$this->imprimir_paciente($consulta, $paciente);

		/*composicion corporal*/
		$posYCuadro = $this->pdf->GetY();
		$this->imprimir_composicion_corporal($consulta, $posYCuadro);		

		/*progreso*/
		$consultaAnterior = $this->model_consulta->m_cargar_atencion_anterior($consulta['idcliente'], $consulta['fecha_atencion']);
		$primeraConsulta = $this->model_consulta->m_cargar_primera_atencion($consulta['idcliente']);
		$this->imprimir_progreso($consulta, $consultaAnterior, $primeraConsulta,$paciente);		

		/*detalle peso*/
		$this->imprimir_cuadro_detalle_peso($paciente,$consulta, $posYCuadro);

		/*barras*/
		$anchoTotalBarras = $this->pdf->GetPageWidth() - 32;
		$this->pdf->SetXY(8,$this->pdf->GetY());
		$margen = 16;
		//BARRA IMC
		$this->imprimir_barra_imc($paciente,$consulta, $anchoTotalBarras, $margen);

		//BARRA % GRASA
		$this->imprimir_barra_grasa($consulta, $anchoTotalBarras, $margen);		

		//BARRA GRASA VISCERAL
		$this->imprimir_barra_grasa_visceral($consulta, $anchoTotalBarras, $margen);

		$this->pdf->AddPage('P','A4');
		$this->pdf->SetMargins(8, 8, 8);
    	$this->pdf->SetAutoPageBreak(false);
		
		/*graficos*/
		$arrayFechas = array();
		$arrayPeso = array();
		$arrayImc = array();
		$arrayGrasa = array();
		$arrayMasa = array();
		foreach ($atenciones as $key => $atencion) {
			array_unshift($arrayFechas, strtotime($atencion['fecha_atencion']));
			array_unshift($arrayPeso, (float)$atencion['peso']);
			array_unshift($arrayGrasa,(float)$atencion['porc_masa_grasa']);
			array_unshift($arrayMasa, (float)$atencion['porc_masa_muscular']);
			$imc = round((float)$atencion['peso'] / 
					(((float)$paciente['estatura']/100) * 
						((float)$paciente['estatura']/100)),2);
			array_unshift($arrayImc, $imc);
		}
		$margen = 6;
		$this->imprimir_grafico_peso($arrayFechas, $arrayPeso, $margen);
		$this->imprimir_grafico_imc($arrayFechas, $arrayImc, $margen);
		$this->imprimir_grafico_grasa_corporal($arrayFechas, $arrayGrasa, $margen);
		$this->imprimir_grafico_masa_muscular($arrayFechas, $arrayMasa, $margen);

		/*datos finales*/
		$this->imprimir_datos_finales($consulta, 8, $configuracion);

		/*output*/
		$timestamp = date('YmdHis');
		$nombreArchivo = 'assets/images/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf';
		$result = $this->pdf->Output( 'F', $nombreArchivo ); 
		$arrData['urlTempPDF'] = 'assets/images/dinamic/pdfTemporales/tempPDF_'. $timestamp .'.pdf';

		if($enviarCorreo){
			$nombrePaciente = ucwords(strtolower_total($paciente['paciente']));
			if(!$this->enviar_correo_pdf_ficha_pac($configuracion,$nombrePaciente,$consulta,$nombreArchivo,$arrayMails)){ 
				$arrData['flag'] = 0;
				$arrData['message'] = 'Ha ocurrido un error enviando la Ficha de Consulta'; 
			}else{
				$arrData['flag'] = 1;
				$arrData['message'] = 'Se envió la Ficha de Consulta correctamente'; 
			}
		}

		$this->output
		    ->set_content_type('application/json')
		    ->set_output(json_encode($arrData));
	}
	private function enviar_correo_pdf_ficha_pac($configuracion, $paciente, $consulta, $nombreArchivo, $listaCorreos)
	{
		$cuerpo = '<p><b>FICHA DE PACIENTE - '. strtoupper_total( darFechaCumple($consulta['fecha_atencion'])) .'</b></p>
				   <p>Hola, '.$paciente.'</p>
				   <p> Te envío la Ficha de Consulta en el archivo adjunto.</p>
				   <p>Nos vemos en tu próxima cita. </p>
				   <p>Saludos.</p>';
		$asunto = 'DESCARGA TU FICHA DE CONSULTA AQUI.'; 
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
		$mail->addStringAttachment(file_get_contents($nombreArchivo), 'FichaConsulta.pdf');

		foreach ($listaCorreos as $key => $email) {
			$mail->addAddress($email);						
		} 

		return $mail->Send();
	}
	private function imprimir_paciente($consulta, $paciente){
		/*paciente*/
		$this->pdf->SetY($this->pdf->GetY()+15);	
		$nombre = ucwords(strtolower_total($paciente['nombre'] . ' ' . $paciente['apellidos']));
		$this->pdf->Cell(0,6,utf8_decode('Nombre: ' . $nombre ) ,0,1,'L');
		$sexo = ($paciente['sexo'] == 'F') ? 'Femenino' : 'Masculino';
		$this->pdf->Cell(0,6,utf8_decode('Sexo: ' . $sexo ) ,0,1,'L');
		$this->pdf->Cell(0,6,utf8_decode('Edad: ' . devolverEdad($paciente['fecha_nacimiento']) ) ,0,1,'L');
		$this->pdf->Cell(0,6,utf8_decode('Talla: ' . $paciente['estatura'] . ' cm.' ) ,0,1,'L');

		$this->pdf->Ln(8);
		$this->pdf->SetFont('Arial','B',15);
		$this->pdf->Cell(17,6,utf8_decode('PESO: '  ) ,0,0,'L');
		$this->pdf->SetFont('Arial','BU',15);
		$this->pdf->Cell(10,6,utf8_decode($consulta['peso'] . 'KG.') ,0,0,'L');
	}
	private function imprimir_composicion_corporal($consulta, $posYCuadro){
		$this->pdf->Ln(5);
		$this->pdf->SetFont('Arial','B',11);		
		$this->pdf->Cell(10,6,utf8_decode('COMPOSICION CORPORAL') ,0,0,'L');
		$this->pdf->Image('assets/images/icons/cuerpo.png',8,$posYCuadro+11);
		$this->pdf->SetFont('Arial','',11);
		$posXporc = 48;
		$anchoPorc = (($this->pdf->GetPageWidth() - 8) / 2)-$posXporc;
		$this->pdf->Ln(12);
		$this->pdf->SetX($posXporc);
		$this->pdf->Cell($anchoPorc,6,utf8_decode('% GRASA CORPORAL: '  . $consulta['porc_masa_grasa'] . ' %') ,0,0,'L');
		$this->pdf->Ln(15);
		$this->pdf->SetX($posXporc);
		$this->pdf->Cell($anchoPorc,6,utf8_decode('% MASA MUSCULAR: '  . $consulta['porc_masa_muscular'] . ' %') ,0,0,'L');
		$this->pdf->Ln(15);
		$this->pdf->SetX($posXporc);
		$this->pdf->Cell($anchoPorc,6,utf8_decode('AGUA: '  . $consulta['porc_agua_corporal'] . ' %') ,0,0,'L');
	}
	private function imprimir_cuadro_detalle_peso($paciente, $consulta, $posYCuadro){
		$posXCuadro = (($this->pdf->GetPageWidth() - 16) / 2)+ 25;
		$anchoCuadro =(($this->pdf->GetPageWidth() - 16) / 2) -20;
		$anchoSubCuadro = $anchoCuadro -30;
		$this->pdf->SetFillColor(234,235,237);
		$this->pdf->SetDrawColor(150,155,165);
		$this->pdf->SetXY($posXCuadro , $posYCuadro);
		$posY = $posYCuadro;
		$this->pdf->SetFont('Arial','B',11);
		$this->pdf->Cell($anchoCuadro,11,utf8_decode('DETALLE DEL PESO') ,1,0,'C', TRUE);
		$this->pdf->SetFont('Arial','',11);
		$posY += 11;
		$this->pdf->SetXY($posXCuadro,$posY);
		$this->pdf->Cell($anchoSubCuadro,9,utf8_decode('   PESO IDEAL') ,1,0,'L', FALSE);
		$this->pdf->SetXY(($posXCuadro+$anchoCuadro-30), $posY);
		$pesoIdeal = round((0.75 * ((float)$paciente['estatura'] - 150)) + 50);
		$this->pdf->Cell(30,9,utf8_decode($pesoIdeal . ' kg.') ,1,0,'C', FALSE);

		$posY += 9;
		$this->pdf->SetXY($posXCuadro, $posY);
		$this->pdf->Cell($anchoSubCuadro,9,utf8_decode('   OBJETIVO') ,1,0,'L', FALSE);
		$this->pdf->SetXY(($posXCuadro+$anchoCuadro-30), $posY);
		$objetivo = round($pesoIdeal - (float)$consulta['peso']);
		$this->pdf->Cell(30,9,utf8_decode($objetivo . ' kg.') ,1,0,'C', FALSE);

		$posY += 9;
		//$this->pdf->SetFillColor(252,184,185);
		$this->pdf->SetXY($posXCuadro, $posY);
		$this->pdf->Cell($anchoSubCuadro,9,utf8_decode('   GRASA') ,1,0,'L', TRUE);
		$this->pdf->SetXY(($posXCuadro+$anchoCuadro-30), $posY);		
		$this->pdf->Cell(30,9,utf8_decode(@$consulta['grasa_para_objetivo'] . ' kg.') ,1,0,'C', TRUE);

		$posY += 9;
		$this->pdf->SetXY($posXCuadro, $posY);
		$this->pdf->Cell($anchoSubCuadro,9,utf8_decode('   MASA MUSCULAR') ,1,0,'L', TRUE);
		$this->pdf->SetXY(($posXCuadro+$anchoCuadro-30), $posY);		
		$this->pdf->Cell(30,9,utf8_decode(@$consulta['masa_muscular_objetivo'] . ' kg.') ,1,0,'C', TRUE);

		/*tipo cuerpo*/
		$posY += 12;
		$this->pdf->SetXY(($posXCuadro), $posY);
		$this->pdf->Cell($anchoCuadro,8,utf8_decode('TIPO DE CUERPO') ,0,0,'C', FALSE);
		$anchoImagen = $anchoCuadro;
		$anchoCuadro = $anchoCuadro - 33;
		$posY += 10;
		$posXCuadro += 5;
		$this->pdf->Image('assets/images/icons/manzana.png',$posXCuadro+6,$posY, $anchoCuadro/3);
		$this->pdf->Image('assets/images/icons/normal.png',$posXCuadro + ($anchoCuadro/3) + 12,$posY, $anchoCuadro/3);
		$this->pdf->Image('assets/images/icons/pera.png',$posXCuadro + ($anchoCuadro/3) + ($anchoCuadro/3) + 18,$posY-2, $anchoCuadro/3);
		$posYCheck = $posY+4;
		$this->pdf->Ln(30);
		$this->pdf->SetX($posXCuadro);
		$this->pdf->SetFont('Arial','',10);
		$this->pdf->Cell($anchoImagen/3,5,utf8_decode('MANZANA') ,0,0,'C',FALSE);
		$this->pdf->SetX($this->pdf->GetX()-4);
		$this->pdf->Cell($anchoImagen/3,5,utf8_decode('NORMAL') ,0,0,'C',FALSE);
		$this->pdf->SetX($this->pdf->GetX()-5);
		$this->pdf->Cell($anchoImagen/3,5,utf8_decode('PERA') ,0,0,'C',FALSE);
		$this->pdf->Ln();				
		$posY = $this->pdf->GetY();

		if(!empty($consulta['cm_cadera_gluteo'])){
			$icc = (float)$consulta['cm_cintura'] / (float)$consulta['cm_cadera_gluteo'];			
			if($paciente['sexo'] == 'F'){
				if($icc == 0.8){
					$tipoCuerpo = 2;
				}else if($icc < 0.8){
					$tipoCuerpo = 1;
				}else{
					$tipoCuerpo = 3;
				}
			}else if($paciente['sexo']=='M'){
				if($icc == 1){
					$tipoCuerpo = 2;
				}else if($icc < 1){
					$tipoCuerpo = 1;
				}else{
					$tipoCuerpo = 3;
				}
			}

			if($tipoCuerpo == 1){
				$posXCheck = $posXCuadro+11;
			}else if($tipoCuerpo == 2){
				$posXCheck = $posXCuadro + 6 +($anchoImagen/3);
			}else if($tipoCuerpo == 3){
				$posXCheck = $posXCuadro + ($anchoImagen/3*2);
			}
			$this->pdf->Image('assets/images/icons/check.png',$posXCheck, $posYCheck,6);
		}
		
		$this->pdf->SetY($posY);
	}
	private function imprimir_progreso($consulta, $consultaAnterior, $primeraConsulta, $paciente = FALSE){
		if($consulta['idatencion'] != $primeraConsulta['idatencion']){

			$this->pdf->Ln(10);
			$this->pdf->SetX(8);

			$anchoProgreso = (($this->pdf->GetPageWidth() - 16) / 2)+ 22;
			$this->pdf->SetFont('Arial','B',11);
			$this->pdf->Cell($anchoProgreso,6,utf8_decode('Progreso:') ,0,1,'L');
			$this->pdf->SetFont('Arial','',11);

			$posYprogreso = $this->pdf->GetY();
			$this->pdf->Image('assets/images/icons/star.png',9, null, 4);
			$this->pdf->SetXY(13, $posYprogreso);
			$pesoPerdido = round((float)$consultaAnterior['peso'] - (float)$consulta['peso'],2);
			$pesoTotalPerdido = round((float)$primeraConsulta['peso'] - (float)$consulta['peso'],2);
			$progreso = 'Ha perdido ' . $pesoPerdido . ' kg desde la ultima cita. (¡'. $pesoTotalPerdido .' kg en total!)';
			$this->pdf->Cell($anchoProgreso,6,utf8_decode($progreso) ,0,0,'L');

			$posYprogreso+=6;
			$this->pdf->Image('assets/images/icons/star.png',9, $posYprogreso, 4);
			$this->pdf->SetXY(13, $posYprogreso);
			$grasaPerdida = round((float)$consultaAnterior['kg_masa_grasa'] - (float)$consulta['kg_masa_grasa'],2);
			$progreso = 'Ha perdido ' . $grasaPerdida . ' kg de grasa gracias al tratamiento';
			$this->pdf->Cell($anchoProgreso,6,utf8_decode($progreso) ,0,0,'L');

			$posYprogreso+=6;
			$this->pdf->Image('assets/images/icons/star.png',9, $posYprogreso, 4);
			$this->pdf->SetXY(13, $posYprogreso);
			$grasaVisceralPerdida = round((float)$consultaAnterior['puntaje_grasa_visceral'] - (float)$consulta['puntaje_grasa_visceral'],2);
			$progreso = 'Ha reducido ' . $grasaVisceralPerdida . ' puntos de grasa visceral desde su ultima cita';
			$this->pdf->Cell($anchoProgreso,6,utf8_decode($progreso) ,0,0,'L');

			$posYprogreso+=6;
			$this->pdf->Image('assets/images/icons/corazon.png',9, $posYprogreso, 4);
			$this->pdf->SetXY(13, $posYprogreso); 
			if( strtoupper($paciente['sexo']) == 'F' ){
				if( $consulta['cm_cintura'] <= 80 && !empty($consulta['cm_cintura']) ){
					$progreso = 'No presenta riesgo cardiovascular'; 
				}elseif ( $consulta['cm_cintura'] > 80 ) {
					$progreso = 'Presenta riesgo cardiovascular'; 
				}else{
					$progreso = '-';
				}
			}elseif ( strtoupper($paciente['sexo']) == 'M' ) {
				if( $consulta['cm_cintura'] <= 90 && !empty($consulta['cm_cintura']) ){
					$progreso = 'No presenta riesgo cardiovascular'; 
				}elseif ( $consulta['cm_cintura'] > 90 ) {
					$progreso = '-'; 
				}else{
					
				}
			}
			$this->pdf->Cell($anchoProgreso,6,utf8_decode($progreso) ,0,0,'L');
		}
	}
	private function imprimir_barra_imc($paciente,$consulta, $anchoTotalBarras, $margen){
		$this->pdf->Ln(12);
		$this->pdf->SetFont('Arial','B',15);
		$this->pdf->Cell(17,6,utf8_decode('IMC: '  ) ,0,0,'L');
		$this->pdf->SetFont('Arial','',10);
		$anchoColor = $anchoTotalBarras / 6;

		$this->pdf->Ln(8);
		$imc = (float)$consulta['peso'] / (((float)$paciente['estatura']/100) * ((float)$paciente['estatura']/100));
		if($imc < 18.5){
			$posXFlechaIMC = ($anchoColor/2);
		}else if($imc >= 18.5 && $imc <= 24.9){
			$posXFlechaIMC = $anchoColor + ($anchoColor/2);
		}else if($imc >= 25 && $imc <= 29.9){
			$posXFlechaIMC = ($anchoColor * 2) + ($anchoColor/2);
		}else if($imc >= 30 && $imc <= 34.9){
			$posXFlechaIMC = ($anchoColor * 3) + ($anchoColor/2);
		}else if($imc >= 35 && $imc <= 39.9){
			$posXFlechaIMC = ($anchoColor * 4) + ($anchoColor/2);
		}else if($imc >= 40){
			$posXFlechaIMC = ($anchoColor * 5) + ($anchoColor/2);
		}	
		$this->pdf->Image('assets/images/dinamic/flechaAbajoRoja.png',$posXFlechaIMC+$margen-4);

		$this->pdf->Ln();		
		$this->pdf->SetXY($margen, $this->pdf->GetY()-4);
		$this->pdf->SetFillColor(58,111,255);
		$this->pdf->Cell($anchoColor,11,utf8_decode('Bajo peso') ,0,0,'C',TRUE);
		$this->pdf->SetFillColor(73,196,91);
		$this->pdf->Cell($anchoColor,11,utf8_decode('Bajo normal') ,0,0,'C',TRUE);
		$this->pdf->SetFillColor(255,253,67);
		$this->pdf->Cell($anchoColor,11,utf8_decode('Sobrepeso') ,0,0,'C',TRUE);
		$this->pdf->SetFillColor(255,152,91);
		$this->pdf->Cell($anchoColor,11,utf8_decode('Obesidad 1º') ,0,0,'C',TRUE);
		$this->pdf->SetFillColor(255,71,71);
		$this->pdf->Cell($anchoColor,11,utf8_decode('Obesidad 2º') ,0,0,'C',TRUE);
		$this->pdf->SetFillColor(214,50,53);
		$this->pdf->Cell($anchoColor,11,utf8_decode('Obesidad 3º') ,0,0,'C',TRUE);

		$this->pdf->Ln();
		$this->pdf->SetX($margen);
		$this->pdf->Cell($anchoColor,5,utf8_decode('< 18.5') ,0,0,'C',FALSE);
		$this->pdf->Cell($anchoColor,5,utf8_decode('18.5 a 24.9') ,0,0,'C',FALSE);
		$this->pdf->Cell($anchoColor,5,utf8_decode('25 a 29.9') ,0,0,'C',FALSE);
		$this->pdf->Cell($anchoColor,5,utf8_decode('30 a 34.9') ,0,0,'C',FALSE);
		$this->pdf->Cell($anchoColor,5,utf8_decode('35 a 39.9') ,0,0,'C',FALSE);
		$this->pdf->Cell($anchoColor,5,utf8_decode('> 40') ,0,0,'C',FALSE);
	}
	private function imprimir_barra_grasa($consulta, $anchoTotalBarras, $margen){
		$this->pdf->Ln(12);
		$this->pdf->SetFont('Arial','B',15);
		$this->pdf->Cell(17,6,utf8_decode('GRASA: '  ) ,0,0,'L');
		$this->pdf->SetFont('Arial','',10);
		$anchoPuntaje = $anchoTotalBarras / 12;

		if((float)$consulta['porc_masa_grasa']>59){
			$posXFlechaGrasa = $anchoPuntaje * 12;
		}else{
			$posXFlechaGrasa = round(((float)$consulta['porc_masa_grasa'] * ($anchoPuntaje * 12)) /60 );
		}		
		$this->pdf->Ln(6);
		$this->pdf->Image('assets/images/dinamic/flechaAbajoVerde.png',$posXFlechaGrasa+$margen);

		$this->pdf->Ln();		
		$this->pdf->SetXY($margen, $this->pdf->GetY()-4);
		$this->pdf->SetFillColor(58,111,255);
		$this->pdf->Cell($anchoPuntaje * 1.5,9,utf8_decode('Bajo') ,0,0,'C',TRUE);
		$this->pdf->SetFillColor(73,196,91);
		$this->pdf->Cell($anchoPuntaje * 2.5,9,utf8_decode('Saludable') ,0,0,'C',TRUE);
		$this->pdf->SetFillColor(255,253,67);
		$this->pdf->Cell($anchoPuntaje,9,utf8_decode('Alto') ,0,0,'C',TRUE);
		$this->pdf->SetFillColor(255,71,71);
		$this->pdf->Cell($anchoPuntaje * 7,9,utf8_decode('Obeso') ,0,0,'C',TRUE);

		$this->pdf->Ln();
		$this->pdf->SetX($margen);
		for($i = 0; $i<12; $i++){
			$this->pdf->Cell($anchoPuntaje,5,utf8_decode($i*5) ,0,0,'L',FALSE);
		}
	}
	private function imprimir_barra_grasa_visceral($consulta, $anchoTotalBarras, $margen){
		$this->pdf->Ln(12);
		$this->pdf->SetFont('Arial','B',15);
		$this->pdf->Cell(17,6,utf8_decode('GRASA VISCERAL: '  ) ,0,0,'L');
		$this->pdf->SetFont('Arial','',10);
		$anchoPuntaje = $anchoTotalBarras / 20;

		$posXFlechaGrasa = round(((float)$consulta['puntaje_grasa_visceral'] * ($anchoPuntaje * 20)) /20 );				
		$this->pdf->Ln(6);
		$this->pdf->Image('assets/images/dinamic/flechaAbajoVerde.png',$posXFlechaGrasa+$margen);

		$this->pdf->Ln();
		$this->pdf->SetXY($margen, $this->pdf->GetY()-4);
		$this->pdf->SetFillColor(73,196,91);
		$this->pdf->Cell($anchoPuntaje * 10,9,utf8_decode('Normal') ,0,0,'C',TRUE);
		$this->pdf->SetFillColor(255,253,67);
		$this->pdf->Cell($anchoPuntaje*5,9,utf8_decode('Alto') ,0,0,'C',TRUE);
		$this->pdf->SetFillColor(255,71,71);
		$this->pdf->Cell($anchoPuntaje *5,9,utf8_decode('Muy Alto') ,0,0,'C',TRUE);

		$this->pdf->Ln();
		$this->pdf->SetX($margen);
		for($i = 0; $i<20; $i++){
			$this->pdf->Cell($anchoPuntaje,5,utf8_decode($i) ,0,0,'L',FALSE);
		}
	}
	function YAxisFormat($value) { return(round($value,2)); } 

	private function imprimir_grafico_peso($dataX, $dataY, $margen){
		//genero el grafico
		$myData = new pData(); 
		$myData->addPoints($dataY,"Values");
		$myData->setSerieWeight("Values",2);

		$myData->addPoints($dataX,"Timestamp");
		$myData->setSerieDescription("Timestamp","Sampled Dates");
		$myData->setAbscissa("Timestamp");
		$myData->setXAxisDisplay(AXIS_FORMAT_DATE);

		$myPicture = new pImage(600,400,$myData);
		$myPicture->setFontProperties(array(
			"FontName"=>"application/libraries/pchart/fonts/verdana.ttf",
			"FontSize"=>11)
		);	
		$myPicture->setGraphArea(60,40,580,370);
		$myPicture->drawRectangle(15,1,586,399,array("R"=>150,"G"=>155,"B"=>165));
		$myPicture->drawScale();
		//$myPicture->drawSplineChart();
		$myPicture->drawLineChart();
		$myPicture->drawPlotChart(array("DisplayValues"=>TRUE,"PlotBorder"=>TRUE,"BorderSize"=>2,"Surrounding"=>-60,"BorderAlpha"=>80));

		$timestamp = date('YmdHis');
		$nombre = "assets/images/dinamic/pdfTemporales/imagePeso". $timestamp .".png";
		$myPicture->render($nombre);

		//inserto grafico a pdf
		$anchoGrafico = ($this->pdf->GetPageWidth() - ($margen*2))/2;
		$this->pdf->SetFont('Arial','B',15);
		$this->pdf->SetXY($margen, 8);
		$this->pdf->Cell($anchoGrafico,6,utf8_decode('PESO: '  ) ,0,1,'L');
		$this->pdf->Image($nombre,$margen-1,null,$anchoGrafico);
	}
	private function imprimir_grafico_imc($dataX, $dataY, $margen){
		//genero el grafico
		$myData = new pData(); 
		$myData->addPoints($dataY,"Values");
		$myData->setSerieWeight("Values",2);
		$myData->setAxisDisplay(0,AXIS_FORMAT_CUSTOM,"YAxisFormat");  

		$myData->addPoints($dataX,"Timestamp");
		$myData->setSerieDescription("Timestamp","Sampled Dates");
		$myData->setAbscissa("Timestamp");
		$myData->setXAxisDisplay(AXIS_FORMAT_DATE);

		$myPicture = new pImage(600,400,$myData);
		$myPicture->setFontProperties(array(
			"FontName"=>"application/libraries/pchart/fonts/verdana.ttf",
			"FontSize"=>11)
		);	
		$myPicture->setGraphArea(60,40,580,370);
		$myPicture->drawRectangle(11,1,587,399,array("R"=>150,"G"=>155,"B"=>165));
		$scaleProperties = array( array(	"Min"=>16, 
											"Max"=>50)
									);
		$myPicture->drawScale(array("Mode" => SCALE_MODE_MANUAL, "ManualScale"=>$scaleProperties));
		//$myPicture->drawSplineChart();
		$myPicture->drawLineChart();
		$myPicture->drawPlotChart(array("DisplayValues"=>TRUE,"PlotBorder"=>TRUE,"BorderSize"=>2,"Surrounding"=>-60,"BorderAlpha"=>80));

		$timestamp = date('YmdHis');
		$nombre = "assets/images/dinamic/pdfTemporales/imageImc". $timestamp .".png";
		$myPicture->render($nombre);

		//inserto grafico a pdf
		$anchoGrafico = ($this->pdf->GetPageWidth() - ($margen*2))/2;
		$this->pdf->SetFont('Arial','B',15);
		$this->pdf->SetXY($margen + $anchoGrafico, 8);
		$this->pdf->Cell($anchoGrafico,6,utf8_decode('IMC: '  ) ,0,1,'L');
		$this->pdf->Image($nombre,$margen + $anchoGrafico,null,$anchoGrafico);
	}
	private function imprimir_grafico_grasa_corporal($dataX, $dataY, $margen){
		//genero el grafico
		$myData = new pData(); 
		$myData->addPoints($dataY,"Values");
		$myData->setSerieWeight("Values",2);		
		$myData->setAxisDisplay(0,AXIS_FORMAT_CUSTOM); 
		$myData->setAxisUnit(0,"%");

		$myData->addPoints($dataX,"Timestamp");
		$myData->setSerieDescription("Timestamp","Sampled Dates");
		$myData->setAbscissa("Timestamp");
		$myData->setXAxisDisplay(AXIS_FORMAT_DATE);

		$myPicture = new pImage(600,400,$myData);
		$myPicture->setFontProperties(array(
			"FontName"=>"application/libraries/pchart/fonts/verdana.ttf",
			"FontSize"=>11)
		);	
		$myPicture->setGraphArea(60,40,580,370);
		$myPicture->drawRectangle(15,1,586,399,array("R"=>150,"G"=>155,"B"=>165));
		$myPicture->drawScale();
		//$myPicture->drawSplineChart();
		$myPicture->drawLineChart();
		$myPicture->drawPlotChart(array("DisplayValues"=>TRUE,"PlotBorder"=>TRUE,"BorderSize"=>2,"Surrounding"=>-60,"BorderAlpha"=>80));

		$timestamp = date('YmdHis');
		$nombre = "assets/images/dinamic/pdfTemporales/imageGrasa". $timestamp .".png";
		$myPicture->render($nombre);

		//inserto grafico a pdf
		$anchoGrafico = ($this->pdf->GetPageWidth() - ($margen*2))/2;
		$this->pdf->SetFont('Arial','B',15);
		$this->pdf->Ln(5);
		$this->pdf->SetX($margen);
		$this->pdf->Cell($anchoGrafico,6,utf8_decode('% GRASA CORPORAL: '  ) ,0,1,'L');
		$this->pdf->Image($nombre,$margen-1,null,$anchoGrafico);
	}
	private function imprimir_grafico_masa_muscular($dataX, $dataY, $margen){
		//genero el grafico
		$myData = new pData(); 
		$myData->addPoints($dataY,"Values");
		$myData->setSerieWeight("Values",2);
		$myData->setAxisDisplay(0,AXIS_FORMAT_CUSTOM); 
		$myData->setAxisUnit(0,"%"); 

		$myData->addPoints($dataX,"Timestamp");
		$myData->setSerieDescription("Timestamp","Sampled Dates");
		$myData->setAbscissa("Timestamp");
		$myData->setXAxisDisplay(AXIS_FORMAT_DATE);

		$myPicture = new pImage(600,400,$myData);
		$myPicture->setFontProperties(array(
			"FontName"=>"application/libraries/pchart/fonts/verdana.ttf",
			"FontSize"=>11)
		);	
		$myPicture->setGraphArea(60,40,580,370);
		$myPicture->drawRectangle(11,1,587,399,array("R"=>150,"G"=>155,"B"=>165));
		$myPicture->drawScale();
		//$myPicture->drawSplineChart();
		$myPicture->drawLineChart();
		$myPicture->drawPlotChart(array("DisplayValues"=>TRUE,"PlotBorder"=>TRUE,"BorderSize"=>2,"Surrounding"=>-60,"BorderAlpha"=>80));

		$timestamp = date('YmdHis');
		$nombre = "assets/images/dinamic/pdfTemporales/imageMasa". $timestamp .".png";
		$myPicture->render($nombre);

		//inserto grafico a pdf
		$anchoGrafico = ($this->pdf->GetPageWidth() - ($margen*2))/2;
		$this->pdf->SetFont('Arial','B',15);
		$this->pdf->SetXY($margen+$anchoGrafico, $this->pdf->GetY()-72);
		$this->pdf->Cell($anchoGrafico,6,utf8_decode('% MASA MUSCULAR: '  ) ,0,1,'L');
		$this->pdf->Image($nombre,$margen+$anchoGrafico,null,$anchoGrafico);
	}
	private function imprimir_datos_finales($consulta, $margen, $configuracion){
		$posYRectangulos = ($this->pdf->GetPageHeight()/2) + 15;
		$anchoColumnas = ($this->pdf->GetPageWidth()-16)/3;
		$anchoObsevaciones = ($anchoColumnas * 2) - 5;
		$anchoProxCita =  $anchoColumnas;
		$altoColumnas = ($this->pdf->GetPageHeight()/2) - 60;

		//observaciones
		$this->pdf->Rect($margen, $posYRectangulos, $anchoObsevaciones, $altoColumnas, 'D');
		$this->pdf->SetFont('Arial','B',15);
		$this->pdf->SetXY($margen+5, $posYRectangulos+5);
		$this->pdf->Cell($anchoObsevaciones-10,6,utf8_decode('OBSERVACIONES: '  ) ,0,1,'L');
		$this->pdf->SetFont('Arial','I',10);
		$this->pdf->SetXY($margen+5, $posYRectangulos+5+7);
		$this->pdf->MultiCell($anchoObsevaciones-10,5,utf8_decode('* ' .$consulta['diagnostico_notas']) ,0,'L');

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
		$this->pdf->SetY($posYRectangulos + $altoColumnas+10);
		$this->pdf->SetFont('Arial','',14);
		$profesional = 'Lic. ' . ucwords(strtolower_total(utf8_decode($consulta['nombre'] . ' ' . $consulta['apellidos'] )));
		$this->pdf->MultiCell(0,7,$profesional,0,'L',FALSE);
		$this->pdf->Cell(0,7,'CNP: ' . $consulta['num_colegiatura'],0,1,'L',FALSE);
	}
}
