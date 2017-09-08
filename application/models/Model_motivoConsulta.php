<?php
class Model_motivoConsulta extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_motivo_consulta_cbo(){
		$this->db->select('mc.idmotivoconsulta, mc.descripcion_mc, mc.estado_mc',FALSE);
		$this->db->from('motivo_consulta mc');
		$this->db->where('mc.estado_mc', 1);
		return $this->db->get()->result_array();
	}
}
?>