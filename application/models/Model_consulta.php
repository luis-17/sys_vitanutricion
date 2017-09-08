<?php
class Model_consulta extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}

	public function m_registrar($datos){
		$data = array(
			'idcliente' 			=> $datos['cita']['cliente']['idcliente'],
			'idcita' 				=> $datos['cita']['id'],
			'peso' 					=> $datos['consulta']['peso'],
			'porc_masa_grasa' 		=> $datos['consulta']['porc_masa_grasa'],
			'porc_masa_libre' 		=> $datos['consulta']['porc_masa_libre'],
			'porc_masa_muscular' 	=> $datos['consulta']['porc_masa_muscular'],
			'kg_masa_muscular' 		=> $datos['consulta']['kg_masa_muscular'],
			'porc_agua_corporal' 	=> empty($datos['consulta']['porc_agua_corporal']) ? NULL : $datos['consulta']['porc_agua_corporal'],
			'kg_agua_corporal' 		=> empty($datos['consulta']['kg_agua_corporal']) ? NULL : $datos['consulta']['kg_agua_corporal'],
			'puntaje_grasa_visceral'=> $datos['consulta']['puntaje_grasa_visceral'],
			'kg_masa_grasa' 		=> $datos['consulta']['kg_masa_grasa'],
			'kg_masa_libre' 		=> $datos['consulta']['kg_masa_libre'],
			/*'porc_grasa_visceral' 	=> $datos['consulta']['porc_grasa_visceral'],
			'kg_grasa_visceral' 	=> $datos['consulta']['kg_grasa_visceral'],*/
			'cm_pecho' 				=> (empty($datos['consulta']['cm_pecho'])) ? NULL : $datos['consulta']['cm_pecho'],
			'cm_antebrazo' 			=> (empty($datos['consulta']['cm_antebrazo'])) ? NULL : $datos['consulta']['cm_antebrazo'],
			'cm_cintura' 			=> (empty($datos['consulta']['cm_cintura'])) ? NULL : $datos['consulta']['cm_cintura'],
			'cm_abdomen' 			=> (empty($datos['consulta']['cm_abdomen'])) ? NULL : $datos['consulta']['cm_abdomen'],
			'cm_cadera_gluteo' 		=> (empty($datos['consulta']['cm_cadera_gluteo'])) ? NULL : $datos['consulta']['cm_cadera_gluteo'],
			'cm_muslo' 				=> (empty($datos['consulta']['cm_muslo'])) ? NULL : $datos['consulta']['cm_muslo'],
			'cm_hombros' 			=> (empty($datos['consulta']['cm_hombros'])) ? NULL : $datos['consulta']['cm_hombros'],
			'cm_biceps_relajados' 	=> (empty($datos['consulta']['cm_biceps_relajados'])) ? NULL : $datos['consulta']['cm_biceps_relajados'],
			'cm_biceps_contraidos' 	=> (empty($datos['consulta']['cm_biceps_contraidos'])) ? NULL : $datos['consulta']['cm_biceps_contraidos'],
			'cm_muneca' 			=> (empty($datos['consulta']['cm_muneca'])) ? NULL : $datos['consulta']['cm_muneca'],
			'cm_rodilla' 			=> (empty($datos['consulta']['cm_rodilla'])) ? NULL : $datos['consulta']['cm_rodilla'],
			'cm_gemelos' 			=> (empty($datos['consulta']['cm_gemelos'])) ? NULL : $datos['consulta']['cm_gemelos'],
			'cm_tobillo' 			=> (empty($datos['consulta']['cm_tobillo'])) ? NULL : $datos['consulta']['cm_tobillo'],
			'cm_tricipital' 		=> (empty($datos['consulta']['cm_tricipital'])) ? NULL : $datos['consulta']['cm_tricipital'],
			'cm_bicipital' 			=> (empty($datos['consulta']['cm_bicipital'])) ? NULL : $datos['consulta']['cm_bicipital'],
			'cm_subescapular' 		=> (empty($datos['consulta']['cm_subescapular'])) ? NULL : $datos['consulta']['cm_subescapular'],
			'cm_axilar' 			=> (empty($datos['consulta']['cm_axilar'])) ? NULL : $datos['consulta']['cm_axilar'],
			'cm_pectoral' 			=> (empty($datos['consulta']['cm_pectoral'])) ? NULL : $datos['consulta']['cm_pectoral'],
			'cm_suprailiaco' 		=> (empty($datos['consulta']['cm_suprailiaco'])) ? NULL : $datos['consulta']['cm_suprailiaco'],
			'cm_supraespinal' 		=> (empty($datos['consulta']['cm_supraespinal'])) ? NULL : $datos['consulta']['cm_supraespinal'],
			'cm_abdominal' 			=> (empty($datos['consulta']['cm_abdominal'])) ? NULL : $datos['consulta']['cm_abdominal'],
			'cm_pierna' 			=> (empty($datos['consulta']['cm_pierna'])) ? NULL : $datos['consulta']['cm_pierna'],
			'si_embarazo' 			=> ($datos['consulta']['si_embarazo']) ? 1 : 2,
			'diagnostico_notas' 	=> (empty($datos['consulta']['diagnostico_notas'])) ? NULL : $datos['consulta']['diagnostico_notas'],
			'resultados_laboratorio'=> (empty($datos['consulta']['resultados_laboratorio'])) ? NULL : $datos['consulta']['resultados_laboratorio'],
			'fecha_atencion' 		=> date('Y-m-d H:i:s', strtotime($datos['consulta']['fecha_atencion'])),
			// OBJETIVO
			'grasa_para_objetivo' 	=> (empty($datos['consulta']['grasa_para_objetivo'])) ? NULL : $datos['consulta']['grasa_para_objetivo'],
			'masa_muscular_objetivo'=> (empty($datos['consulta']['masa_muscular_para_objetivo'])) ? NULL : $datos['consulta']['masa_muscular_para_objetivo'],
			// AUDITORIA 
			'createdat' 			=> date('Y-m-d H:i:s'),
			'updatedat'				=> date('Y-m-d H:i:s')
			);
		return $this->db->insert('atencion' , $data);
	}

	public function m_actualizar($datos){
		$data = array(
			'peso' 					=> $datos['consulta']['peso'],
			'porc_masa_grasa' 		=> $datos['consulta']['porc_masa_grasa'],
			'porc_masa_libre' 		=> $datos['consulta']['porc_masa_libre'],
			'porc_masa_muscular' 	=> $datos['consulta']['porc_masa_muscular'],
			'kg_masa_muscular' 		=> $datos['consulta']['kg_masa_muscular'],
			'porc_agua_corporal' 	=> empty($datos['consulta']['porc_agua_corporal']) ? NULL : $datos['consulta']['porc_agua_corporal'],
			'kg_agua_corporal' 		=> empty($datos['consulta']['kg_agua_corporal']) ? NULL : $datos['consulta']['kg_agua_corporal'],
			'puntaje_grasa_visceral'=> $datos['consulta']['puntaje_grasa_visceral'],
			'kg_masa_grasa' 		=> $datos['consulta']['kg_masa_grasa'],
			'kg_masa_libre' 		=> $datos['consulta']['kg_masa_libre'],
			/*'porc_grasa_visceral' 	=> $datos['consulta']['porc_grasa_visceral'],
			'kg_grasa_visceral' 	=> $datos['consulta']['kg_grasa_visceral'],*/
			'cm_pecho' 				=> (empty($datos['consulta']['cm_pecho'])) ? NULL : $datos['consulta']['cm_pecho'],
			'cm_antebrazo' 			=> (empty($datos['consulta']['cm_antebrazo'])) ? NULL : $datos['consulta']['cm_antebrazo'],
			'cm_cintura' 			=> (empty($datos['consulta']['cm_cintura'])) ? NULL : $datos['consulta']['cm_cintura'],
			'cm_abdomen' 			=> (empty($datos['consulta']['cm_abdomen'])) ? NULL : $datos['consulta']['cm_abdomen'],
			'cm_cadera_gluteo' 		=> (empty($datos['consulta']['cm_cadera_gluteo'])) ? NULL : $datos['consulta']['cm_cadera_gluteo'],
			'cm_muslo' 				=> (empty($datos['consulta']['cm_muslo'])) ? NULL : $datos['consulta']['cm_muslo'],
			'cm_hombros' 			=> (empty($datos['consulta']['cm_hombros'])) ? NULL : $datos['consulta']['cm_hombros'],
			'cm_biceps_relajados' 	=> (empty($datos['consulta']['cm_biceps_relajados'])) ? NULL : $datos['consulta']['cm_biceps_relajados'],
			'cm_biceps_contraidos' 	=> (empty($datos['consulta']['cm_biceps_contraidos'])) ? NULL : $datos['consulta']['cm_biceps_contraidos'],
			'cm_muneca' 			=> (empty($datos['consulta']['cm_muneca'])) ? NULL : $datos['consulta']['cm_muneca'],
			'cm_rodilla' 			=> (empty($datos['consulta']['cm_rodilla'])) ? NULL : $datos['consulta']['cm_rodilla'],
			'cm_gemelos' 			=> (empty($datos['consulta']['cm_gemelos'])) ? NULL : $datos['consulta']['cm_gemelos'],
			'cm_tobillo' 			=> (empty($datos['consulta']['cm_tobillo'])) ? NULL : $datos['consulta']['cm_tobillo'],
			'cm_tricipital' 		=> (empty($datos['consulta']['cm_tricipital'])) ? NULL : $datos['consulta']['cm_tricipital'],
			'cm_bicipital' 			=> (empty($datos['consulta']['cm_bicipital'])) ? NULL : $datos['consulta']['cm_bicipital'],
			'cm_subescapular' 		=> (empty($datos['consulta']['cm_subescapular'])) ? NULL : $datos['consulta']['cm_subescapular'],
			'cm_axilar' 			=> (empty($datos['consulta']['cm_axilar'])) ? NULL : $datos['consulta']['cm_axilar'],
			'cm_pectoral' 			=> (empty($datos['consulta']['cm_pectoral'])) ? NULL : $datos['consulta']['cm_pectoral'],
			'cm_suprailiaco' 		=> (empty($datos['consulta']['cm_suprailiaco'])) ? NULL : $datos['consulta']['cm_suprailiaco'],
			'cm_supraespinal' 		=> (empty($datos['consulta']['cm_supraespinal'])) ? NULL : $datos['consulta']['cm_supraespinal'],
			'cm_abdominal' 			=> (empty($datos['consulta']['cm_abdominal'])) ? NULL : $datos['consulta']['cm_abdominal'],
			'cm_pierna' 			=> (empty($datos['consulta']['cm_pierna'])) ? NULL : $datos['consulta']['cm_pierna'],
			'si_embarazo' 			=> ($datos['consulta']['si_embarazo']) ? 1 : 2,
			'diagnostico_notas' 	=> (empty($datos['consulta']['diagnostico_notas'])) ? NULL : $datos['consulta']['diagnostico_notas'],
			'resultados_laboratorio'=> (empty($datos['consulta']['resultados_laboratorio'])) ? NULL : $datos['consulta']['resultados_laboratorio'],
			'fecha_atencion' 		=> date('Y-m-d H:i:s', strtotime($datos['consulta']['fecha_atencion'])),
			// OBJETIVO
			'grasa_para_objetivo' 	=> (empty($datos['consulta']['grasa_para_objetivo'])) ? NULL : $datos['consulta']['grasa_para_objetivo'],
			'masa_muscular_objetivo'=> (empty($datos['consulta']['masa_muscular_para_objetivo'])) ? NULL : $datos['consulta']['masa_muscular_para_objetivo'],
			// AUDITORIA 
			'updatedat'				=> date('Y-m-d H:i:s')
			);
		$this->db->where('idatencion',$datos['consulta']['idatencion']);
		return $this->db->update('atencion' , $data);
	}

	public function m_anular($id){
		$data = array(
			'estado_atencion' => 0,
			'updatedat' => date('Y-m-d H:i:s')
		);
		$this->db->where('idatencion', $id);
		return $this->db->update('atencion', $data);
	}

	public function m_consultar_atencion($idatencion){
		$this->db->select('at.idatencion, at.idcliente, at.idcita, at.peso, at.porc_masa_grasa, at.porc_masa_libre, at.porc_masa_muscular,
			at.kg_masa_muscular, at.porc_agua_corporal, at.kg_agua_corporal, at.porc_grasa_visceral, at.kg_grasa_visceral, at.cm_pecho,
			at.cm_antebrazo, at.cm_cintura, at.cm_abdomen, at.cm_cadera_gluteo, at.cm_muslo, at.cm_hombros, at.cm_biceps_relajados,
			at.cm_biceps_contraidos, at.cm_muneca, at.cm_rodilla, at.cm_gemelos, at.cm_tobillo, at.cm_tricipital, at.cm_bicipital,
			at.cm_subescapular, at.cm_axilar, at.cm_pectoral, at.cm_suprailiaco, at.cm_supraespinal, at.cm_abdominal, at.cm_pierna,
			at.si_embarazo, at.diagnostico_notas, at.estado_atencion, at.fecha_atencion, at.puntaje_grasa_visceral, at.kg_masa_grasa, 
			at.masa_muscular_objetivo, at.grasa_para_objetivo, at.kg_masa_libre, at.resultados_laboratorio, at.indicaciones_dieta, at.tipo_dieta');
		$this->db->select('at.idproxcita, c.fecha as prox_cita');
		$this->db->select("pr.nombre, pr.apellidos, pr.num_colegiatura", FALSE);

		$this->db->from('atencion at');
		$this->db->join('cita c', 'c.idcita = at.idproxcita', 'left');
		$this->db->join('cita cc', 'cc.idcita = at.idcita');
		$this->db->join('profesional pr', 'cc.idprofesional = pr.idprofesional', 'left');
		$this->db->where('at.estado_atencion', 1);
		$this->db->where('at.idatencion', (int)$idatencion);
		return $this->db->get()->row_array();
	}
	public function m_cargar_ultima_atencion($idcliente){
		$this->db->select('at.idatencion, at.idcliente, at.idcita, at.peso, at.porc_masa_grasa, at.porc_masa_libre, at.porc_masa_muscular,
			at.kg_masa_muscular, at.porc_agua_corporal, at.kg_agua_corporal, at.porc_grasa_visceral, at.kg_grasa_visceral, at.cm_pecho,
			at.cm_antebrazo, at.cm_cintura, at.cm_abdomen, at.cm_cadera_gluteo, at.cm_muslo, at.cm_hombros, at.cm_biceps_relajados,
			at.cm_biceps_contraidos, at.cm_muneca, at.cm_rodilla, at.cm_gemelos, at.cm_tobillo, at.cm_tricipital, at.cm_bicipital,
			at.cm_subescapular, at.cm_axilar, at.cm_pectoral, at.cm_suprailiaco, at.cm_supraespinal, at.cm_abdominal, at.cm_pierna,
			at.si_embarazo, at.diagnostico_notas, at.estado_atencion, at.fecha_atencion, at.kg_masa_grasa, at.puntaje_grasa_visceral');

		$this->db->from('atencion at');
		$this->db->where('at.estado_atencion', 1);
		$this->db->where('at.idcliente', $idcliente);
		$this->db->order_by('fecha_atencion', 'DESC');
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}

	public function m_cargar_atencion_anterior($idcliente, $fecha_atencion){
		$this->db->select('at.idatencion, at.idcliente, at.idcita, at.peso, at.porc_masa_grasa, at.porc_masa_libre, at.porc_masa_muscular,
			at.kg_masa_muscular, at.porc_agua_corporal, at.kg_agua_corporal, at.porc_grasa_visceral, at.kg_grasa_visceral, at.cm_pecho,
			at.cm_antebrazo, at.cm_cintura, at.cm_abdomen, at.cm_cadera_gluteo, at.cm_muslo, at.cm_hombros, at.cm_biceps_relajados,
			at.cm_biceps_contraidos, at.cm_muneca, at.cm_rodilla, at.cm_gemelos, at.cm_tobillo, at.cm_tricipital, at.cm_bicipital,
			at.cm_subescapular, at.cm_axilar, at.cm_pectoral, at.cm_suprailiaco, at.cm_supraespinal, at.cm_abdominal, at.cm_pierna,
			at.si_embarazo, at.diagnostico_notas, at.estado_atencion, at.fecha_atencion, at.kg_masa_grasa, at.puntaje_grasa_visceral');

		$this->db->from('atencion at');
		$this->db->where('at.estado_atencion', 1);
		$this->db->where('at.idcliente', $idcliente);
		$this->db->where('fecha_atencion <', $fecha_atencion);
		$this->db->order_by('fecha_atencion', 'DESC');
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}

	public function m_cargar_primera_atencion($idcliente){
		$this->db->select('at.idatencion, at.idcliente, at.idcita, at.peso, at.porc_masa_grasa, at.porc_masa_libre, at.porc_masa_muscular,
			at.kg_masa_muscular, at.porc_agua_corporal, at.kg_agua_corporal, at.porc_grasa_visceral, at.kg_grasa_visceral, at.cm_pecho,
			at.cm_antebrazo, at.cm_cintura, at.cm_abdomen, at.cm_cadera_gluteo, at.cm_muslo, at.cm_hombros, at.cm_biceps_relajados,
			at.cm_biceps_contraidos, at.cm_muneca, at.cm_rodilla, at.cm_gemelos, at.cm_tobillo, at.cm_tricipital, at.cm_bicipital,
			at.cm_subescapular, at.cm_axilar, at.cm_pectoral, at.cm_suprailiaco, at.cm_supraespinal, at.cm_abdominal, at.cm_pierna,
			at.si_embarazo, at.diagnostico_notas, at.estado_atencion, at.fecha_atencion,at.kg_masa_grasa, at.puntaje_grasa_visceral');

		$this->db->from('atencion at');
		$this->db->where('at.estado_atencion', 1);
		$this->db->where('at.idcliente', $idcliente);
		$this->db->order_by('fecha_atencion', 'ASC');
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}

	public function m_cargar_atenciones_paciente($idcliente, $todas = FALSE, $ordenAsc = FALSE, $fecha_maxima  = FALSE){
		$this->db->select('at.idatencion, at.idcliente, at.idcita, at.fecha_atencion,
			at.peso, at.porc_masa_grasa, at.porc_masa_libre,
			at.porc_masa_muscular, at.kg_masa_muscular,
			at.porc_agua_corporal, at.kg_agua_corporal,
			at.porc_grasa_visceral, at.kg_grasa_visceral,
			at.cm_pecho, at.cm_antebrazo, at.cm_cintura, at.cm_abdomen, at.cm_cadera_gluteo,
			at.cm_muslo, at.cm_hombros, at.cm_biceps_relajados,	at.cm_biceps_contraidos,
			at.cm_muneca, at.cm_rodilla, at.cm_gemelos, at.cm_tobillo, at.cm_tricipital,
			at.cm_bicipital,at.cm_subescapular, at.cm_axilar, at.cm_pectoral, at.cm_suprailiaco,
			at.cm_supraespinal, at.cm_abdominal, at.cm_pierna,
			at.si_embarazo, at.diagnostico_notas, at.estado_atencion, at.kg_masa_grasa, at.puntaje_grasa_visceral');

		$this->db->from('atencion at');
		$this->db->where('at.estado_atencion', 1);
		$this->db->where('at.idcliente', $idcliente);

		if($fecha_maxima){
			$this->db->where('at.fecha_atencion <=',$fecha_maxima);
		}

		if($ordenAsc){
			$this->db->order_by('fecha_atencion', 'ASC');
		}else{
			$this->db->order_by('fecha_atencion', 'DESC');
		}
		
		if(!$todas)
			$this->db->limit(4);
		return $this->db->get()->result_array();
	}

	public function m_act_fecha_atencion($datos){
		$data = array(
			'fecha_atencion' => $datos['fecha'],
			'updatedat' => date('Y-m-d H:i:s')
		);
		$this->db->where('idatencion', $datos['idatencion']);
		return $this->db->update('atencion', $data);

	}

	public function m_act_idproxcita($idatencion, $idcita){
		$data = array(
			'idproxcita' => $idcita,
			'updatedat' => date('Y-m-d H:i:s')
		);
		$this->db->where('idatencion', $idatencion);
		return $this->db->update('atencion',$data);
	}

	public function m_actualizar_desde_plan($datos){
		$data = array(
			'indicaciones_dieta' => (empty($datos['indicaciones_dieta'])) ? NULL : $datos['indicaciones_dieta'],
			'tipo_dieta' => $datos['tipo_dieta'],
			'updatedat' => date('Y-m-d H:i:s')
		);
		$this->db->where('idatencion', $datos['idatencion']);
		return $this->db->update('atencion',$data);
	}
}
?>