<?php
class Model_turno extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	// ACCESO AL SISTEMA
	public function m_cargar_turno_cbo(){ 
		$this->db->select('t.idturno, t.descripcion_tu',FALSE);
		$this->db->from('turno t');
		$this->db->where('estado_tu',1);
		return $this->db->get()->result_array();
	}
}
?>