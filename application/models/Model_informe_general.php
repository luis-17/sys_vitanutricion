<?php
class Model_informe_general extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function cargar_total_pacientes_atendidos($datos){ 
		$sql = 'SELECT COUNT(*) AS contador, sc.idempresa  FROM ( 
					SELECT DISTINCT cl.idcliente, emp.idempresa 
					FROM atencion am 
					INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
					INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
					WHERE am.estado_atencion = 1 
					AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
				) AS sc 
				GROUP BY sc.idempresa';
		$query = $this->db->query( $sql,array(darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin'])) ); 
		return $query->row_array();
	}
	public function cargar_total_atenciones_realizadas($datos){ 
		$sql = 'SELECT COUNT(*) AS contador, emp.idempresa 
					FROM  atencion am 
					INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
					INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
					WHERE am.estado_atencion = 1 
					AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
					GROUP BY emp.idempresa'; 
		$query = $this->db->query( $sql,array(darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin'])) ); 
		return $query->row_array();
	}
}
?>