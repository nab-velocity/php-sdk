
Velocity PHP SDK Documentations 



1.	Installation: Download and unzip.

 

Dependencies:

Velocity credentials are required for configuration (identitytoken, workflowid, applicationprofileid, merchantprofileid ).

2.	How to use the PHP SDK

Include PHP sdk:

	require_once 'Velocity.php';

Create Velocity_Processer class object :

try {
	$velocity_processor = new Velocity_Processor( $applicationprofileid, $merchantprofileid, $workflowid, $isTestAccount, $identitytoken );
} catch (Exception $e) {
	echo $e->getMessage();
}




Note: 
$response is an array/object for success/failure.
Try, catch is mandatory for error handling.

















Verify method                

   
try {
	
	$response = $velocity_processor->verify(array(  									
		'avsdata' => array(   
			'Street' => 'xyz', 
			'City' => 'cityname', 
			'StateProvince' => 'statecode', 
			'PostalCode' => 'postcode', 
			'Country' => 'countrycode three letter'
		),
		'carddata' => array(    
			'cardowner' => 'Jane Doe', 
			'cardtype' => 'Visa', 
			'pan' => '4012888812348882', 
			'expire' => '1215', 
			'cvv' => '123'
		)										
	)); 

	if (isset($response['Status']) && $response['Status'] == 'Successful') {
		echo 'Verify Successful!</br>';
		echo 'PostalCodeResult: ' . $response['AVSResult']['PostalCodeResult'] . '</br>'; 
		echo 'CVResult: ' . $response['CVResult']; 
	} else {
		// some error
		print_r($response);
	}

} catch(Exception $e) {
	echo $e->getMessage();
}
         




















Authorize and capture with token Method              

try {
	
	$response = $velocity_processor->authorizeAndCapture(array(
		'amount' => 10.03, 
		'avsdata' => array(   
			'Street' => 'xyz', 
			'City' => 'cityname', 
			'StateProvince' => 'statecode', 
			'PostalCode' => 'postcode', 
			'Country' => 'countrycode three letter'
		),
		'token' => $paymentAccountDataToken, 								      
		'order_id' => '629203',
	));
	
	if (isset($response['Status']) && $response['Status'] == 'Successful') {
		echo 'AuthorizeAndCapture Successful!</br>';
		echo 'Masked PAN: ' . $response['MaskedPAN'] . '</br>';
		echo 'Approval Code: ' . $response['ApprovalCode'] . '</br>';
		echo 'Amount: ' . $response['Amount'] . '</br>'; 
		echo 'TransactionId: ' . $response['TransactionId']; 
	} else {
		// some error
		print_r($response);
	}
	
	$authCapTransactionid = $response['TransactionId'];
	
} catch(Exception $e) {
	echo $e->getMessage(); 
} 

Note: $paymentAccountDataToken is obtained from a previous transaction’s response (usually verify) and represents a credit card.. 
For Authorize and capture without token Method:   
           
try {	

	$response = $velocity_processor->authorizeAndCapture(array(
		'amount' => 10.03, 
		'avsdata' => array(   
			'Street' => 'xyz', 
			'City' => 'cityname', 
			'StateProvince' => 'statecode', 
			'PostalCode' => 'postcode', 
			'Country' => 'countrycode three letter'
		 ),
		'carddata' => array(    
			'cardowner' => 'Jane Doe', 
			'cardtype' => 'Visa', 
			'pan' => '4012888812348882', 
			'expire' => '1215', 
			'cvv' => '123'
		),
		'order_id' => '629203',
	));

	if (isset($response['Status']) && $response['Status'] == 'Successful') {
		echo 'AuthorizeAndCapture Successful!</br>';
		echo 'Masked PAN: ' . $response['MaskedPAN'] . '</br>';
		echo 'Approval Code: ' . $response['ApprovalCode'] . '</br>';
		echo 'Amount: ' . $response['Amount'] . '</br>'; 
		echo 'TransactionId: ' . $response['TransactionId']; 
	} else {
		// some error
		print_r($response);
	}
		
	$authCapTransactionid = $response['TransactionId'];

} catch(Exception $e) {
	echo $e->getMessage(); 
}
















Authorize method with Token:    
               
try {
	
	$response = $velocity_processor->authorize(array(
		'amount' => 10,  
		'avsdata' => array(   
			'Street' => 'xyz', 
			'City' => 'cityname', 
			'StateProvince' => 'statecode', 
			'PostalCode' => 'postcode', 
			'Country' => 'countrycode three letter'
		),
		'token' => $paymentAccountDataToken,
		'order_id' => '629203'
	)); 
	
	if (isset($response['Status']) && $response['Status'] == 'Successful') {
		echo 'Authorize Successful!</br>';
		echo 'Masked PAN: ' . $response['MaskedPAN'] . '</br>';
		echo 'Approval Code: ' . $response['ApprovalCode'] . '</br>';
		echo 'Amount: ' . $response['Amount'] . '</br>'; 
		echo 'TransactionId: ' . $response['TransactionId']; 
	} else {
		// some error
		print_r($response);
	}

	$authTransactionid = $response['TransactionId'];
		
} catch (Exception $e) {
	echo $e->getMessage();	
}        























