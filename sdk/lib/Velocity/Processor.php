<?php
/*
 * This class represents a Velocity Transaction.
 * It can be used to query and authorize/authandcap transactions.
 */
 
class Velocity_Processor 
{
	public static $sessionToken;
	private $connection;
	public static $Txn_method = array('authorize', 'authorizeandcapture', 'capture', 'adjust', 'undo', 'returnbyid', 'returnunlinked'); // array of method name to identify method request for common process.
	
	public static $processorinstance;
	
	public static function processorinstance($identitytoken) {
		if (!isset(self::$processorinstance)) {
			self::$processorinstance = new self($identitytoken);
		}
		return self::$processorinstance;
	}

	public function __construct ($identitytoken) {
		self::$sessionToken = Velocity_Processor::signOn($identitytoken);
		$this->connection = Velocity_Connection::instance();
	}
	
	/*
	* Returns a `$sessionToken` genrate by identitytoken.
	*
	* @param string $identitytoken this is identity token provided by gateway. 
	* @return string $sessionToken session token is velocity security token.
	*/
	public static function signOn($identitytoken) {
		$response = Velocity_Processor::curl_json('', VelocityCon::$site.'SvcInfo/token', 'GET', $identitytoken);
		if (isset($response->body->ErrorId)) {
			handleRestFault($response);
			throw new Exception(Velocity_Message::$descriptions['errauthsesstoken']);
		} else {
			if (isset($response[2])) {
				$sessionToken = $response[2];
				return $sessionToken;
			} else {
				throw new Exception(Velocity_Message::$descriptions['errauthsesstoken']);
			}
		}
	}
	
	/*
	* Authorize a payment_method for a particular amount.
	* This Method create corresponding xml for gateway request.
	* This Method Reqest send to gateway and handle the response.
	* @param array $options this array hold "amount, paymentAccountDataToken, avsData, carddata, invoice no., order no"
	* @return array $this->handleResponse($error, $response) array of successfull or failure of gateway response. 
	*/
	
	public function authorize($options = array()) {
		if(isset($options['amount']) && isset($options['token']) && isset($options['avsdata'])) {
		$amount = number_format($options['amount'], 2, '.', '');
		$options['amount'] = $amount;
			try {
				$xml = Velocity_XmlCreator::auth_XML($options);  // got authorize xml object.
				$xml->formatOutput = TRUE;
				$body = $xml->saveXML();
				//echo '<xmp>'.$body.'</xmp>';
				list($error, $response) = $this->connection->post(
																	$this->path(
																		VelocityCon::$workflowid, 
																		null, 
																		self::$Txn_method[0]
																	), 
																	array(
																		'sessiontoken' => self::$sessionToken, 
																		'xml' => $body, 
																		'method' => self::$Txn_method[0]
																	)
																 );
				return $this->handleResponse($error, $response);
				//return $response;
			} catch (Exception $e) {
				throw new Exception( $e->getMessage() );
			}
	
		} else {
		    throw new Exception(Velocity_Message::$descriptions['errauthtrandata']);
		}
	}

	/*
	 * Authorizeandcapture operation is used to authorize transactions by performing a check on cardholder's funds and reserves.  
	 * The authorization amount if sufficient funds are available.  
	 * @param array $options this array hold "amount, paymentAccountDataToken, avsData, carddata, invoice no., order no"
	 * @return array $this->handleResponse($error, $response) array of successfull or failure of gateway response. 	 
	 */
	public function authorizeAndCapture($options = array()) { 
		
		if(isset($options['amount']) && isset($options['token'])) {
			$amount = number_format($options['amount'], 2, '.', '');
			$options['amount'] = $amount;
			try {
			
				$xml = Velocity_XmlCreator::authandcap_XML($options);  // got authorizeandcapture xml object. 
				$xml->formatOutput = TRUE;
				$body = $xml->saveXML();
				//echo '<xmp>'.$body.'</xmp>';
				list($error, $response) = $this->connection->post(
																	$this->path(
																		VelocityCon::$workflowid, 
																		null, 
																		self::$Txn_method[1]
																	), 
																	array(
																		'sessiontoken' => Velocity_Processor::$sessionToken, 
																		'xml' => $body, 
																		'method' => self::$Txn_method[1]
																		)
																 );
				return $this->handleResponse($error, $response);
				//return $response;
				
			} catch(Exception $e) {
				throw new Exception($e->getMessage());
			}
		
		} else {
			throw new Exception(Velocity_Message::$descriptions['erraurhncapavswflid']);
		}
	}
	
