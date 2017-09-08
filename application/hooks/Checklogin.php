<?php 
class Checklogin {
  var $CI; 
  function __construct() {
    $this->CI =& get_instance();  
  }          

  function session_check() { 
    //print_r($this->CI);
    if($this->CI->uri->uri_string != 'acceso'){      
      if( !$this->CI->session->has_userdata( 'sess_vp_'.substr(base_url(),-8,7) ) || 
        empty($this->CI->session->userdata('sess_vp_'.substr(base_url(),-8,7))['idusuario']) ){
        $arrData['datos'] = [];
        $arrData['flag'] = 'session_expired';
        $arrData['message'] = 'La sesión ha finalizado, debe acceder nuevamente.';
        echo json_encode($arrData);
        exit;
      }
    }
  } 
}
