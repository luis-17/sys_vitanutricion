<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_plan_plantilla extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
    public function m_cargar_plan_plantilla_cbo($datos)
    {
    	$this->db->select('pd.idplantilladieta, pd.nombre_pd, pd.observacion, pd.estado_pd');
    	$this->db->from('plantilla_dieta pd');
    	$this->db->where('tipo_pd',$datos['tipo']);
    	$this->db->where('estado_pd',1); // activo 
    	return $this->db->get()->result_array();
    } 
    public function m_cargar_plan_plantilla($datos)
    {
    	$this->db->select('t.idturno, t.descripcion_tu',FALSE);
		$this->db->select('d.iddia, d.nombre_dia',FALSE);
		$this->db->select('pdt.idplantilladieta, pdt.idplantilladietaturno, pdt.hora, pdt.indicaciones',FALSE);		
		$this->db->from('turno t, dia d'); 
		$this->db->join('plantilla_dieta_turno pdt', 't.idturno = pdt.idturno AND (d.iddia = pdt.iddia or pdt.iddia is null) AND pdt.idplantilladieta = '.$datos['plantilla']['id'],'left'); 
		//$this->db->join('plantilla_dieta pd', 'pdt.idplantilladieta = pd.idplantilladieta AND pd.estado_pd = 1','left'); 
		// $this->db->where('pdt.idplantilladieta',$datos['plantilla']['id']); // idplantilladieta
		$this->db->where('t.estado_tu',1); 
		// $this->db->where('pd.estado_pd',1); 
		$this->db->order_by('d.iddia ASC, t.idturno ASC'); 
		return $this->db->get()->result_array(); 
    }
    public function m_registrar_plan_plantilla($datos)
    {
    	$data = array(
			'nombre_pd' => $datos['nombre'],
			'observacion' => empty($datos['descripcion']) ? NULL : $datos['descripcion'],
			'tipo_pd' => $datos['tipo']
		);
		return $this->db->insert('plantilla_dieta', $data); 
    }
    public function m_registrar_plan_plantilla_turno($datos)
    {
    	$data = array(
 			'idplantilladieta' => $datos['idplantilladieta'],
 			'idturno' => $datos['idturno'],
 			'iddia' => empty($datos['iddia']) ? NULL : $datos['iddia'],
 			'hora' => $datos['hora'],
 			'indicaciones' => $datos['indicaciones'],
 		);
		return $this->db->insert('plantilla_dieta_turno', $data); 
    }
   
} 