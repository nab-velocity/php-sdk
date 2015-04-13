# Velocity PHP SDK Documentation 

## Installation

[Download ZIP](https://github.com/nab-velocity/php-client-sdk/archive/readme_changes.zip) and unzip to a web server.  The SDK alone can be found in the actual SDK folder.  The index.php file is an example payments form utilizing Velocity's transparent redirect.  The transparent redirect does a Verify (zero-dollar authorization) in order to tokenize the cardholder data.  This reduces the PCI-scope of the payments form by sending the payment data directly from the client's browser (via javascript) to the Velocity server.  It then redirects the payment form to the velocityClients.php page which utilizes the card data token to perform further payment operations exposed by the SDK.  

## Dependencies

Credentials for the velocity platform are required for configuration (IdentityToken, WorkflowId, ApplicationProfileId, & MerchantProfileId).  You should obtain these from your solutions consultant.

##	Tutorial

#### 1. Include PHP sdk

```
require_once '/sdk/Velocity.php';
```

#### 2. Instantiate processor

```
// ensure to keep this identity token secret
$identitytoken = "PHNhbWw6QXNzZXJ0aW9uIE1ham9yVmVyc2lvbj0iMSIgTWlub3JWZXJzaW9uPSIxIiBBc3NlcnRpb25JRD0iXzdlMDhiNzdjLTUzZWEtNDEwZC1hNmJiLTAyYjJmMTAzMzEwYyIgSXNzdWVyPSJJcGNBdXRoZW50aWNhdGlvbiIgSXNzdWVJbnN0YW50PSIyMDE0LTEwLTEwVDIwOjM2OjE4LjM3OVoiIHhtbG5zOnNhbWw9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjEuMDphc3NlcnRpb24iPjxzYW1sOkNvbmRpdGlvbnMgTm90QmVmb3JlPSIyMDE0LTEwLTEwVDIwOjM2OjE4LjM3OVoiIE5vdE9uT3JBZnRlcj0iMjA0NC0xMC0xMFQyMDozNjoxOC4zNzlaIj48L3NhbWw6Q29uZGl0aW9ucz48c2FtbDpBZHZpY2U+PC9zYW1sOkFkdmljZT48c2FtbDpBdHRyaWJ1dGVTdGF0ZW1lbnQ+PHNhbWw6U3ViamVjdD48c2FtbDpOYW1lSWRlbnRpZmllcj5GRjNCQjZEQzU4MzAwMDAxPC9zYW1sOk5hbWVJZGVudGlmaWVyPjwvc2FtbDpTdWJqZWN0PjxzYW1sOkF0dHJpYnV0ZSBBdHRyaWJ1dGVOYW1lPSJTQUsiIEF0dHJpYnV0ZU5hbWVzcGFjZT0iaHR0cDovL3NjaGVtYXMuaXBjb21tZXJjZS5jb20vSWRlbnRpdHkiPjxzYW1sOkF0dHJpYnV0ZVZhbHVlPkZGM0JCNkRDNTgzMDAwMDE8L3NhbWw6QXR0cmlidXRlVmFsdWU+PC9zYW1sOkF0dHJpYnV0ZT48c2FtbDpBdHRyaWJ1dGUgQXR0cmlidXRlTmFtZT0iU2VyaWFsIiBBdHRyaWJ1dGVOYW1lc3BhY2U9Imh0dHA6Ly9zY2hlbWFzLmlwY29tbWVyY2UuY29tL0lkZW50aXR5Ij48c2FtbDpBdHRyaWJ1dGVWYWx1ZT5iMTVlMTA4MS00ZGY2LTQwMTYtODM3Mi02NzhkYzdmZDQzNTc8L3NhbWw6QXR0cmlidXRlVmFsdWU+PC9zYW1sOkF0dHJpYnV0ZT48c2FtbDpBdHRyaWJ1dGUgQXR0cmlidXRlTmFtZT0ibmFtZSIgQXR0cmlidXRlTmFtZXNwYWNlPSJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcyI+PHNhbWw6QXR0cmlidXRlVmFsdWU+RkYzQkI2REM1ODMwMDAwMTwvc2FtbDpBdHRyaWJ1dGVWYWx1ZT48L3NhbWw6QXR0cmlidXRlPjwvc2FtbDpBdHRyaWJ1dGVTdGF0ZW1lbnQ+PFNpZ25hdHVyZSB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnIyI+PFNpZ25lZEluZm8+PENhbm9uaWNhbGl6YXRpb25NZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzEwL3htbC1leGMtYzE0biMiPjwvQ2Fub25pY2FsaXphdGlvbk1ldGhvZD48U2lnbmF0dXJlTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnI3JzYS1zaGExIj48L1NpZ25hdHVyZU1ldGhvZD48UmVmZXJlbmNlIFVSST0iI183ZTA4Yjc3Yy01M2VhLTQxMGQtYTZiYi0wMmIyZjEwMzMxMGMiPjxUcmFuc2Zvcm1zPjxUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjZW52ZWxvcGVkLXNpZ25hdHVyZSI+PC9UcmFuc2Zvcm0+PFRyYW5zZm9ybSBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMTAveG1sLWV4Yy1jMTRuIyI+PC9UcmFuc2Zvcm0+PC9UcmFuc2Zvcm1zPjxEaWdlc3RNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjc2hhMSI+PC9EaWdlc3RNZXRob2Q+PERpZ2VzdFZhbHVlPnl3NVZxWHlUTUh5NUNjdmRXN01TV2RhMDZMTT08L0RpZ2VzdFZhbHVlPjwvUmVmZXJlbmNlPjwvU2lnbmVkSW5mbz48U2lnbmF0dXJlVmFsdWU+WG9ZcURQaUorYy9IMlRFRjNQMWpQdVBUZ0VDVHp1cFVlRXpESERwMlE2ZW92T2lhN0pkVjI1bzZjTk1vczBTTzRISStSUGRUR3hJUW9xa0paeEtoTzZHcWZ2WHFDa2NNb2JCemxYbW83NUFSWU5jMHdlZ1hiQUVVQVFCcVNmeGwxc3huSlc1ZHZjclpuUytkSThoc2lZZW4vT0VTOUdtZUpsZVd1WUR4U0xmQjZJZnd6dk5LQ0xlS0FXenBkTk9NYmpQTjJyNUJWQUhQZEJ6WmtiSGZwdUlablp1Q2l5OENvaEo1bHU3WGZDbXpHdW96VDVqVE0wU3F6bHlzeUpWWVNSbVFUQW5WMVVGMGovbEx6SU14MVJmdWltWHNXaVk4c2RvQ2IrZXpBcVJnbk5EVSs3NlVYOEZFSEN3Q2c5a0tLSzQwMXdYNXpLd2FPRGJJUFpEYitBPT08L1NpZ25hdHVyZVZhbHVlPjxLZXlJbmZvPjxvOlNlY3VyaXR5VG9rZW5SZWZlcmVuY2UgeG1sbnM6bz0iaHR0cDovL2RvY3Mub2FzaXMtb3Blbi5vcmcvd3NzLzIwMDQvMDEvb2FzaXMtMjAwNDAxLXdzcy13c3NlY3VyaXR5LXNlY2V4dC0xLjAueHNkIj48bzpLZXlJZGVudGlmaWVyIFZhbHVlVHlwZT0iaHR0cDovL2RvY3Mub2FzaXMtb3Blbi5vcmcvd3NzL29hc2lzLXdzcy1zb2FwLW1lc3NhZ2Utc2VjdXJpdHktMS4xI1RodW1icHJpbnRTSEExIj5ZREJlRFNGM0Z4R2dmd3pSLzBwck11OTZoQ2M9PC9vOktleUlkZW50aWZpZXI+PC9vOlNlY3VyaXR5VG9rZW5SZWZlcmVuY2U+PC9LZXlJbmZvPjwvU2lnbmF0dXJlPjwvc2FtbDpBc3NlcnRpb24+";
$applicationprofileid = 14644;  
$merchantprofileid = "PrestaShop Global HC"; 
$workflowid = 2317000001;
$isTestAccount = true; 

try {
	$velocityProcessor = new Velocity_Processor($applicationprofileid, $merchantprofileid, $workflowid, $isTestAccount, $identitytoken);
} catch (Exception $e) {
	echo $e->getMessage();
}
```

Here we instantiate the processor in order to use it to process payments.  It takes all of our configuration parameters as well as a boolean indicating whether we are testing or not.  

#### 3. Use payment methods of processor 

To understand the Authorization & Capture process, please read our [Integration Guidance](http://docs.nabvelocity.com/hc/en-us/articles/202966458-Integration-Guidance-Transaction-Processing).  Also, the [tokenization process](http://docs.nabvelocity.com/hc/en-us/articles/202551793-Value-Added-Service-Provider-Guidelines-Tokenization) should be understood.  For each payment method taking cardholder data below (Authorize, AuthorizeAndCapture, ReturnUnlinked) there are four different payment methods:  Keyed data, swiped data, tokenized data, or encrypted data.  While swiped and keyed data are obtained directly from the card, tokenized data is obtained from a solution like the Transparent Redirect, or by a direct call to Verify via the SDK.  Utilizing tokens can help reduce your PCI scope.  Encrypted data is obtained from an encrypted card reader and can also greatly reduce your PCI scope.  Ask your Velocity representative for more information on any of these payment methods.

Below is an example of an authorize and capture with each of the different payment methods:

#### Authorize and capture with token              

```
try {
	
	$response = $velocityProcessor->authorizeAndCapture(array(
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

#### Authorize and capture with keyed data

```          
try {	
	$response = $velocityProcessor->authorizeAndCapture(array(
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
```

### Authorize and capture with swiped data

```          
	$response = $velocityProcessor->authorizeAndCapture(array(
		'amount' => 10.03, 
		'avsdata' => array(   
			'Street' => 'xyz', 
			'City' => 'cityname', 
			'StateProvince' => 'statecode', 
			'PostalCode' => 'postcode', 
			'Country' => 'countrycode three letter'
		 ),
		'carddata' => array(    
			'track2data' => '4012000033330026=09041011000012345678', 
			'cardtype' => 'Visa'
		),
		'order_id' => '629203',
	));
```

### Authorize and capture with encrypted data

Before doing the actualy transaction, you must re-instantiate your processor with the proper workflow id (provided by your velocity representative):

```
	$workflowid = 'BBBAAA0001';
	try {
		$velocityProcessor = new VelocityProcessor( $applicationprofileid, $merchantprofileid, $workflowid, $isTestAccount, $identitytoken );
	} catch (Exception $e) {
	    echo $e->getMessage();
	}
```

Then you can perform the transaction:

```          
	$response = $velocityProcessor->authorizeAndCapture(array(
		'amount' => 10.03, 
		'avsdata' => array(   
			'Street' => 'xyz', 
			'City' => 'cityname', 
			'StateProvince' => 'statecode', 
			'PostalCode' => 'postcode', 
			'Country' => 'countrycode three letter'
		 ),
		'p2pedata' => array(
			'SecurePaymentAccountData' => '576F2E197D5804F2B6201FB2578DCD1DDDC7BAE692FE48E9C368E678914233561FB953DF47E29F88',
			'EncryptionKeyId' => '9010010B257DC7000084'
		),
		'order_id' => '629203',
	));
```

#### Authorize method with token 

```            
try {
	
	$response = $velocityProcessor->authorize(array(
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
	
	$response = $velocityProcessor->authorize(array(
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
	
	$response = $velocityProcessor->capture(array(
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
	
	$response = $velocityProcessor->undo(array(
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
		
	$response = $velocityProcessor->adjust(array(
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
	$response = $velocityProcessor->returnById(array(
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
				
	$response = $velocityProcessor->returnUnlinked(array( 
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
				
	$response = $velocityProcessor->returnUnlinked(array( 
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
	
	$response = $velocityProcessor->verify(array(  									
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

####P2PE for Authorize:

    try{
        $response = $velocityProcessor->authorize(array( 
			'amount' => $cash, 
			'p2pedata' => array(
				'SecurePaymentAccountData' => $SecurePaymentAccountData,
				'EncryptionKeyId' => $EncryptionKeyId
			),
			'order_id' => '629203'
		));
     } catch (Exception $ex) {
		echo $e->getMessage();
    }

####P2PE for AuthorizeandCapture:

    try{
        $response = $velocityProcessor->authorizeAndCapture(array( 
			'amount' => $cash, 
			'p2pedata' => array(
				'SecurePaymentAccountData' => $SecurePaymentAccountData,
				'EncryptionKeyId' => $EncryptionKeyId
			),
			'order_id' => '629203'
		));
	} catch (Exception $ex) {
		echo $e->getMessage();
	}


####P2PE for ReturnUnlinked:

    try{
		$response = $velocityProcessor->returnUnlinked (
			'amount' => $cash, 
			'p2pedata' => array(
				'SecurePaymentAccountData' => $SecurePaymentAccountData,
				'EncryptionKeyId' => $EncryptionKeyId
			),
			'order_id' => '629203'
		));
    } catch (Exception $ex) {
		echo $e->getMessage();
    }


####CaptureAll Method:

    try{
		$velocityProcessor->captureAll();
    } catch (Exception $ex) {
		echo $e->getMessage();
    }



####QueryTransactionDetail Method:

    try {
		$response = $VelocityProcessor->queryTransactionsDetail(array(
			'querytransactionparam' => array(
			'Amounts' => array(10.00),
			'ApprovalCodes' => array('VI0000'),
			'BatchIds' => array('0539'),
			'CaptureDateRange' => array(
				'EndDateTime' => '2015-03-17 02:03:40',
				'StartDateTime' => '2015-03-13 02:03:40'
			),
			'CaptureStates' => array('ReadyForCapture'),
			'CardTypes' => array('Visa'),
			'MerchantProfileIds' => array('PrestaShop Global HC'),
			'OrderNumbers' => array('629203'),
			'ServiceIds' => array('2317000001'),
			'ServiceKeys' => array('FF3BB6DC58300001'),
			'TransactionClassTypePairs' => array( array(
				'TransactionClass' => 'CREDIT',
				'TransactionType' => 'AUTHONLY'
				)
			),
			'TransactionDateRange' => array(
				'EndDateTime' => '2015-03-17 02:03:40',
				'StartDateTime' => '2015-03-13 02:03:40'
			),
			'TransactionIds' =>             array('9B935E96763F43C3866F603319BE7B52'),
			'TransactionStates' => array('Authorized')                        
			),
			'PagingParameters' => array(
				'page' => '0',
				'pagesize' => '3'
			),
		));
    } catch(Exception $e) {
	    echo $e->getMessage();
    }
  