<?php
class Model_profesional extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	//CARGAR PERFIL
	public function m_cargar_perfil($idusuario){ 
		$this->db->select('pro.idprofesional, pro.idusuario, pro.idespecialidad, pro.nombre, pro.apellidos, pro.correo, 
			pro.fecha_nacimiento, pro.num_colegiatura , pro.nombre_foto, us.username, us.idusuario, gr.idgrupo, gr.nombre_gr, gr.key_grupo',FALSE);
		$this->db->select('esp.descripcion_es as especialidad',FALSE);
		$this->db->from('profesional pro');
		$this->db->join('especialidad esp', 'esp.idespecialidad = pro.idespecialidad');
		$this->db->join('usuario us', 'pro.idusuario = us.idusuario');
		$this->db->join('grupo gr', 'us.idgrupo = gr.idgrupo');
		$this->db->where('pro.idusuario', $idusuario);
		$this->db->where('pro.estado_pf', 1);
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}

	public function m_cargar_profesional($paramPaginate=FALSE){
		$this->db->select('p.idprofesional, p.idusuario, u.username , u.idgrupo, e.descripcion_es AS especialidad, p.idespecialidad, p.nombre, p.apellidos, p.correo, p.fecha_nacimiento, p.num_colegiatura, p.nombre_foto');
		$this->db->from('profesional p');
		$this->db->join('especialidad e', 'p.idespecialidad = e.idespecialidad AND e.estado_es = 1','left');
		$this->db->join('usuario u', 'p.idusuario = u.idusuario AND u.estado_us = 1','left');				
		$this->db->where('p.estado_pf', 1);
		if( isset($paramPaginate['search'] ) && $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if(! empty($value)){
					$this->db->like($key ,strtoupper_total($value) ,FALSE);
				}
			}
		}

		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_profesional($paramPaginate=FALSE){
		$this->db->select('count(*) AS contador');
		$this->db->from('profesional p');
		$this->db->join('especialidad e', 'p.idespecialidad = e.idespecialidad AND e.estado_es = 1','left');		
		$this->db->where('p.estado_pf', 1);
		if( isset($paramPaginate['search'] ) && $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if(! empty($value)){
					$this->db->like($key ,strtoupper_total($value) ,FALSE);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}	
	public function m_cargar_profesional_cbo(){
		$this->db->select("idprofesional, CONCAT(nombre, ' ', apellidos) As profesional",FALSE);
		$this->db->from('profesional ');
		$this->db->where('estado_pf', 1);
		return $this->db->get()->result_array();
	}
	public function m_registrar($datos)
	{
		$data = array(
			'idusuario' => $datos['idusuario'],
			'idespecialidad' => $datos['idespecialidad'],
			'nombre' => strtoupper_total($datos['nombre']),
			'apellidos' => strtoupper_total($datos['apellidos']),
			'correo' => empty($datos['correo'])? NULL : $datos['correo'],	
			'fecha_nacimiento' => darFormatoYMD($datos['fecha_nacimiento']),	
			'num_colegiatura' => empty($datos['num_colegiatura'])? NULL : $datos['num_colegiatura'],
			'nombre_foto' => empty($datos['nombre_foto'])? 'sin-imagen.png' : $datos['nombre_foto'],											
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('profesional', $data);
	}	
	public function m_editar_foto($datos){
		$data = array(
			'nombre_foto' => $datos['nombre_foto'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idprofesional',$datos['idprofesional']);
		return $this->db->update('profesional', $data);
	}	
	public function m_editar($datos){
		$data = array(
			'idusuario' => $datos['idusuario'],
			'idespecialidad' => $datos['idespecialidad'],
			'nombre' => strtoupper_total($datos['nombre']),
			'apellidos' => strtoupper_total($datos['apellidos']),
			'correo' => empty($datos['correo'])? NULL : $datos['correo'],	
			'fecha_nacimiento' => darFormatoYMD($datos['fecha_nacimiento']),	
			'num_colegiatura' => empty($datos['num_colegiatura'])? NULL : $datos['num_colegiatura'],	
			'updatedAt' => date('Y-m-d H:i:s')
		);
		// var_dump($datos['fecha_nacimiento'],darFormatoYMD($datos['fecha_nacimiento'])); exit();
		$this->db->where('idprofesional',$datos['idprofesional']);
		return $this->db->update('profesional', $data);
	}

	public function m_anular($datos)
	{
		$data = array(
			'estado_pf' => 0,
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idprofesional',$datos['idprofesional']);
		return $this->db->update('profesional', $data);
	}	

}
?>