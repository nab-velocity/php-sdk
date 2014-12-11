<?php
/*
 * This class represents a Velocity Transaction.
 * It can be used to query and capture/undo/adjust/returnbyid/returnunlinked/authorizeandcapture transactions.
 */

class Velocity_Transaction 
{
	/* -- Properties -- */

	private $isNew;
	private $connection;
	public $messages = array();
	public $errors = array();

	/* -- Class Methods -- */

	public function __construct() {
		$this->connection = Velocity_Connection::instance(); // velocity_connection class object store in private data member $connection. 
		
		if ( Velocity_Processor::$sessionToken == '' ) {  // genrate session token for SDK using identity token.
			try {
				new Velocity_Processor(VelocityCon::$identitytoken);
			} catch (Exception $e) {
				echo $e->getMessage();
			}
		}
	}

	/* -- Methods -- */

	/*
	* Captures an authorization. Optionally specify an `$amount` to do a partial capture of the initial
	* authorization. The default is to capture the full amount of the authorization.
	* @param array $options this is hold the amount, transactionid, method name. 
	*/
	public function capture($options = array()) {
		
		if(isset($options['amount']) && isset($options['TransactionId'])) {
			$amount = number_format($options['amount'], 2, '.', '');
			try {
				$xml = Velocity_XmlCreator::cap_XML($options['TransactionId'], $amount);  // got capture xml object.  
				$xml->formatOutput = TRUE;
				$body = $xml->saveXML();
				list($error, $response) = $this->connection->put(
																	$this->path(
																					VelocityCon::$workflowid, 
																					$options['TransactionId'], 
																					Velocity_Processor::$Txn_method[2]
																				), 
																	 array(
																			'sessiontoken' => Velocity_Processor::$sessionToken, 
																			'xml' => $body, 
																			'method' => Velocity_Processor::$Txn_method[2]
																		  )
																);
				//return $response;
				return $this->handleResponse($error, $response);
			} catch(Exception $e) {
				throw new Exception($e->getMessage());
			}
			
		} else {
		    throw new Exception(Velocity_Message::$descriptions['errcapsesswfltransid']);
		}
	}

	/*
	* Adjust this transaction. If the transaction has not yet been captured and settled it can be Adjust to 
	* A previously authorized amount (incremental or reversal) prior to capture and settlement. 
	* @param array $options this is hold the amount, transactionid, method name.
	*/
	public function adjust($options = array()) {
		
		if( isset($options['amount']) && isset($options['TransactionId']) ) {
			$amount = number_format($options['amount'], 2, '.', '');
			try {
				$xml = Velocity_XmlCreator::adjust_XML($options['TransactionId'], $amount);  // got adjust xml object.  
				$xml->formatOutput = TRUE;
				$body = $xml->saveXML();
				//echo '<xmp>'.$body.'</xmp>'; die;
				list($error, $response) = $this->connection->put(
																	$this->path(
																		VelocityCon::$workflowid, 
																		$options['TransactionId'], 
																		Velocity_Processor::$Txn_method[3]
																	), 
																	array(
																		'sessiontoken' => Velocity_Processor::$sessionToken, 
																		'xml' => $body, 
																		'method' => Velocity_Processor::$Txn_method[3]
																	)
																);
				return $this->handleResponse($error, $response);
		        //return $response;
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}	
			
		} else {
			throw new Exception(Velocity_Message::$descriptions['erradjustsesswfltransid']);
		}
	}
	
