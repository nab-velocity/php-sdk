
Velocity PHP SDK Documentations 



1.	Installation: For installation first download the source code and upload source code on your server

 

Dependencies:
The Velocity PHP SDK has the following dependencies which are required.
1.	Server must be PHP environment.
2.	Velocity credential is also required for configuration (like identitytoken, workflowid, applicationprofileid, merchantprofileid ).




2.	How to use the PHP SDK
        
•	Create Velocity_Processer class object :
    
    try {
    $velocity_processor = new Velocity_Processor( $applicationprofileid, $merchantprofileid, $workflowid, $isTestAccount,$identitytoken = null, $sessionToken );
    } catch (Exception $e) {
	 echo $e->getMessage();
    }

Note: 
•	$respone is an array/object for success/failure.
•	Try, catch is mandatory for error handling.


•	Verify method                

   
     try {
	
		$response = $velocity_processor->verify( array(  								        	'avsdata' => array( 'Street' => 'xyz',                                                            'City' => 'cityname', 
                                         'StateProvince' => 'statecode', 
                                         'PostalCode' => 'postcode', 
                                         'Country' => 'countrycode three letter'
                                    ),
				      'carddata' => array( 'cardowner' => 'Jane Doe', 
                                           'cardtype' => 'Visa', 
                                           'pan' => '4012888812348882', 
                                           'expire' => '1215', 
                                           'cvv' => '123'                                                                     )
						)); 

    } catch(Exception $e) {
		echo $e->getMessage();
    }

•	Authorize and capture with token Method:                   

    try {	
	       $response = $velocity_processor->authorizeAndCapture( array(
	                     'amount' => 10.03, 
		                 'avsdata' => array('Street' => 'xyz',                                                             'City' => 'cityname',                                                          'StateProvince' => 'statecode',                                                'PostalCode' => 'postcode', 
                                            'Country' => 'countrycode three letter'                       ),
						   'token' => $paymentAccountDataToken, 								          'order_id' => '629203',
							)
						);
		$authCapTransactionid = $response['TransactionId'];
		
    } catch(Exception $e) {
		echo $e->getMessage(); 
    } 

Note: $paymentAccountDataToken is get from verify response. 
For Authorize and capture without token Method:   
           
       try {	
	       $response = $velocity_processor->authorizeAndCapture( array(
							      'amount' => 10.03, 
						          'avsdata' => array(   'Street' => 'xyz', 
                                      'City' => 'cityname', 
                                      'StateProvince' => 'statecode', 
                                      'PostalCode' => 'postcode', 
                                      'Country' => 'countrycode three letter'
                                    ),
                                 'carddata' => array( 'cardowner' => 'Jane Doe', 
                                 'cardtype' => 'Visa', 
                                 'pan' => '4012888812348882', 
                                 'expire' => '1215', 
                                 'cvv' => '123'
                              ),
							     'order_id' => '629203',
							)
						 );
		$authCapTransactionid = $response['TransactionId'];
		
    } catch(Exception $e) {
		echo $e->getMessage(); 
    }

•	Authorize method with Token:    
               
          try {
	
	        	$response = $velocity_processor->authorize( array(
								'amount' => 10,  
                                'avsdata' => array(   'Street' => 'xyz', 
                                'City' => 'cityname', 
                                'StateProvince' => 'statecode', 
                                'PostalCode' => 'postcode', 
                                'Country' => 'countrycode three letter'
                            ),
								'token' => $paymentAccountDataToken,
								'order_id' => '629203'
								)
							); 
		$authTransactionid = $response['TransactionId'];
	} catch (Exception $e) {
		echo $e->getMessage(); die;	
	}        





•	Authorize method without Token:    
               
          try {
	
		$response = $velocity_processor->authorize( array(
							      'amount' => 10,  
						          'avsdata' => array( 'Street' => 'xyz', 
                                     'City' => 'cityname', 
                                     'StateProvince' => 'statecode', 
                                     'PostalCode' => 'postcode', 
                                     'Country' => 'countrycode three letter'
                                   ),
                                 'carddata' => array( 'cardowner' => 'Jane Doe', 
                                     'cardtype' => 'Visa', 
                                     'pan' => '4012888812348882', 
                                     'expire' => '1215', 
                                      'cvv' => '123'
                                    ),
                                 'order_id' => '629203'
							)
						  ); 
 
		$authTransactionid = $response['TransactionId'];
		
	} catch (Exception $e) {
		echo $e->getMessage(); die;
	}      


•	Capture method:                   

               try {
	
		$response = $velocity_processor->capture( array(
								'amount' => 6.03, 
								'TransactionId' => $authTransactionid
								)
							);		
		$captxnid = $response['TransactionId'];
		
	} catch(Exception $e) {
		echo $e->getMessage();
	}

Note : $authTransactionid is get from authorize response.





•	Void(Undo) method:                   

       try {
		$response = $velocity_processor->undo( array(
								 'TransactionId' => $adjusttxnid
							        ) 
							 );
										   		
	} catch (Exception $e) {
		echo $e->getMessage();
	} 




Adjust method:            
       
      try {
		
		$response = $velocity_processor->adjust( array(
								'amount' => 3.01, 
								'TransactionId' => $captxnid
							         )
							);
		 		
	} catch (Exception $e) {
		echo $e->getMessage();
	}
	
	$adjusttxnid = $response['TransactionId'];





•	ReturnById method:             
      
          try {
		$response = $velocity_processor->returnById( array(
								  'amount' => 5.03, 
								  'TransactionId' => $authCapTransactionid
								  ) 
							  );
	
    } catch (Exception $e) {
		echo $e->getMessage();
	}




•	ReturnUnlinked method: 
               
     try {
				
		$response = $velocity_processor->returnUnlinked( array( 
								  'amount' => 1.03, 
								  'token' => $paymentAccountDataToken, 
								  'order_id' => '629203'
								  ) 
								);
		
		
    } catch (Exception $e) {
		echo $e->getMessage();
    }
 
 
    
