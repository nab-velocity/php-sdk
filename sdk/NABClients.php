<?php
/* 
 * include all helping and supporting classes and methods.
 */
 
require_once dirname(__FILE__) . '/lib/Velocity.php';

/*
 * include configuration file to use the value set from this file. 
 */
require_once dirname(__FILE__) . '/configuration.php';
 
/* 
 * received data from transparent redirect, check is available and decode then convert into array. 
 */
if (isset($_POST['TransactionToken']) && $_POST['TransactionToken'] != '') {
 
	$verify_array = json_decode(base64_decode($_POST['TransactionToken'])); // base64 decode the transactiontoken and then decode json string into array.

	/* 
	 * Display response of transparent redirect here.
	 */
	echo 'Message: Display response of transparent redirect! <br>';
	echo '<pre>'; print_r($verify_array); echo '</pre>';
	
	$avsdata = isset($verify_array->CardSecurityData->AVSData) ? $verify_array->CardSecurityData->AVSData : null;
	$paymentAccountDataToken = isset($verify_array->PaymentAccountDataToken) ? $verify_array->PaymentAccountDataToken : null;
	
	/* create object of processor class */
	try {
		$obj_processor = new Velocity_Processor(VelocityCon::$identitytoken);
	} catch (Exception $e) {
	    echo $e->getMessage();
	}
	
	/* create object of Transaction class */
	try {
		$obj_transaction = new Velocity_Transaction();
	} catch (Exception $e) {
		echo $e->getMessage();
	}	

	/*
	 * convert standard class array into normat php array. 
	 */
	$avsData = array();
	if($avsdata != null) {
		foreach($avsdata as $key => $value) {
			$avsData[$key] = $value; 
		}
	}
	
	/*
	 * carddata optional for use SDK only without transparent redirect. 
	 * Note: array key must be not change.  
	 */
	$cardData = array('cardtype' => '', 'pan' => '', 'expire' => '', 'track1data' => '', 'tarck2data' => ''); 
	 
	/* *****************************************************Authorizeandcapture************************************************************************* */
	
	try {
				
		$res_authandcap = $obj_processor->authorizeAndCapture( array(
																		'amount' => 10.03, 
																		'token' => $paymentAccountDataToken, 
																		'avsdata' => $avsData, 
																		'carddata' => $cardData, 
																		'invoice_no' => '',
																		'order_id' => '629203'
																		)
																);
		
		/* 
		 * Display response of authorizeandcapture request here.
		 */ 
		 
		echo 'Message: Display response of authorizeandcapture request! <br>';
		echo '<pre>'; print_r($res_authandcap); echo '</pre>';
		
		if ( gettype($res_authandcap) == 'object') { // stop execution if return array object.
			die;
		}
		
    } catch(Exception $e) {
		echo $e->getMessage();
	}
	
	/* *****************************************************Authorize***************************************************************************** */
	
	/* try {
	
		$res_auth = $obj_processor->authorize( array(
														'amount' => 10, 
														'token' => $paymentAccountDataToken, 
														'avsdata' => $avsData, 
														'carddata' => $cardData,
														'invoice_no' => '',
														'order_id' => '629203'
														)
												);  */
		
		/* 
		* Display response of authorize request here.
		*/

		/* echo 'Message: Display response of authorize request! <br>';
		echo '<pre>'; print_r($res_auth); echo '</pre>';
		if ( gettype($res_auth) == 'object') { // stop execution if return array object.
			die;
		}
		
	} catch (Exception $e) {
	
		echo $e->getMessage();
		
	}  */
	
	/* create object of transaction class
	$transactionid = isset($res_auth['BankcardTransactionResponsePro']['TransactionId']) ? $res_auth['BankcardTransactionResponsePro']['TransactionId'] : null;
	 */
	/* *****************************************************Capture******************************************************************************** */
	
	/* try {
	
		$res_capture = $obj_transaction->capture( array(
														'amount' => 1.03, 
														'TransactionId' => $transactionid
														)
												);
		$captxnid =isset($res_capture['BankcardCaptureResponse']['TransactionId']) ? $res_capture['BankcardCaptureResponse']['TransactionId'] : null; */
		/* 
		* Display response of capture request here.
		*/
		/* echo 'Message: Display response of capture request! <br>';
		echo '<pre>'; print_r($res_capture); echo '</pre>';
		if ( gettype($res_capture) == 'object') { // stop execution if return array object.
			die;
		}
		
	} catch(Exception $e) {
		echo $e->getMessage();
	} */
	
	/* *****************************************************Adjust******************************************************************************** */
		
	/* try {
		$captxnid = isset($captxnid) ? $captxnid : null; */
		/* $res_adjust = $obj_transaction->adjust( array(
													'amount' => 3.01, 
													'TransactionId' => $captxnid
													)
												); */
		
		/* 
		 * Display response of adjust request here.
		 */
		//echo 'Message: Display response of adjust request! <br>';
		//echo '<pre>'; print_r($res_adjust); echo '</pre>';
		/* if ( gettype($res_adjust) == 'object') { // stop execution if return array object.
			die;
		}
	} catch (Exception $e) {
		echo $e->getMessage();
	} */
	
	/* *****************************************************Undo******************************************************************************** */
	
	/* try {
		$captxnid = isset($captxnid) ? $captxnid : null; */
		/* $res_undo = $obj_transaction->undo( array(
												  'TransactionId' => $transactionid
												   ) 
										   ); */
		/* 
		 *  Display response of undo request here.
		 */
		//echo 'Message: Display response of undo request! <br>';
		//echo '<pre>'; print_r($res_undo); echo '</pre>'; 
		/* if ( gettype($res_undo) == 'object') { // stop execution if return array object.
			die;
		}
		
	} catch (Exception $e) {
		echo $e->getMessage();
	} */
	
	/* *****************************************************ReturnById************************************************************************* */
	
	try {
		//$captxnid = isset($captxnid) ? $captxnid : null;	
        $captxnid = $res_authandcap['BankcardTransactionResponsePro']['TransactionId'];		
		$res_returnbyid = $obj_transaction->returnById( array(
															  'amount' => 5.03, 
															  'TransactionId' => $captxnid 
															  ) 
													  );
		
		/* 
		 * Display response of ReturnById request here.
		 */ 
		echo 'Message: Display response of ReturnById request! <br>';
		echo '<pre>'; print_r($res_returnbyid); echo '</pre>'; 
		if ( gettype($res_returnbyid) == 'object') { // stop execution if return array object.
			die;
		}
		
    } catch (Exception $e) {
		echo $e->getMessage();
	}  
	
	/* *****************************************************ReturnUnlinked************************************************************************* */
	
	/* try {
				
		$res_returnUnlinked = $obj_transaction->returnUnlinked( array( 
																	  'amount' => 1.03, 
																	  'token' => $paymentAccountDataToken, 
																	  'avsdata' => $avsData, 
																	  'carddata' => $cardData, 
																	  'invoice_no' => '',
																	  'order_id' => '629203'
																	   ) 
															    ); */
		
		/* 
		 * Display response of ReturnUnlinked request here.
		 */ 
		/* echo 'Message: Display response of ReturnUnlinked request! <br>';
		echo '<pre>'; print_r($res_returnUnlinked); echo '</pre>'; 
		if ( gettype($res_returnUnlinked) == 'object') { // stop execution if return array object.
			die;
		}
		
    } catch (Exception $e) {
		echo $e->getMessage();
	} */
	
} else {
    echo Velocity_Message::$descriptions['errtransparentjs'];
}

?>