<?php
class Model_plan_alimentario extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}

	public function m_cargar_plan_alimentario($datos){
		$this->db->select('t.idturno, t.descripcion_tu',FALSE);
		$this->db->select('d.iddia, d.nombre_dia',FALSE);
		$this->db->select('adt.idatencion, adt.idatenciondietaturno, adt.hora, adt.indicaciones',FALSE);		
		$this->db->select('ada.idatenciondietaalim, ada.valor, a.idalimento',FALSE);
		$this->db->select('adaa.idatenciondietaalimalter, aa.idalimento as idalimento_alter',FALSE);
		$this->db->select("a.nombre,a.calorias,a.proteinas,a.grasas, a.carbohidratos, a.medida_casera, a.gramo, a.ceniza, a.calcio, a.fosforo, a.zinc, a.hierro, a.fibra ");
		$this->db->select("aa.nombre as nombre_alter,aa.calorias as calorias_alter,aa.proteinas as proteinas_alter,
						   aa.grasas as grasas_alter,aa.carbohidratos as carbohidratos_alter, aa.medida_casera as medida_casera_alter,
						   aa.gramo as gramo_alter, aa.ceniza as ceniza_alter, aa.calcio as calcio_alter, aa.fosforo as fosforo_alter, 
						   aa.zinc as zinc_alter, aa.hierro as hierro_alter, aa.fibra as fibra_alter");

		
		$this->db->from('turno t, dia d'); 
		$this->db->join('atencion_dieta_turno adt', 't.idturno = adt.idturno and (d.iddia = adt.iddia or adt.iddia is null) and adt.estado_dt = 1 and adt.idatencion = '.$datos['idatencion'],'left');
		$this->db->join('atencion_dieta_alim ada', 'ada.idatenciondietaturno = adt.idatenciondietaturno and ada.estado_ada = 1','left');
		$this->db->join('alimento a', 'a.idalimento = ada.idalimento','left');
		$this->db->join('atencion_dieta_alim_alter adaa', 'ada.idatenciondietaalim = adaa.idatenciondietaalim and adaa.estado_chta = 1','left');
		$this->db->join('alimento aa', 'aa.idalimento = adaa.idalimento','left');
		$this->db->where('t.estado_tu',1);
		$this->db->order_by('d.iddia ASC, t.idturno ASC');
		return $this->db->get()->result_array();
	}
 	
 	public function m_registrar_dieta_turno($datos){
 		$data = array(
 			'idatencion' => $datos['idatencion'],
 			'idturno' => $datos['idturno'],
 			'iddia' => empty($datos['iddia']) ? NULL : $datos['iddia'],
 			'hora' => $datos['hora'],
 			'indicaciones' => $datos['indicaciones'],
 		);

 		return $this->db->insert('atencion_dieta_turno',$data);
 	}

 	public function m_registrar_dieta_turno_alimento($datos){
 		$data = array(
 			'idatenciondietaturno' => $datos['idatenciondietaturno'],
 			'iddia' => empty($datos['iddia']) ? NULL : $datos['iddia'],
 			'idalimento' => $datos['idalimento'],
 			'valor' => $datos['valor'],
 		);

 		return $this->db->insert('atencion_dieta_alim',$data);
 	} 	
 	public function m_registrar_dieta_turno_alimento_alt($datos){
 		$data = array(
 			'idatenciondietaalim' => $datos['idatenciondietaalim'],
 			'idalimento' => $datos['idalimento'],
 		);

 		return $this->db->insert('atencion_dieta_alim_alter',$data);
 	}

 	public function m_anular_todo_dieta_turno($datos){
 		$data = array(
 			'estado_dt' => 0
 		);
 		$this->db->where('idatencion', $datos['idatencion']);
 		return $this->db->update('atencion_dieta_turno',$data);
 	}

 	public function m_anular_todo_dieta_alimento($datos){
 		$data = array(
 			'estado_ada' => 0
 		);
 		$this->db->where('idatenciondietaturno', $datos['idatenciondietaturno']);
 		return $this->db->update('atencion_dieta_alim',$data);
 	}

 	public function m_anular_todo_dieta_alimento_alter($datos){
 		$data = array(
 			'estado_chta' => 0
 		);
 		$this->db->where('idatenciondietaalim', $datos['idatenciondietaalim']);
 		return $this->db->update('atencion_dieta_alim_alter',$data);
 	}

 	public function m_actualizar_dieta_turno($datos){
 		$data = array(
 			'estado_dt' => 1,
 			'idturno' => $datos['idturno'],
 			'iddia' => empty($datos['iddia']) ? NULL : $datos['iddia'],
 			'hora' => $datos['hora'],
 			'indicaciones' => $datos['indicaciones'],
 		);
 		$this->db->where('idatenciondietaturno', $datos['idatenciondietaturno']);
 		return $this->db->update('atencion_dieta_turno',$data);
 	}

 	public function m_actualizar_dieta_alimento($datos){
 		$data = array(
 			'estado_ada' => 1,
 			'idatenciondietaturno' => $datos['idatenciondietaturno'],
 			'iddia' => empty($datos['iddia']) ? NULL : $datos['iddia'],
 			'idalimento' => $datos['idalimento'],
 			'valor' => $datos['valor'],
 		);
 		$this->db->where('idatenciondietaalim', $datos['idatenciondietaalim']);
 		return $this->db->update('atencion_dieta_alim',$data);
 	}

 	public function m_actualizar_dieta_turno_alimento_alter($datos){
 		$data = array(
 			'estado_chta' => 1,
 			'idatenciondietaalim' => $datos['idatenciondietaalim'],
 			'idalimento' => $datos['idalimento'],
 		);
 		$this->db->where('idatenciondietaalimalter', $datos['idatenciondietaalimalter']);
 		return $this->db->update('atencion_dieta_alim_alter',$data);
 	}
}
?>