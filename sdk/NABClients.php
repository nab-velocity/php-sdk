<?php
/* 
 * include all helping and supporting classes and methods.
 */
require_once dirname(__FILE__) . '/lib/NorthAmericanBancard.php';

/*
 * include configuration file to use the value set from this file. 
 */
require_once dirname(__FILE__) . '/configuration.php';
/*
 * setups is the methods to set the value at the time of configuration 
 */
NorthAmericanBancard::setups($configobj->config['applicationprofileid'], $configobj->config['merchantprofileid'], $configobj->config['baseurl'], $configobj->config['debug']);
 
/* 
 * received data from transparent redirect, check is available and decode then convert into array. 
 */
if(isset($_POST['TransactionToken']) && $_POST['TransactionToken'] != '') {
 
	$verify_detail = base64_decode($_POST['TransactionToken']);
	$verify_array = json_decode($verify_detail);

	/* 
	* Display response of transparent redirect here.
	*/
	echo 'Message: Display response of transparent redirect! <br>';
	echo '<pre>'; print_r($verify_array); echo '</pre>';
	
	if(isset($verify_array->sessiontoken) && isset($verify_array->CardSecurityData->workflowid) && isset($verify_array->CardSecurityData->AVSData)) {
	   $sessiontoken = $verify_array->sessiontoken;
	   $workflowid = $verify_array->CardSecurityData->workflowid;
	   $avsdata = $verify_array->CardSecurityData->AVSData;
	}
	
	if(isset($sessiontoken)) {
		$obj_processor = new NorthAmericanBancard_Processor($sessiontoken);
	} else {
	    throw new Exception(NorthAmericanBancard_Message::$descriptions['errauthsesstoken']);
	}
	
	if(isset($workflowid) && isset($avsdata)) {
		
		/*
		 * convert standard class array into normat. 
		 */
		$avsData = array();
		foreach($avsdata as $key => $value) {
			$avsData[$key] = $value; 
		}
		
		$res_auth = $obj_processor->authorize(0.02, array_merge($avsData, array('workflowid' => $workflowid)));
		
    } else {
		throw new Exception(NorthAmericanBancard_Message::$descriptions['erraurhavswflid']);
	}
	
	/* 
	* Display response of authorize request here.
	*/
	echo 'Message: Display response of authorize request! <br>';
	echo '<pre>'; print_r($res_auth); echo '</pre>';

	if(isset($res_auth->attributes)) {
		$obj_transaction = new NorthAmericanBancard_Transaction($res_auth->attributes);
	} else {
		throw new Exception(NorthAmericanBancard_Message::$descriptions['erraurhattraray']);
	}
	
	if(isset($sessiontoken) && isset($workflowid) && isset($res_auth->attributes['TransactionId'])) {
		$res_capture = $obj_transaction->capture(0.00, array("workflowid" => $workflowid, "sessiontoken" => $sessiontoken, "method" => "capture", "TransactionId" => $res_auth->attributes['TransactionId']) );
	} else {
		throw new Exception(NorthAmericanBancard_Message::$descriptions['errcapsesswfltransid']);
	}
	
	/* 
	* Display response of capture request here.
	*/
	echo 'Message: Display response of capture request! <br>';
	echo '<pre>'; print_r($res_capture); echo '</pre>';
	
	if(isset($sessiontoken) && isset($workflowid) && isset($res_auth->attributes['TransactionId'])) {
		$res_adjust = $obj_transaction->adjust(0.03, array("workflowid" => $workflowid, "sessiontoken" => $sessiontoken, "method" => "adjust", "TransactionId" => $res_auth->attributes['TransactionId']) );
	} else {
		throw new Exception(NorthAmericanBancard_Message::$descriptions['erradjustsesswfltransid']);
	}
	
	/* 
	* Display response of adjust request here.
	*/
	echo 'Message: Display response of adjust request! <br>';
	echo '<pre>'; print_r($res_adjust); echo '</pre>';

} else {
    throw new Exception(NorthAmericanBancard_Message::$descriptions['errtransparentjs']);
}

?>