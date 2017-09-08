<?php
class Model_ubicacion extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	// ACCESO AL SISTEMA
	public function m_cargar_ubicacion_cbo(){ 
		$this->db->select('ub.idubicacion, ub.descripcion_ub, ub.estado_ub',FALSE);
		$this->db->from('ubicacion ub');
		$this->db->where('ub.estado_ub <>', 0);
		return $this->db->get()->result_array();
	}
}
?>