Authorize method without Token:    
               
try {
	
	$response = $velocity_processor->authorize(array(
		'amount' => 10,  
		'avsdata' => array(   
			'Street' => 'xyz', 
			'City' => 'cityname', 
			'StateProvince' => 'statecode', 
			'PostalCode' => 'postcode', 
			'Country' => 'countrycode three letter'
			),
		'carddata' => array(    
			'cardowner' => 'Jane Doe', 
			'cardtype' => 'Visa', 
			'pan' => '4012888812348882', 
			'expire' => '1215', 
			'cvv' => '123'
		),
		'order_id' => '629203'
	)); 
 
	if (isset($response['Status']) && $response['Status'] == 'Successful') {
		echo 'Authorize Successful!</br>';
		echo 'Masked PAN: ' . $response['MaskedPAN'] . '</br>';
		echo 'Approval Code: ' . $response['ApprovalCode'] . '</br>';
		echo 'Amount: ' . $response['Amount'] . '</br>'; 
		echo 'TransactionId: ' . $response['TransactionId']; 
	} else {
		// some error
		print_r($response);
	}
	
	$authTransactionid = $response['TransactionId'];
		
} catch (Exception $e) {
	echo $e->getMessage();
}      












Capture method:                   

try {
	
	$response = $velocity_processor->capture(array(
		'amount' => 6.03, 
		'TransactionId' => $authTransactionid
	));	

	if (isset($response['Status']) && $response['Status'] == 'Successful') {
		echo 'Capture Successful!</br>';
		echo 'Amount: ' . $response['TransactionSummaryData']['NetTotals']['NetAmount']; 
	} else {
		// some error
		print_r($response);
	}

	$captxnid = $response['TransactionId'];
		
} catch(Exception $e) {
	echo $e->getMessage();
}


Note : $authTransactionid is obtained from authorize response.





Void(Undo) method:                   

try {
	
	$response = $velocity_processor->undo(array(
		'TransactionId' => $adjusttxnid
	));
	
	if (isset($response['Status']) && $response['Status'] == 'Successful') {
		echo 'Undo Successful!</br>';
		echo 'TransactionId: ' . $response['TransactionId'] . '</br></br>'; 
	} else {
		// some error
		print_r($response);
	}
							   		
} catch (Exception $e) {
	echo $e->getMessage();
} 




Adjust method:            
       
try {
		
	$response = $velocity_processor->adjust(array(
		'amount' => 3.01, 
		'TransactionId' => $captxnid
	));
	if (isset($response['Status']) && $response['Status'] == 'Successful') {
		echo 'Adjust Successful!</br>';
		echo 'Amount: ' . $response['Amount'] . '</br></br>'; 
		$adjusttxnid = $response['TransactionId'];
	} else {
		// some error
		print_r($response);
	}
		
} catch (Exception $e) {
	echo $e->getMessage();
}
	
	





ReturnById method:             
      
try {
	$response = $velocity_processor->returnById( array(
		'amount' => 5.03, 
		'TransactionId' => $authCapTransactionid
	));
	
	if (isset($response['Status']) && $response['Status'] == 'Successful') {
		echo 'ReturnById Successful!</br>';
		echo 'ApprovalCode: ' . $response['ApprovalCode'] . '</br></br>'; 
	} else {
		// some error
		print_r($response);
	}

} catch (Exception $e) {
	echo $e->getMessage();
}

ReturnUnlinked method with token: 
               
try {
				
	$response = $velocity_processor->returnUnlinked(array( 
		'amount' => 1.03, 
		'token' => $paymentAccountDataToken, 
		'order_id' => '629203'
	));
		
	if (isset($response['Status']) && $response['Status'] == 'Successful') {
		echo 'ReturnUnlinked Successful!</br>';
		echo 'ApprovalCode: ' . $response['ApprovalCode'] . '</br></br>'; 
	} else {
		// some error
		print_r($response);
	}

} catch (Exception $e) {
	echo $e->getMessage();
}
 
ReturnUnlinked method without token: 
               
try {
				
	$response = $velocity_processor->returnUnlinked(array( 
		'amount' => 1.03, 
		'carddata' => array(    
			'cardowner' => 'Jane Doe', 
			'cardtype' => 'Visa', 
			'pan' => '4012888812348882', 
			'expire' => '1215', 
			'cvv' => '123'
		),
		'order_id' => '629203'
	));
		
	if (isset($response['Status']) && $response['Status'] == 'Successful') {
		echo 'ReturnUnlinked Successful!</br>';
		echo 'ApprovalCode: ' . $response['ApprovalCode'] . '</br></br>'; 
	} else {
		// some error
		print_r($response);
	}

} catch (Exception $e) {
	echo $e->getMessage();
}
