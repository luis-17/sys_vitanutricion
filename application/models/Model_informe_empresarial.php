<?php
class Model_informe_empresarial extends CI_Model {
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
					AND emp.idempresa = ? 
					AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
				) AS sc 
				GROUP BY sc.idempresa';
		$query = $this->db->query( $sql,array($datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin'])) ); 
		return $query->row_array();
	}
	public function cargar_total_atenciones_realizadas($datos){ 
		$sql = 'SELECT COUNT(*) AS contador, emp.idempresa 
					FROM  atencion am 
					INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
					INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
					WHERE am.estado_atencion = 1 
					AND emp.idempresa = ? 
					AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
					GROUP BY emp.idempresa'; 
		$query = $this->db->query( $sql,array($datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin'])) ); 
		return $query->row_array();
	}
	public function cargar_pacientes_por_sexo_atendidos($datos)
	{
		$sql = 'SELECT COUNT(*) AS contador, UPPER(sc.sexo) AS sexo, sc.idempresa  FROM ( 
					SELECT DISTINCT cl.idcliente, cl.sexo, emp.idempresa 
					FROM atencion am 
					INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
					INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
					WHERE am.estado_atencion = 1 
					AND emp.idempresa = ? 
					AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
				) AS sc 
			GROUP BY UPPER(sc.sexo), sc.idempresa';
		$query = $this->db->query( $sql,array($datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin'])) ); 
		return $query->result_array();
	}
	public function cargar_pacientes_por_sexo_atendidos_mas_complementos($datos)
	{
		$sql = "
		SELECT cl.idcliente, cl.sexo, emp.idempresa, TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) AS edad, 
				ROUND(am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ,2 ) AS imc, am.puntaje_grasa_visceral, 
				(1.2 * ( ROUND(am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ,2 ) ) + 0.23 * ( TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) ) - 10.8 * ( IF (cl.sexo = 'M',1,0) ) - 5.4) AS value_porc_grasa_corporal, am.cm_cintura, am.porc_masa_muscular 
				FROM atencion am 
				INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
				INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
				WHERE am.estado_atencion = 1 
				AND emp.idempresa = ? 
				AND DATE(am.fecha_atencion) BETWEEN ? AND ?";
		$query = $this->db->query( $sql,array($datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin'])) ); 
		return $query->result_array();
	}
	public function cargar_pacientes_por_edad_atendidos($datos)
	{
		$sql = " 
			SELECT DISTINCT cl.idcliente, emp.idempresa, 'J' AS etareo 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 18 AND 29  
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT DISTINCT cl.idcliente, emp.idempresa, 'A' AS etareo 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 30 AND 59  
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT DISTINCT cl.idcliente, emp.idempresa, 'AD' AS etareo 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 60 AND 150   
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ?
		"; 
		$query = $this->db->query( $sql,
			array(
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
			) 
		); 
		return $query->result_array();
	}
	public function cargar_pacientes_por_edad_atendidos_mas_complementos($datos)
	{
		$sql = " 
			SELECT cl.idcliente, emp.idempresa, 'J' AS etareo, cl.sexo, TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) AS edad, 
				ROUND(am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ,2 ) AS imc, am.puntaje_grasa_visceral, 
				(1.2 * ( ROUND(am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ,2 ) ) + 0.23 * ( TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) ) - 10.8 * ( IF (cl.sexo = 'M',1,0) ) - 5.4) AS value_porc_grasa_corporal, am.cm_cintura, am.porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 18 AND 29  
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT DISTINCT cl.idcliente, emp.idempresa, 'A' AS etareo, cl.sexo, TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) AS edad, 
				ROUND(am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ,2 ) AS imc, am.puntaje_grasa_visceral, 
				(1.2 * ( ROUND(am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ,2 ) ) + 0.23 * ( TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) ) - 10.8 * ( IF (cl.sexo = 'M',1,0) ) - 5.4) AS value_porc_grasa_corporal, am.cm_cintura, am.porc_masa_muscular     
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 30 AND 59  
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT DISTINCT cl.idcliente, emp.idempresa, 'AD' AS etareo, cl.sexo, TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) AS edad,  
				ROUND(am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ,2 ) AS imc, am.puntaje_grasa_visceral, 
				(1.2 * ( ROUND(am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ,2 ) ) + 0.23 * ( TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) ) - 10.8 * ( IF (cl.sexo = 'M',1,0) ) - 5.4) AS value_porc_grasa_corporal, am.cm_cintura, am.porc_masa_muscular     
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 60 AND 150   
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ?
		"; 
		$query = $this->db->query( $sql,
			array(
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
			) 
		); 
		return $query->result_array();
	}
	public function cargar_pacientes_por_peso_atendidos($datos)
	{
		$sql= "
			SELECT COUNT(*) AS contador ,'Bajo de peso' AS tipo_peso 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND (am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ) BETWEEN 1 AND 18.5 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'Normal' AS tipo_peso 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND (am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ) BETWEEN 18.6 AND 24.9 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'Sobrepeso' AS tipo_peso 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND (am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ) BETWEEN 25 AND 29.9 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'Obesidad 1°' AS tipo_peso 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND (am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ) BETWEEN 30 AND 34.9 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'Obesidad 2°' AS tipo_peso 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND (am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ) BETWEEN 35 AND 39.9 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'Obesidad 3°' AS tipo_peso 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND (am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ) BETWEEN 40 AND 100 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
		";
		$query = $this->db->query( $sql,
			array(
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
			) 
		); 
		return $query->result_array();
	}
	// PESO PERDIDO 
	public function cargar_detalle_pacientes_atendidos($datos)
	{
		$sql = 'SELECT am.idatencion, cl.idcliente, cl.nombre, cl.apellidos, cl.sexo, emp.idempresa, am.fecha_atencion, am.peso, 
				TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) AS edad, am.kg_masa_grasa 
				FROM atencion am 
				INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
				INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
				WHERE am.estado_atencion = 1 
				AND emp.idempresa = ? 
				AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
				ORDER BY cl.idcliente ASC, am.fecha_atencion ASC';
		$query = $this->db->query( $sql,array($datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin'])) ); 
		return $query->result_array();
	}
	// INDICE GRASA VISCERAL 
	public function cargar_pacientes_por_grasa_visceral_atendidos($datos)
	{
		$sql= "
			SELECT COUNT(*) AS contador ,'NORMAL' AS indice_grasa_visceral  
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.puntaje_grasa_visceral BETWEEN 0 AND 9  
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'ALTO' AS indice_grasa_visceral  
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.puntaje_grasa_visceral BETWEEN 10 AND 14 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'MUY ALTO' AS indice_grasa_visceral    
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.puntaje_grasa_visceral >= 15 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
		";
		$query = $this->db->query( $sql, 
			array(
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']) 
			) 
		); 
		return $query->result_array();
	}
	// % GRASA CORPORAL  
	public function cargar_pacientes_por_grasa_corporal_atendidos($datos)
	{
		$sql= "
			SELECT COUNT(*) AS contador ,'BAJO' AS porc_grasa_corporal 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND (1.2 * ( ROUND(am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ,2 ) ) + 0.23 * ( TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) ) - 10.8 * ( IF (cl.sexo = 'M',1,0) ) - 5.4) < 25 
			AND cl.sexo = 'F' 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'BAJO' AS porc_grasa_corporal  
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND (1.2 * ( ROUND(am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ,2 ) ) + 0.23 * ( TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) ) - 10.8 * ( IF (cl.sexo = 'M',1,0) ) - 5.4) < 15 
			AND cl.sexo = 'M' 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			
			UNION 

			SELECT COUNT(*) AS contador ,'NORMAL' AS porc_grasa_corporal  
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND (1.2 * ( ROUND(am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ,2 ) ) + 0.23 * ( TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) ) - 10.8 * ( IF (cl.sexo = 'M',1,0) ) - 5.4) BETWEEN 25 AND 30 
			AND cl.sexo = 'F' 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'NORMAL' AS porc_grasa_corporal  
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND (1.2 * ( ROUND(am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ,2 ) ) + 0.23 * ( TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) ) - 10.8 * ( IF (cl.sexo = 'M',1,0) ) - 5.4) BETWEEN 15 AND 20 
			AND cl.sexo = 'M' 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			
			UNION 

			SELECT COUNT(*) AS contador ,'ALTO' AS porc_grasa_corporal  
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND (1.2 * ( ROUND(am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ,2 ) ) + 0.23 * ( TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) ) - 10.8 * ( IF (cl.sexo = 'M',1,0) ) - 5.4) > 30 
			AND cl.sexo = 'F' 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'ALTO' AS porc_grasa_corporal  
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND (1.2 * ( ROUND(am.peso / ( (cl.estatura / 100) * (cl.estatura / 100)  ) ,2 ) ) + 0.23 * ( TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) ) - 10.8 * ( IF (cl.sexo = 'M',1,0) ) - 5.4) > 20 
			AND cl.sexo = 'M' 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
		";
		$query = $this->db->query( $sql, 
			array(
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin'])
			) 
		); 
		return $query->result_array();
	}
	// PERÍMETRO DE CINTURA 
	public function cargar_pacientes_por_perimetro_cintura_atendidos($datos)
	{
		$sql= "
			SELECT COUNT(*) AS contador ,'NORMAL' AS dx_perimetro_cintura 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.cm_cintura BETWEEN 10 AND 80 
			AND cl.sexo = 'F' 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'NORMAL' AS dx_perimetro_cintura  
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.cm_cintura BETWEEN 10 AND 90 
			AND cl.sexo = 'M' 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			
			UNION 

			SELECT COUNT(*) AS contador ,'RIESGO CARDIOVASCULAR' AS dx_perimetro_cintura 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.cm_cintura > 80 
			AND cl.sexo = 'F' 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'RIESGO CARDIOVASCULAR' AS dx_perimetro_cintura 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.cm_cintura > 90 
			AND cl.sexo = 'M' 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
		";
		$query = $this->db->query( $sql, 
			array(
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']) 
			) 
		); 
		return $query->result_array();
	}
	// % DE MASA MUSCULAR 
	public function cargar_pacientes_por_porc_masa_muscular_atendidos($datos)
	{
		$sql= "
			SELECT COUNT(*) AS contador ,'BAJO' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular BETWEEN 1 AND 24.29 
			AND cl.sexo = 'F' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 18 AND 39 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'BAJO' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular BETWEEN 1 AND 24.09 
			AND cl.sexo = 'F' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 40 AND 59 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'BAJO' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular BETWEEN 1 AND 23.89 
			AND cl.sexo = 'F' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 60 AND 80 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
		UNION 
			SELECT COUNT(*) AS contador ,'BAJO' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular BETWEEN 1 AND 33.29 
			AND cl.sexo = 'M' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 18 AND 39 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'BAJO' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular BETWEEN 1 AND 33.09 
			AND cl.sexo = 'M' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 40 AND 59 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'BAJO' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular BETWEEN 1 AND 32.89 
			AND cl.sexo = 'M' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 60 AND 80 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 

		UNION 

			SELECT COUNT(*) AS contador ,'NORMAL' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular BETWEEN 24.3 AND 30.3 
			AND cl.sexo = 'F' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 18 AND 39 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'NORMAL' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular BETWEEN 24.1 AND 30.1 
			AND cl.sexo = 'F' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 40 AND 59 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'NORMAL' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular BETWEEN 23.9 AND 29.9 
			AND cl.sexo = 'F' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 60 AND 80 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
		UNION 
			SELECT COUNT(*) AS contador ,'NORMAL' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular BETWEEN 33.3 AND 39.3 
			AND cl.sexo = 'M' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 18 AND 39 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'NORMAL' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular BETWEEN 33.1 AND 39.1 
			AND cl.sexo = 'M' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 40 AND 59 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'NORMAL' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular BETWEEN 32.9 AND 38.9 
			AND cl.sexo = 'M' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 60 AND 80 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 

		UNION 

			SELECT COUNT(*) AS contador ,'ELEVADO' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular >= 30.4 
			AND cl.sexo = 'F' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 18 AND 39 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'ELEVADO' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular >= 30.2 
			AND cl.sexo = 'F' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 40 AND 59 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'ELEVADO' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular >= 30
			AND cl.sexo = 'F' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 60 AND 80 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
		UNION 
			SELECT COUNT(*) AS contador ,'ELEVADO' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular >= 39.4 
			AND cl.sexo = 'M' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 18 AND 39 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'ELEVADO' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular >= 39.2 
			AND cl.sexo = 'M' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 40 AND 59 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
			UNION 
			SELECT COUNT(*) AS contador ,'ELEVADO' AS dx_porc_masa_muscular 
			FROM atencion am 
			INNER JOIN cliente cl ON am.idcliente = cl.idcliente 
			INNER JOIN empresa emp ON cl.idempresa = emp.idempresa 
			WHERE am.estado_atencion = 1 
			AND am.porc_masa_muscular >= 39 
			AND cl.sexo = 'M' 
			AND TIMESTAMPDIFF(YEAR, cl.fecha_nacimiento, CURDATE()) BETWEEN 60 AND 80 
			AND am.peso > 0 
			AND cl.estatura > 0 
			AND emp.idempresa = ? 
			AND DATE(am.fecha_atencion) BETWEEN ? AND ? 
		";
		$query = $this->db->query( $sql, 
			array(
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),

				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),

				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']),
				$datos['empresa']['id'], darFormatoYMD($datos['inicio']), darFormatoYMD($datos['fin']) 
			) 
		); 
		return $query->result_array();
	}
}
?>