<?php
class Model_usuario extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_usuario_disponible(){
		$this->db->select('u.idusuario, u.username, u.idgrupo');
		$this->db->from('usuario u');
		$this->db->join('profesionales p', 'u.idusuario = p.idusuario AND u.estado_us = 1 AND p.idusuario = NULL','left');			
		return $this->db->get()->result_array();
	}
	public function m_cargar_usuario_search($datos){
		$this->db->select('u.idusuario,u.username',FALSE);
		$this->db->from('usuario u');		
		$this->db->where("username LIKE '". $datos['username'] . "'");
		return $this->db->get()->result_array();		
	}
	public function m_cargar_usuario_autocomplete($datos){
		$this->db->select('u.idusuario,u.username',FALSE);
		$this->db->from('usuario u');		
		$this->db->join('profesional p', 'u.idusuario = p.idusuario AND u.estado_us = 1','left');	
		$this->db->where("username LIKE '%". $datos['search'] . "%' AND p.idusuario IS NULL");

		$this->db->limit(10);
		return $this->db->get()->result_array();
	}	
	public function m_cargar_usuario_id($datos){
		$this->db->select('idusuario,username,idgrupo',FALSE);
		$this->db->from('usuario');		
		$this->db->where('idusuario',$datos['idusuario']);
		$this->db->where('estado_us',1);		
		return $this->db->get()->result_array();
	}	
	public function m_registrar($datos)
	{
		$data = array(
			'username' => $datos['username'],
			'idgrupo' => $datos['idgrupo']['id'],
			'pass' => do_hash($datos['pass'],'md5'), 	
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s'),
			'pass_view' => $datos['pass']
		);
		return $this->db->insert('usuario', $data);
	}	
	public function m_editar($datos){
		$data = array(
			'username' => $datos['username'],
			'idgrupo' => $datos['idgrupo']['id'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idusuario',$datos['idusuario']);
		return $this->db->update('usuario', $data);
	}

	public function m_anular($datos)
	{
		$data = array(
			'estado_us' => 0,
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idusuario',$datos['idusuario']);
		return $this->db->update('usuario', $data);
	}
	public function m_cambiar_clave($datos){
		$data = array(
			'pass' => do_hash($datos['pass'],'md5')
		);
		$this->db->where('idusuario',$datos['idusuario']);
		return $this->db->update('usuario', $data);
	}		
	public function m_verificar_clave($datos){
		$this->db->select('idusuario,username',FALSE);
		$this->db->from('usuario');		
		$this->db->where("idusuario",$datos['idusuario']);
		$this->db->where("pass",do_hash($datos['passAnt'],'md5'));		
		return $this->db->get()->result_array();		
	}			
	
}
?>