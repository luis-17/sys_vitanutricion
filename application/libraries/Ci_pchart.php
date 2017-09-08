<?php
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	class Ci_pchart {
		public function __construct(){
			include("pchart/class/pDraw.class.php"); 
			include("pchart/class/pImage.class.php"); 
			include("pchart/class/pData.class.php");	    
		}
	}

?>