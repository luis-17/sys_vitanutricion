<?php
class Model_antecedente extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_antecedente_por_tipo($datos){
		$this->db->select('an.idantecedente, an.nombre, an.tipo, an.comentario, an.estado_an',FALSE);
		$this->db->from('antecedente an');
		$this->db->where('an.estado_an', 1);
		$this->db->where('an.tipo', $datos['tipo']);
		return $this->db->get()->result_array();
	}
}
?>