	/*
	 * The Undo operation is used to release cardholder funds by performing a void (Credit Card) or reversal (PIN Debit) on a previously 
	 * authorized transaction that has not been captured (flagged) for settlement.
	 * @param array $options this is hold the amount, transactionid, method name.
	 */
	public function undo($options = array()) {
		
		if ( isset($options['TransactionId']) && isset($options['avsdata']) ) {
		
			try {
				$xml = Velocity_XmlCreator::undo_XML($options['TransactionId']);  // got undo xml object.  
				$xml->formatOutput = TRUE;
				$body = $xml->saveXML();
				list($error, $response) = $this->connection->put( 
																	$this->path(
																		VelocityCon::$workflowid, 
																		$options['TransactionId'], 
																		Velocity_Processor::$Txn_method[4]
																	), 
																	array(
																		'sessiontoken' => Velocity_Processor::$sessionToken, 
																		'xml' => $body, 
																		'method' => Velocity_Processor::$Txn_method[4]
																	) 
																);
				//return $response;
				return $this->handleResponse($error, $response);
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
			
		} else {
			throw new Exception(Velocity_Message::$descriptions['errundosesswfltransid']);
		}
	}
	
	/*
	 * The ReturnById operation is used to perform a linked credit to a cardholder’s account from the merchant’s account based on a
	 * previously authorized and settled transaction.
	 * @param array $options this is hold the transactionid, method name.
	 */
	public function returnById($options = array()) {
		
		if(isset($options['amount']) && isset($options['TransactionId'])) {
			$amount = number_format($options['amount'], 2, '.', '');
			try {
				$xml = Velocity_XmlCreator::returnById_XML($amount, $options['TransactionId']);  // got ReturnById xml object. 
				$xml->formatOutput = TRUE;
				$body = $xml->saveXML();
				//echo '<xmp>'.$body.'</xmp>'; die;
				list($error, $response) = $this->connection->post(
																	$this->path(
																		VelocityCon::$workflowid, 
																		null, 
																		Velocity_Processor::$Txn_method[5]
																	), 
																	array(
																		'sessiontoken' => Velocity_Processor::$sessionToken, 
																		'xml' => $body, 
																		'method' => Velocity_Processor::$Txn_method[5]
																	)
																 );
				return $this->handleResponse($error, $response);
				//return $response;
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
			
		} else {
			throw new Exception(Velocity_Message::$descriptions['errreturntranidwid']);
		}  
	}
	
	/*
	 * The ReturnUnlinked operation is used to perform an "unlinked", or standalone, credit to a cardholder’s account from the merchant’s account.
	 * This operation is useful when a return transaction is not associated with a previously authorized and settled transaction.
	 * @param array $options this array hold "amount, paymentAccountDataToken, avsData, carddata, invoice no., order no"
	 */
	public function returnUnlinked($options = array()) {
		
		if(isset($options['amount']) && isset($options['token'])) {
			$amount = number_format($options['amount'], 2, '.', '');
			$options['amount'] = $amount;
			try {
				$xml = Velocity_XmlCreator::returnunlinked_XML($options);  // got ReturnById xml object. 
				$xml->formatOutput = TRUE;
				$body = $xml->saveXML();
				//echo '<xmp>'.$body.'</xmp>'; die;
				list($error, $response) = $this->connection->post(
																	$this->path(
																		VelocityCon::$workflowid, 
																		null, 
																		Velocity_Processor::$Txn_method[6]
																	), 
																	array(
																		'sessiontoken' =>  Velocity_Processor::$sessionToken, 
																		'xml' => $body, 
																		'method' => Velocity_Processor::$Txn_method[6]
																	)
																 );
				return $this->handleResponse($error, $response);
				//return $response;
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
			
		} else {
			throw new Exception(Velocity_Message::$descriptions['errreturntranidwid']);
		}  
	}

	
	/* path for according to request needed 
	 * @param string $arg1 part of url for request.
	 * @param string $arg2 part of url for request.
	 * @param string $arg3 name of method.
	 */
	private function path($arg1, $arg2, $rtype) {
		if(isset($arg1) && isset($arg2) && isset($rtype) && ( $rtype == 'capture' || $rtype == 'adjust' || $rtype == 'undo' ) ) {
			$path = 'Txn/'.$arg1.'/'.$arg2;
			return $path;
		} else if(isset($arg1) && isset($rtype) && ($rtype == 'authorizeandcapture' || $rtype == 'returnbyid' || $rtype == 'returnunlinked') ) {
			$path = 'Txn/'.$arg1;
			return $path;
		} else {
			throw new Exception(Velocity_Message::$descriptions['errcapadjpath']);
		}
	}
	
	/*
	* Parses the Velocity response for messages (info or error) and updates 
	* the current transaction's information. If an HTTP error is 
	* encountered, it will be thrown from this method.
	* @param array $error error message created on the basis of gateway error status. 
	* @param array $response gateway response deatil. 
	*/

	public function handleResponse($error, $response) {
		if ($error) {
			  return $error;
		} else {
		    if(!empty($response)) {
			  return $response;
			}
		}

		
	}

	/* optional
	 * Finds message blocks in the Velocity response, creates a `Velocity_Message`
	 * object for each one and stores them in either the `messages` or the
	 * `errors` internal array, depending on the message type.
	 * @param array $response gateway response deatil.
	 */
	private function processResponseMessages($response = array()) {
		$messages = self::extractMessagesFromResponse($response);  
		$this->messages = array();
		$this->errors = array();
		// error processing according to needed in module of prestashop.
		return $messages;
	}

	/*optional
	* Finds all messages returned in a Velocity response, regardless of
	* what part of the response they were in.
	* @param array $response gateway response deatil.
	*/
	private static function extractMessagesFromResponse($response = array()) {
		$message = '';
		foreach ($response as $key => $value) {
			if ( is_array($value) ) {
				$message = self::extractMessagesFromResponse($value);
			} else {
				if($key == 'TransactionState') {
					$message .= $key . ':' . $value;
				}
			}
		}
		return $message;
	}

}