<?php
class Model_alimentos extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_alimentos($paramPaginate=FALSE){
		$this->db->select('al.idalimento, al.nombre, al.calorias, al.proteinas, al.grasas, al.carbohidratos, al.medida_casera, al.gramo, al.fibra, al.ceniza, al.calcio, al.fosforo, al.zinc, al.hierro, al.estado_ali, g1.idgrupo1, g1.descripcion_gr1, g2.idgrupo2, g2.descripcion_gr2'); 
		$this->db->from('alimento al');
		$this->db->join('grupo1 g1','al.idgrupo1 = g1.idgrupo1');
		$this->db->join('grupo2 g2','al.idgrupo2 = g2.idgrupo2');
		$this->db->where('al.estado_ali', 1);
		if( isset($paramPaginate['search'] ) && $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if(! empty($value)){
					$this->db->like($key ,strtoupper($value) ,FALSE);
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
	public function m_count_alimentos($paramPaginate=FALSE){
		$this->db->select('COUNT(*) AS contador');
		$this->db->from('alimento al');
		$this->db->join('grupo1 g1','al.idgrupo1 = g1.idgrupo1');
		$this->db->join('grupo2 g2','al.idgrupo2 = g2.idgrupo2');
		$this->db->where('al.estado_ali', 1);
		if( isset($paramPaginate['search'] ) && $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if(! empty($value)){
					$this->db->like($key ,strtoupper($value) ,FALSE);
				}
			}
		}
		$fData = $this->db->get()->row_array();
		return $fData;
	}

	public function m_registrar($datos)
	{
		$data = array(
			'idgrupo1' => $datos['idgrupo1']['id'],
			'idgrupo2' => $datos['idgrupo2']['id'],
			'nombre' => strtoupper_total($datos['nombre']),
			'calorias' => empty($datos['calorias']) ? NULL : $datos['calorias'],
			'proteinas' => empty($datos['proteinas']) ? NULL : $datos['proteinas'],
			'grasas' => empty($datos['grasas']) ? NULL : $datos['grasas'],
			'carbohidratos' => empty($datos['carbohidratos']) ? NULL : $datos['carbohidratos'],
			'medida_casera' => empty($datos['medida_casera']) ? NULL : $datos['medida_casera'],
			'gramo' => empty($datos['gramo']) ? NULL : $datos['gramo'],
			'fibra' => empty($datos['fibra']) ? NULL : $datos['fibra'],
			'ceniza' => empty($datos['ceniza']) ? NULL : $datos['ceniza'],
			'calcio' => empty($datos['calcio']) ? NULL : $datos['calcio'],
			'fosforo' => empty($datos['fosforo']) ? NULL : $datos['fosforo'],
			'zinc' => empty($datos['zinc']) ? NULL : $datos['zinc'],
			'hierro' => empty($datos['hierro']) ? NULL : $datos['hierro'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('alimento', $data);
	}

	public function m_editar($datos)
	{
		$data = array(
			'idgrupo1' => $datos['idgrupo1']['id'],
			'idgrupo2' => $datos['idgrupo2']['id'],
			'nombre' => strtoupper($datos['nombre']),
			'calorias' => empty($datos['calorias']) ? NULL : $datos['calorias'],
			'proteinas' => empty($datos['proteinas']) ? NULL : $datos['proteinas'],
			'grasas' => empty($datos['grasas']) ? NULL : $datos['grasas'],
			'carbohidratos' => empty($datos['carbohidratos']) ? NULL : $datos['carbohidratos'],
			'medida_casera' => empty($datos['medida_casera']) ? NULL : $datos['medida_casera'],
			'gramo' => empty($datos['gramo']) ? NULL : $datos['gramo'],
			'fibra' => empty($datos['fibra']) ? NULL : $datos['fibra'],
			'ceniza' => empty($datos['ceniza']) ? NULL : $datos['ceniza'],
			'calcio' => empty($datos['calcio']) ? NULL : $datos['calcio'],
			'fosforo' => empty($datos['fosforo']) ? NULL : $datos['fosforo'],
			'zinc' => empty($datos['zinc']) ? NULL : $datos['zinc'],
			'hierro' => empty($datos['hierro']) ? NULL : $datos['hierro'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idalimento',$datos['idalimento']);
		return $this->db->update('alimento', $data);
	}
	public function m_anular($datos)
	{
		$data = array(
			'estado_ali' => 0,
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idalimento',$datos['idalimento']);
		return $this->db->update('alimento', $data);
	}

	public function m_cargar_alimentos_cbo($datos){
		$this->db->select("a.idalimento,a.idgrupo1,a.idgrupo2,a.nombre,a.calorias,a.proteinas,a.grasas, a.carbohidratos, 
			 a.estado_ali, a.medida_casera, a.gramo, a.ceniza, a.calcio, a.fosforo, a.zinc, a.hierro, a.fibra ");
		$this->db->from('alimento a');
		$this->db->where("a.estado_ali",1);
		$this->db->where("UPPER(a.nombre) LIKE '%". strtoupper($datos['search']) . "%'");
		$this->db->limit(10);
		return $this->db->get()->result_array();
	}

}