# Velocity PHP SDK Documentation 

## Installation

[Download ZIP](https://github.com/nab-velocity/php-client-sdk/archive/readme_changes.zip) and unzip to a web server.  The SDK alone can be found in the actual SDK folder.  The index.php file is an example payments form utilizing Velocity's transparent redirect.  The transparent redirect does a Verify (zero-dollar authorization) in order to tokenize the cardholder data.  This reduces the PCI-scope of the payments form by sending the payment data directly from the client's browser (via javascript) to the Velocity server.  It then redirects the payment form to the velocityClients.php page which utilizes the card data token to perform further payment operations exposed by the SDK.  

## Dependencies

Credentials for the velocity platform are required for configuration (IdentityToken, WorkflowId, ApplicationProfileId, & MerchantProfileId).  You should obtain these from your solutions consultant.

##	Tutorial

#### 1. Include PHP sdk

```
require_once 'Velocity.php';
```

#### 2. Instantiate processor

```
try {
	$velocity_processor = new Velocity_Processor($applicationprofileid, $merchantprofileid, $workflowid, $isTestAccount, $identitytoken);
} catch (Exception $e) {
	echo $e->getMessage();
}
```

Here we instantiate the processor in order to use it to process payments.  It takes all of our configuration paramerters as well as a boolean indicating whether we are testing or not.  Test accounts go through Velocity's certification environment, while non-test accounts imply a live card-holder data environment.

#### 3. Use payment methods of processor 

To understand the Authorization & Capture process, please read our [Integration Guidance](http://docs.nabvelocity.com/hc/en-us/articles/202966458-Integration-Guidance-Transaction-Processing).  Also, the [tokenization process](http://docs.nabvelocity.com/hc/en-us/articles/202551793-Value-Added-Service-Provider-Guidelines-Tokenization) should be understood.  For each payment method taking cardholder data below (Authorize, AuthorizeAndCapture, ReturnUnlinked) an example is given both with and without token.  Tokens are obtained from a solution like the Tranpsarent Redirect, or by a direct call to Verify via the SDK.  Utilizing tokens can help reduce your PCI scope.

#### Authorize and capture with token              

```
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
```

#### Authorize and capture without token 

```          
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
```

#### Authorize method with token 

```            
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
```

#### Authorize method without token  

```
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
```

#### Capture                

```
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
```

#### Undo (Void/Reversal)                   

```
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
```

#### Adjust        

```     
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
```	

#### ReturnById            

```
try {
	$response = $velocity_processor->returnById(array(
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
```

#### ReturnUnlinked with token: 

```             
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
```
 
#### ReturnUnlinked without token

```           
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
```

#### Verify method            

```   
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
```   