<?php
class Model_grupoAlimento extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_grupo_alimento_1(){
		$this->db->select('idgrupo1, descripcion_gr1');
		$this->db->from('grupo1');
		return $this->db->get()->result_array();
	}
	public function m_cargar_grupo_alimento_2($datos){
		$this->db->select('idgrupo2, descripcion_gr2, idgrupo1');
		$this->db->from('grupo2');
		$this->db->where('idgrupo1', $datos);		
		return $this->db->get()->result_array();
	}	
}
?>