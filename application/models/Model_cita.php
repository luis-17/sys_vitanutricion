<?php
class Model_cita extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
 	// ACCESO AL SISTEMA
	public function m_cargar_citas($datos){ 
		$this->db->select('ci.idcita, ci.idcliente, ci.idprofesional, ci.idubicacion, ci.fecha, ci.hora_desde, ci.hora_hasta, ci.estado_ci',FALSE);
		$this->db->select('cli.cod_historia_clinica, cli.nombre, cli.apellidos, cli.sexo, cli.estatura, cli.fecha_nacimiento, cli.email',FALSE);
		$this->db->select("UPPER(CONCAT(pro.nombre, ' ',pro.apellidos)) AS profesional",FALSE);
		$this->db->select('ub.descripcion_ub, ub.idubicacion',FALSE);

		$this->db->select('at.idatencion, at.fecha_atencion, at.diagnostico_notas, at.tipo_dieta, at.indicaciones_dieta',FALSE);
		//$this->db->select('adt.idatenciondietaturno',FALSE);

		$this->db->from('cita ci');
		$this->db->join('cliente cli', 'cli.idcliente = ci.idcliente AND cli.estado_cl = 1');
		$this->db->join('profesional pro', 'pro.idprofesional = ci.idprofesional AND pro.estado_pf = 1');
		$this->db->join('ubicacion ub', 'ub.idubicacion = ci.idubicacion AND ub.estado_ub = 1');

		$this->db->join('atencion at', 'at.idcita = ci.idcita AND at.estado_atencion = 1', 'left');
		//$this->db->join('atencion_dieta_turno adt', 'adt.idatencion = at.idatencion AND adt.estado_dt = 1', 'left');
		$this->db->where('ci.estado_ci <>', 0);

		if(!empty($datos['profesional']['id'])){
			$this->db->where('ci.idprofesional', $datos['profesional']['id']);
		}

		return $this->db->get()->result_array();
	}

	public function m_cuenta_citas($fecha, $hora_desde){
		$this->db->select('count(*) AS contador');
		$this->db->from('cita ci');
		$this->db->where('ci.estado_ci', 1);
		$this->db->where('ci.fecha', $fecha);
		$this->db->where('ci.hora_desde', $hora_desde);
		
		$fData = $this->db->get()->row_array();
		return $fData['contador'];
	}
	public function m_cargar_proximas_citas($datos)
	{
		$this->db->select('ci.idcita, ci.idcliente, ci.idprofesional, ci.idubicacion, ci.fecha, ci.hora_desde, ci.hora_hasta, ci.estado_ci',FALSE);
		$this->db->select('cli.cod_historia_clinica, cli.nombre, cli.apellidos, cli.sexo, cli.estatura, cli.fecha_nacimiento, cli.email',FALSE);
		$this->db->select('ub.descripcion_ub, ub.idubicacion',FALSE);
		$this->db->from('cita ci');
		$this->db->join('cliente cli', 'cli.idcliente = ci.idcliente AND cli.estado_cl = 1');
		$this->db->join('ubicacion ub', 'ub.idubicacion = ci.idubicacion AND ub.estado_ub = 1');
		$this->db->where('ci.estado_ci <>', 0);
		$this->db->where('ci.fecha > CURDATE()'); // mayor que hoy 
		$this->db->order_by('ci.fecha ASC');
		$this->db->order_by('ci.hora_desde ASC');
		$this->db->limit($datos['numeroCitas']);
		return $this->db->get()->result_array();
	}
	public function m_registrar($data){
		return $this->db->insert('cita', $data);
	}	

	public function m_actualizar($data, $id){
		$this->db->where('idcita', $id);
		return $this->db->update('cita', $data);
	}

	public function m_anular($id){
		$data = array(
			'estado_ci' => 0,
			'updatedat' => date('Y-m-d H:i:s')
		);
		$this->db->where('idcita', $id);
		return $this->db->update('cita', $data);
	}

	public function m_consulta_cita($idcita){
		$this->db->select('ci.idcita, ci.idcliente, ci.idprofesional, ci.idubicacion, ci.fecha, ci.hora_desde, ci.hora_hasta, ci.estado_ci',FALSE);
		$this->db->select('at.idatencion, at.createdat AS fecha_atencion, at.diagnostico_notas',FALSE);

		$this->db->from('cita ci');
		$this->db->join('atencion at', 'at.idcita = ci.idcita AND at.estado_atencion = 1', 'left');

		$this->db->where('ci.estado_ci <>', 0);
		$this->db->where('ci.idcita =', $idcita);
		return $this->db->get()->row_array();
	}

	public function m_act_fecha_cita($datos){
		$data = array(
			'fecha' => $datos['fecha'],
			'updatedat' => date('Y-m-d H:i:s')
		);
		$this->db->where('idcita', $datos['idcita']);
		return $this->db->update('cita', $data);
	}
}
?>