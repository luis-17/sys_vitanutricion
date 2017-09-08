<?php
class Model_especialidad extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_especialidad(){
		$this->db->select('idespecialidad, descripcion_es',FALSE);
		$this->db->from('especialidad');
		$this->db->where('estado_es', 1);
		return $this->db->get()->result_array();
	}
}
?>