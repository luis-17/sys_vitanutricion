<?php
class Model_tipoCliente extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_tipo_cliente_cbo(){
		$this->db->select('tc.idtipocliente, tc.descripcion_tc, tc.estado_tc',FALSE);
		$this->db->from('tipo_cliente tc');
		$this->db->where('tc.estado_tc', 1);
		return $this->db->get()->result_array();
	}
	public function m_cargar_prefijo_tipo_cliente($id){
		$this->db->select('tc.idtipocliente, tc.descripcion_tc, tc.estado_tc, tc.prefijo',FALSE);
		$this->db->from('tipo_cliente tc');
		$this->db->where('tc.idtipocliente', $id);
		return $this->db->get()->row_array();
	}
}
?>