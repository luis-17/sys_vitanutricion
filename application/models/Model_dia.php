<?php
class Model_dia extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	// ACCESO AL SISTEMA
	public function m_cargar_dia_cbo(){ 
		$this->db->select('d.iddia, d.nombre_dia',FALSE);
		$this->db->from('dia d');
		return $this->db->get()->result_array();
	}
}
?>