	/* path for according to request needed 
	 * @param string $arg1 part of url for request.
	 * @param string $arg2 part of url for request.
	 * @param string $arg3 name of method.
	 */
	private function path($arg1, $arg2, $rtype) {
		if(isset($arg1) && isset($rtype) && ($rtype == 'authorizeandcapture' || $rtype == 'authorize') ) {
			$path = 'Txn/'.$arg1;
			return $path;
		} else {
			throw new Exception(Velocity_Message::$descriptions['errcapadjpath']);
		}
	}
	
	
	/*
	* Returns a new `Velocity_Transaction` object, associated with the 
	* request.
	* @param array $error error message created on the basis of gateway error status. 
	* @param array $response gateway response deatil. 
	* @return array $transaction_response array of successfull or failure of gateway response. 	
	*/

	private function handleResponse($error, $response) { 
		$transaction = new Velocity_Transaction();
		$transaction_response = $transaction->handleResponse($error, $response);
		return $transaction_response;
	}
	
	/* method to genrate signon response with session token
     * @param string $body set the optional curl request.
	 * @param string $api_url base url of gateway request.
	 * @param string $rest_action method name of request.
	 * @param string $session_token identity token provided by velocity.
	 * @return array array($response, $info, $data) array of successfull or failure of gateway response.
	 * @return array array($response, $info) array of successfull or failure of gateway response.
     * @return object $fault fault of gateway error message and/or detail.	 
	 */
	private static function curl_json($body , $api_url, $rest_action, $session_token='', $timeout=60) {
		$user_agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
		
		// Parse the full api_url for required pieces. 
		$strpos = strpos($api_url, '/', 8); // 8 denotes look after https://
		$host = mb_substr($api_url, 8, $strpos-8);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url); // set url to post to
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return variable
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // connection timeout
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_USERAGENT, $user_agent); 
			
		if ($rest_action == 'POST')
			curl_setopt($ch, CURLOPT_POST, true);
		elseif ($rest_action == 'GET')
			curl_setopt($ch, CURLOPT_HTTPGET, true);
		elseif ($rest_action == 'PUT')
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		elseif ($rest_action == 'DELETE')
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		
		// Header setup		
		$header[] = 'Authorization: Basic '. base64_encode($session_token.":");
		$header[] = 'Content-Type: application/json';
		$header[] = 'Accept: '; // Known issue: defining this causes server to reply with no content.
		$header[] = 'Expect: 100-continue';
		$header[] = 'Host: '.$host;
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		
		//The following 3 will retrieve the header with the response. Remove if you do not want the response to contain the header.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_VERBOSE, 1); // Will output network information to the Console
		curl_setopt($ch, CURLOPT_HEADER, 1);
		
		if ($rest_action != 'GET')
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
			
		if ($rest_action == 'DELETE')
			$expected_response = "204";	
		elseif (($rest_action == 'POST') and (strpos($api_url, 'transactionsSummary') == true))	
			$expected_response = "200";	
		elseif (($rest_action == 'POST') and (strpos($api_url, 'transactionsFamily') == true))	
			$expected_response = "200";
		elseif (($rest_action == 'POST') and (strpos($api_url, 'transactionsDetail') == true))	
			$expected_response = "200";					
		elseif ($rest_action == 'POST')
			$expected_response = "201";
		else
			$expected_response = "200";
		

		$response = curl_exec($ch); // Run the request	
		$info = curl_getinfo($ch);	
		curl_close($ch);
		
		$header_size = $info['header_size'];
		$response_header = substr($response, 0, $header_size);
		$response_body = substr($response, $header_size);	
		
		if($info['http_code'] == $expected_response){
		
			if($info['http_code'] != "204")
			{
				if ($response_body[0] == "{" || $response_body[0] == "\"" || $response_body[0] == "[")
					$data = json_decode($response_body);
			}		
		}else{
		
			$fault = new stdClass();
			$fault->header = $response_header;
			if ($response_body[0] == "{" || $response_body[0] == "\"" || $response_body[0] == "[")
				$fault->body = json_decode($response_body);
			else{ // The response is in XML
			
				$pos_of_500 = strpos($response_header, '500');
				$pos_of_cr = strpos($response_header, "\r", $pos_of_500);
				$length = $pos_of_cr - $pos_of_500;						
				$msg_from_header = substr($response_header, $pos_of_500, $length); 
				$fault->body->ErrorId = 500;
				$fault->body->Messages = Array(0 => $msg_from_header);
				
			}
			return $fault;
		}
		
		if(isset($data)){
			return array($response, $info, $data);
		}else{
			return array($response, $info);
		}
		
	}
  
}
