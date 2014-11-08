<?php

class NorthAmericanBancard_Config 
{
	public $config = array();
	
	function __construct(){
		   
	   $this->config['applicationprofileid'] = 14644;
	   
	   $this->config['merchantprofileid'] = "PrestaShop Global HC";
	   
	   $this->config['baseurl'] = "https://api.cert.nabcommerce.com/REST/2.0.18/";
	   
	   $this->config['debug'] = false; // just for test to display the response. 
	
	}
}
$configobj = new NorthAmericanBancard_Config();