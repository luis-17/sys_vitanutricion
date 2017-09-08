<?php
class Model_paciente extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	public function m_cargar_pacientes($paramPaginate=FALSE){
		$this->db->select('cl.idcliente, cl.nombre, cl.apellidos, cl.sexo, cl.fecha_nacimiento,
			cl.estatura,cl.email, cl.celular, cl.nombre_foto, cl.idtipocliente, cl.idempresa,
			mc.descripcion_mc AS clasificacion,	cl.idmotivoconsulta, cl.cod_historia_clinica,
			cl.alergias_ia, cl.medicamentos, cl.cargo_laboral, tc.descripcion_tc, emp.nombre_comercial,
			cl.antecedentes_notas, cl.habitos_notas, cl.estado_cl, MAX(at.fecha_atencion) AS fec_ult_atencion, count(at.idatencion) AS cant_atencion, cl.createdat as fecha_alta');
		$this->db->from('cliente cl');
		$this->db->join('motivo_consulta mc', 'cl.idmotivoconsulta = mc.idmotivoconsulta');
		$this->db->join('atencion at', 'cl.idcliente = at.idcliente AND at.estado_atencion = 1','left');
		$this->db->join('tipo_cliente tc','cl.idtipocliente = tc.idtipocliente');
		$this->db->join('empresa emp','cl.idempresa = emp.idempresa','left');
		$this->db->where('cl.estado_cl', 1);
		if( isset($paramPaginate['search'] ) && $paramPaginate['search'] ){
			foreach ($paramPaginate['searchColumn'] as $key => $value) {
				if(! empty($value)){
					$this->db->like($key ,strtoupper_total($value) ,FALSE);
				}
			}
		}
		$this->db->group_by('cl.idcliente');
		if( $paramPaginate['sortName'] ){
			$this->db->order_by($paramPaginate['sortName'], $paramPaginate['sort']);
		}
		if( $paramPaginate['firstRow'] || $paramPaginate['pageSize'] ){
			$this->db->limit($paramPaginate['pageSize'],$paramPaginate['firstRow'] );
		}
		return $this->db->get()->result_array();
	}
	public function m_count_pacientes($paramPaginate=FALSE){
		$this->db->select('count(*) AS contador');
		$this->db->from('cliente cl');
		$this->db->join('empresa emp','cl.idempresa = emp.idempresa','left');
		$this->db->where('estado_cl', 1);
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
	public function m_cargar_paciente_por_id($datos){
		$this->db->select('cl.idcliente, cl.nombre, cl.apellidos, cl.sexo, cl.fecha_nacimiento, cl.estatura,
			cl.email, cl.celular, cl.nombre_foto, mc.descripcion_mc AS clasificacion, cl.cargo_laboral, 
			tc.idtipocliente, tc.descripcion_tc, emp.idempresa, emp.nombre_comercial,
			cl.idmotivoconsulta, cl.cod_historia_clinica, alergias_ia, cl.medicamentos,
			cl.antecedentes_notas, cl.habitos_notas, cl.estado_cl, MAX(at.fecha_atencion) AS fec_ult_atencion, count(at.idatencion) AS cant_atencion, cl.createdat as fecha_alta');
		$this->db->select("UPPER(CONCAT(cl.nombre, ' ',cl.apellidos)) as paciente",FALSE);
		$this->db->from('cliente cl');
		$this->db->join('motivo_consulta mc', 'cl.idmotivoconsulta = mc.idmotivoconsulta');
		$this->db->join('atencion at', 'cl.idcliente = at.idcliente AND at.estado_atencion = 1','left');
		$this->db->join('tipo_cliente tc','cl.idtipocliente = tc.idtipocliente');
		$this->db->join('empresa emp','cl.idempresa = emp.idempresa','left');
		$this->db->where('cl.estado_cl', 1);
		$this->db->where('cl.idcliente', $datos['idcliente']);
		$this->db->group_by('cl.idcliente');
		$this->db->limit(1);

		return $this->db->get()->row_array();
	}
	public function m_cargar_paciente_por_nombre($datos){
		$this->db->select('cl.idcliente, cl.nombre, cl.apellidos, cl.sexo, cl.fecha_nacimiento, cl.estatura,
			cl.email, cl.celular, cl.nombre_foto, cl.idtipocliente, cl.idempresa, mc.descripcion_mc AS clasificacion, cl.cargo_laboral, tc.descripcion_tc,emp.nombre_comercial,
			cl.idmotivoconsulta, cl.cod_historia_clinica, alergias_ia, cl.medicamentos,
			cl.antecedentes_notas, cl.habitos_notas, cl.estado_cl, MAX(at.fecha_atencion) AS fec_ult_atencion, count(at.idatencion) AS cant_atencion, cl.createdat as fecha_alta');
		$this->db->from('cliente cl');
		$this->db->join('motivo_consulta mc', 'cl.idmotivoconsulta = mc.idmotivoconsulta');
		$this->db->join('atencion at', 'cl.idcliente = at.idcliente AND at.estado_atencion = 1','left');
		$this->db->join('tipo_cliente tc','cl.idtipocliente = tc.idtipocliente');
		$this->db->join('empresa emp','cl.idempresa = emp.idempresa','left');		
		$this->db->where('cl.estado_cl', 1);
		$this->db->where("UPPER(CONCAT(cl.nombre, ' ',cl.apellidos)) LIKE '%". strtoupper_total($datos['search']) . "%'");
		$this->db->group_by('cl.idcliente');		
		$this->db->limit(1);

		return $this->db->get()->row_array();
	}
	public function m_cargar_ultimo_codigo_historia_clinica($datos){
		$this->db->select('cl.idcliente, cl.cod_historia_clinica');
		$this->db->from('cliente cl');
		$this->db->where('cl.idtipocliente',$datos['idtipocliente']);
		$this->db->where("cl.cod_historia_clinica LIKE '" . $datos['prefijo'] . "%'");
		$this->db->order_by('cl.idcliente', 'DESC');
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}
	public function m_cargar_pacientes_autocomplete($datos){
		$this->db->select("c.idcliente, c.email");
		$this->db->select("UPPER(CONCAT(c.nombre, ' ',c.apellidos)) AS paciente", FALSE);
		$this->db->from('cliente c');
		$this->db->where("c.estado_cl",1);
		$this->db->where("UPPER(CONCAT(c.nombre, ' ',c.apellidos)) LIKE '%". strtoupper_total($datos['search']) . "%'");

		$this->db->limit(10);
		return $this->db->get()->result_array();
	}
	public function m_cargar_habitos_alim_paciente($datos){
		$this->db->select("ht.idclientehabitoturno, tu.idturno, tu.descripcion_tu, ht.hora, ht.texto_alimentos");

		$this->db->from('cliente_habito_turno ht');
		$this->db->join('turno tu', 'ht.idturno = tu.idturno AND ht.idcliente = ' . $datos['idcliente'] . ' AND ht.estado_ht = 1 AND tu.estado_tu = 1','right');

		return $this->db->get()->result_array();
	}
	public function m_cargar_habitos_paciente($datos){
		$this->db->select("clha.idclientehabitogen, clha.actividad_fisica, clha.frecuencia,clha.detalle_act_fisica, clha.consumo_agua, clha.consumo_gaseosa");
		$this->db->select("clha.consumo_alcohol, clha.consumo_tabaco,clha.tiempo_suenio, clha.notas_generales, clha.estado_clha");
		$this->db->from('cliente_habito_gen clha');
		$this->db->where("clha.estado_clha",1);
		$this->db->where("clha.idcliente",$datos['idcliente']);
		return $this->db->get()->row_array();
	}
	public function m_cargar_antecedentes_paciente($datos){
		$this->db->select("an.idantecedente, an.nombre as antecedente, an.tipo, clan.texto_otros");
		$this->db->select("clan.idcliente, CASE WHEN idcliente IS NOT NULL THEN 1 ELSE 0 END AS checkbox",FALSE);
		$this->db->from('cliente_antecedente clan');
		$this->db->join('antecedente an', 'clan.idantecedente = an.idantecedente AND clan.idcliente = ' . $datos['idcliente'] . ' AND clan.estado_clan = 1','right');


		return $this->db->get()->result_array();
	}
	public function m_cargar_ultimos_antecedentes_paciente($datos){
		$this->db->select("an.idantecedente, an.nombre as antecedente, an.tipo, clan.texto_otros");
		$this->db->from('cliente_antecedente clan');
		$this->db->join('antecedente an', 'clan.idantecedente = an.idantecedente AND clan.idcliente = ' . $datos['idcliente'] . ' AND clan.estado_clan = 1');
		$this->db->limit(5);
		return $this->db->get()->result_array();
	}
	public function m_cargar_historial_paciente($datos){
		$this->db->select("at.idatencion, at.idcita, at.idcliente, at.peso, at.fecha_atencion, cl.estatura, at.kg_masa_grasa, at.kg_masa_muscular");
		$this->db->from('atencion at');
		$this->db->join('cliente cl','at.idcliente = cl.idcliente');
		$this->db->where('at.idcliente',$datos['idcliente']);
		$this->db->where('at.estado_atencion',1);
		$this->db->order_by('at.fecha_atencion','ASC');
		return $this->db->get()->result_array();
	}
	public function m_cargar_planes_paciente($datos){
		$this->db->select("at.idatencion, at.idcita, at.idcliente, at.fecha_atencion, at.indicaciones_dieta, tipo_dieta");
		$this->db->from('atencion at');
		// $this->db->join('cliente cl','at.idcliente = cl.idcliente');
		$this->db->where('at.idcliente',$datos['idcliente']);
		$this->db->where('at.estado_atencion',1);
		$this->db->where('at.tipo_dieta IS NOT NULL'); 
		// $this->db->where('at.indicaciones_dieta IS NOT NULL');
		$this->db->order_by('at.fecha_atencion','ASC');
		return $this->db->get()->result_array();
	}

	public function m_registrar($datos)
	{
		$data = array(
			'nombre' => strtoupper_total($datos['nombre']),
			'apellidos' => strtoupper_total($datos['apellidos']),
			'idtipocliente' => $datos['idtipocliente'],
			'idempresa' => empty($datos['idempresa'])? NULL : $datos['idempresa'],
			'idmotivoconsulta' => $datos['idmotivoconsulta'],
			'cod_historia_clinica' => empty($datos['cod_historia_clinica'])? 'H001' : $datos['cod_historia_clinica'],
			'sexo' => $datos['sexo'],
			'estatura' => $datos['estatura'],
			'fecha_nacimiento' => darFormatoYMD($datos['fecha_nacimiento']),
			'email' => empty($datos['email'])? '' : $datos['email'],
			'celular' => $datos['celular'],
			'cargo_laboral' => empty($datos['cargo_laboral'])? NULL : $datos['cargo_laboral'],
			'nombre_foto' => empty($datos['nombre_foto'])? 'sin-imagen.png' : $datos['nombre_foto'],
			// 'alergias_ia' => empty($datos['alergias_ia'])? NULL : $datos['alergias_ia'],
			// 'medicamentos' => empty($datos['medicamentos'])? NULL : $datos['medicamentos'],
			// 'antecedentes_notas' => empty($datos['antecedentes_notas'])? NULL : $datos['antecedentes_notas'],
			// 'habitos_notas' => empty($datos['habitos_notas'])? NULL : $datos['habitos_notas'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('cliente', $data);
	}

	public function m_editar_foto($datos){
		$data = array(
			'nombre_foto' => $datos['nombre_foto'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idcliente',$datos['idcliente']);
		return $this->db->update('cliente', $data);
	}
	public function m_editar($datos){
		$data = array(
			'nombre' => strtoupper_total($datos['nombre']),
			'apellidos' => strtoupper_total($datos['apellidos']),
			'idtipocliente' => $datos['idtipocliente'], 
			'idempresa' => empty($datos['idempresa']) ? NULL : $datos['idempresa'],
			'idmotivoconsulta' => $datos['idmotivoconsulta'],
			'sexo' => $datos['sexo'],
			'estatura' => $datos['estatura'],
			'fecha_nacimiento' => darFormatoYMD($datos['fecha_nacimiento']),
			'email' => $datos['email'],
			'celular' => $datos['celular'],
			'cargo_laboral' => empty($datos['cargo_laboral'])? NULL : $datos['cargo_laboral'],
			// 'nombre_foto' => empty($datos['nombre_foto'])? 'sin-imagen.png' : $datos['nombre_foto'],
			// 'alergias_ia' => empty($datos['alergias_ia'])? NULL : $datos['alergias_ia'],
			// 'medicamentos' => empty($datos['medicamentos'])? NULL : $datos['medicamentos'],
			// 'antecedentes_notas' => empty($datos['antecedentes_notas'])? NULL : $datos['antecedentes_notas'],
			// 'habitos_notas' => empty($datos['habitos_notas'])? NULL : $datos['habitos_notas'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idcliente',$datos['idcliente']);
		return $this->db->update('cliente', $data);
	}

	public function m_anular($datos)
	{
		$data = array(
			'estado_cl' => 0,
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idcliente',$datos['idcliente']);
		return $this->db->update('cliente', $data);
	}
	// ANTECEDENTES
	public function m_registrar_antecedente($datos)
	{
		$data = array(
			'idcliente' => $datos['idcliente'],
			'idantecedente' => $datos['id'],
			'texto_otros' => empty($datos['texto_otros'])? NULL : $datos['texto_otros'],
			'createdAt' => date('Y-m-d H:i:s'),
			'updatedAt' => date('Y-m-d H:i:s')
		);
		return $this->db->insert('cliente_antecedente', $data);
	}
	public function m_editar_antecedentes_cliente($datos)
	{
		$data = array(
			'alergias_ia' => empty($datos['alergias_ia'])? NULL : $datos['alergias_ia'],
			'medicamentos' => empty($datos['medicamentos'])? NULL : $datos['medicamentos'],
			'antecedentes_notas' => empty($datos['antecedentes_notas'])? NULL : $datos['antecedentes_notas'],
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idcliente',$datos['idcliente']);
		return $this->db->update('cliente', $data);
	}
	public function m_anular_antecedentes_paciente($datos)
	{
		$data = array(
			'estado_clan' => 0,
			'updatedAt' => date('Y-m-d H:i:s')
		);
		$this->db->where('idcliente',$datos['idcliente']);
		$this->db->where("idantecedente IN (
			SELECT idantecedente
			FROM antecedente
			WHERE tipo = '" . $datos['tipo'] . "' )");
		return $this->db->update('cliente_antecedente', $data);
	}
	// HABITOS
	public function m_registrar_habito_alimentario($datos)
	{
		$data = array(
			'idcliente' => $datos['idcliente'],
			'idturno' => $datos['idturno'],
			'hora' => $datos['hora'],
			'texto_alimentos' => $datos['texto_alimentos'],
		);
		return $this->db->insert('cliente_habito_turno', $data);
	}
	public function m_editar_habito_alimentario($datos)
	{
		$data = array(
			// 'idcliente' => $datos['idcliente'],
			// 'idturno' => $datos['idturno'],
			'hora' => $datos['hora'],
			'texto_alimentos' => empty($datos['texto_alimentos'])? '' : $datos['texto_alimentos'],
		);
		$this->db->where('idclientehabitoturno',$datos['idclientehabitoturno']);
		return $this->db->update('cliente_habito_turno', $data);
	}
	public function m_registrar_habito_cliente($datos)
	{
		$data = array(
			'idcliente' => $datos['idcliente'],
			'actividad_fisica' => $datos['actividad_fisica']['id'],
			'frecuencia' => $datos['frecuencia']['id'],
			'detalle_act_fisica' => $datos['detalle_act_fisica'],
			'consumo_agua' => $datos['consumo_agua']['id'],
			'consumo_gaseosa' => $datos['consumo_gaseosa']['id'],
			'consumo_alcohol' => $datos['consumo_alcohol']['id'],
			'consumo_tabaco' => $datos['consumo_tabaco']['id'],
			'tiempo_suenio' => $datos['tiempo_suenio']['id'],
			'notas_generales' => $datos['notas_generales'],

		);
		return $this->db->insert('cliente_habito_gen', $data);
	}
	public function m_editar_habito_cliente($datos)
	{
		$data = array(
			'actividad_fisica' => $datos['actividad_fisica']['id'],
			'frecuencia' => $datos['frecuencia']['id'],
			'detalle_act_fisica' => $datos['detalle_act_fisica'],
			'consumo_agua' => $datos['consumo_agua']['id'],
			'consumo_gaseosa' => $datos['consumo_gaseosa']['id'],
			'consumo_alcohol' => $datos['consumo_alcohol']['id'],
			'consumo_tabaco' => $datos['consumo_tabaco']['id'],
			'tiempo_suenio' => $datos['tiempo_suenio']['id'],
			'notas_generales' => $datos['notas_generales'],
		);
		$this->db->where('idcliente',$datos['idcliente']);
		return $this->db->update('cliente_habito_gen', $data);
	}

}