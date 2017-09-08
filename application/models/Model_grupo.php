<?php
class Model_grupo extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_grupo($arrGrupos){
		$this->db->select('gr.idgrupo, gr.nombre_gr');
		$this->db->from('grupo gr');
		$this->db->where('estado_gr', 1);
		$this->db->where_in('key_grupo', $arrGrupos);
		return $this->db->get()->result_array();
	}
	
}
?>