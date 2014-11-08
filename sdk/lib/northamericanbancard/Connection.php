<?php

/* 
 * The `NorthAmericanBancard_Connection` class is responsible for making requests to 
 * the NorthAmericanBancard API and parsing the returned response.
 */
class NorthAmericanBancard_Connection 
{
	private static $instance;
	
	public static function instance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
	}

	/*
     * Convenience method for making GET requests.
	 */
	public function get($path, $data = array()) {
		return $this->request('GET', $path, $data);
	}

	/*
     * Convenience method for making POST requests.
	 */
	public function post($path, $data = array()) {
		return $this->request('POST', $path, $data);
	}

	/*
     * Convenience method for making PUT requests.
	 */
	public function put($path, $data = array()) {
		return $this->request('PUT', $path, $data);
	}

	/*
     * Performs a GET/POST/PUT request to the NorthAmericanBancard API, parses the returned response
	 * and then returns it.
	 */
	private function request($method, $path, $data = array()) {
	
		$body;	
		$session_token; 
		if(isset($data['method']) && $data['method'] == 'capture') {
			
			if(isset($data['amount']) && isset($data['TransactionId'])) {
				$xml = $this->cap_XML($data['TransactionId'], $data['amount']);  // got capture xml object.  
			} else {
				throw new Exception(NorthAmericanBancard_Message::$descriptions['errcaptransidamount']);
			}
			if(isset($xml)) {
				$xml->formatOutput = TRUE;
				$body = $xml->saveXML();
			} else {
				throw new Exception(NorthAmericanBancard_Message::$descriptions['errcapxml']);
			}
			if(isset($data['sessiontoken'])) {
			   $session_token = $data['sessiontoken'];
			} else {
				throw new Exception(NorthAmericanBancard_Message::$descriptions['errcapsesstoken']);
			}
			
		} else if (isset($data['method']) && $data['method'] == 'adjust') {
		
			if(isset($data['amount']) && isset($data['TransactionId'])) {
				$xml = $this->adjust_XML($data['TransactionId'], $data['amount']);  // got adjust xml object.  
			} else {
				throw new Exception(NorthAmericanBancard_Message::$descriptions['erradjtransidamount']);
			}
			if(isset($xml)) {
				$xml->formatOutput = TRUE;
				$body = $xml->saveXML();die();
				echo '<xmp>'.$body.'</xmp>'; 
			} else {
				throw new Exception(NorthAmericanBancard_Message::$descriptions['erradjxml']);
			}
			if(isset($data['sessiontoken'])) {
			   $session_token = $data['sessiontoken'];
			} else {
				throw new Exception(NorthAmericanBancard_Message::$descriptions['erradjsesstoken']);
			}
			
		} else {
		   
			if(isset($data['transaction'])) {
				$xml = $this->auth_XML($data['transaction']);  // got authorize xml object.
				
				if(isset($xml)) {
					$xml->formatOutput = TRUE;
					$body = $xml->saveXML();
				} else {
					throw new Exception(NorthAmericanBancard_Message::$descriptions['errcapxml']);
				}
			} else {
				throw new Exception(NorthAmericanBancard_Message::$descriptions['errauthtrandata']);
			}
			if(isset($data['transaction']['Session_token'])) {
			   $session_token = $data['transaction']['Session_token'];
			}else {
				throw new Exception(NorthAmericanBancard_Message::$descriptions['errauthsesstoken']);
			}
		}
		
		$rest_action = $method;
		$api_url = NorthAmericanBancard::$site . $path;
		$timeout=60;
		
		 
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
		$header[] = 'Authorization: Basic '. $session_token;
		$header[] = 'Content-Type: application/xml';
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
		
		$res = curl_exec($ch);
		list($header, $body) = explode("\r\n\r\n", $res, 2);

		if (NorthAmericanBancard::$debug) {   // print the response and error for debug
			echo "\n--------- Response ----------\n<br>";
			echo '<pre>'; print_r($header); echo '</pre>';
			echo "\n<br>";
			echo '<xmp>'.$body.'</xmp>';
			echo "\n-----------------------------\n<br>";
		}

		$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$error = self::errorFromStatus($statusCode);


		$match = null;
		preg_match('/Content-Type: ([^;]*);/i', $body, $match);
		$contentType;
		if(isset($match[1])){
		   $contentType = $match[1]; 
		}else{
		   preg_match('/Content-Type: ([^;]*);/i', $header, $match);
		   $contentType = $match[1];
		}

		// Parse response, depending on value of the Content-Type header.
		$response = null;
		if (preg_match('/json/', $contentType)) {
			$response = json_decode($body, true); 
		} elseif (preg_match('/xml/', $contentType)) {
		    $arr = explode('Path=/',$body);
			if(isset($arr[1]))
			   $response = NorthAmericanBancard_XmlParser::parse($arr[1]);
			else
			   $response = NorthAmericanBancard_XmlParser::parse($body);
		}
		
		return array($error, $response);
	 
	}
	
	/* 
	 * create authorize xml as per the api format .
	 */
	private function auth_XML($data) {
	  
	    if(isset($data['amount']) && isset($data['Street']) && isset($data['City']) && isset($data['StateProvince']) && isset($data['PostalCode']) && isset($data['Country'])) {
		
			$xml = new DOMDocument("1.0");

			$root = $xml->createElement("AuthorizeTransaction");

			$xml->appendChild($root);

			$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:i', 'http://www.w3.org/2001/XMLSchema-instance');
			$root->setAttribute('xmlns', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions/Rest');
			$root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:type', 'AuthorizeTransaction');

			$n = $xml->createElement("ApplicationProfileId");
			$idText = $xml->createTextNode(NorthAmericanBancard::$applicationprofileid);
			$n->appendChild($idText);
			$root->appendChild($n);

			$n = $xml->createElement("MerchantProfileId");
			$idText = $xml->createTextNode(NorthAmericanBancard::$merchantprofileid);
			$n->appendChild($idText);
			$root->appendChild($n);

			$n = $xml->createElement("Transaction");
			$n->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ns1', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions/Bankcard');
			$n->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:type', 'ns1:BankcardTransaction');

			$n1 = $xml->createElement("ns2:CustomerData");
			$n1->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ns2', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions');
			$n->appendChild($n1);

			$n2 = $xml->createElement("ns2:BillingData");
			$n1->appendChild($n2);

			$n3 = $xml->createElement("ns2:Name");
			$n3->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n2->appendChild($n3);

			$n3 = $xml->createElement("ns2:Address");
			$n2->appendChild($n3);

			$n4 = $xml->createElement("ns2:Street1");
			$idText = $xml->createTextNode($data['Street']);
			$n4->appendChild($idText);
			$n3->appendChild($n4);

			$n4 = $xml->createElement("ns2:Street2");
			$n4->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n3->appendChild($n4);

			$n4 = $xml->createElement("ns2:City");
			$idText = $xml->createTextNode($data['City']);
			$n4->appendChild($idText);
			$n3->appendChild($n4);

			$n4 = $xml->createElement("ns2:StateProvince");
			$idText = $xml->createTextNode($data['StateProvince']);
			$n4->appendChild($idText);
			$n3->appendChild($n4);

			$n4 = $xml->createElement("ns2:PostalCode");
			$idText = $xml->createTextNode($data['PostalCode']);
			$n4->appendChild($idText);
			$n3->appendChild($n4);

			$n4 = $xml->createElement("ns2:CountryCode");
			$idText = $xml->createTextNode('USA');
			$n4->appendChild($idText);
			$n3->appendChild($n4);

			$n3 = $xml->createElement("ns2:BusinessName");
			$idText = $xml->createTextNode('MomCorp');
			$n3->appendChild($idText);
			$n2->appendChild($n3);

			$n3 = $xml->createElement("ns2:Phone");
			$n3->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n2->appendChild($n3);

			$n3 = $xml->createElement("ns2:Fax");
			$n3->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n2->appendChild($n3);

			$n3 = $xml->createElement("ns2:Email");
			$n3->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n2->appendChild($n3);

			$n2 = $xml->createElement("ns2:CustomerId");
			$idText = $xml->createTextNode('cust123x');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns2:CustomerTaxId");
			$n2->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns2:ShippingData");
			$n2->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n1->appendChild($n2);

			$n1 = $xml->createElement("ns3:ReportingData");
			$n1->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ns3', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions');
			$n->appendChild($n1);

			$n2 = $xml->createElement("ns3:Comment");
			$idText = $xml->createTextNode('a test comment');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns3:Description");
			$idText = $xml->createTextNode('a test description');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns3:Reference");
			$idText = $xml->createTextNode('001');
			$n2->appendChild($idText);
			$n1->appendChild($n2);
		
			$n1 = $xml->createElement("ns1:TenderData");
			$n->appendChild($n1);

			$n2 = $xml->createElement("ns4:PaymentAccountDataToken");
			$n2->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ns4', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions');
			$n2->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns5:SecurePaymentAccountData");
			$n2->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ns5', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions');
			$n2->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns6:EncryptionKeyId");
			$n2->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ns6', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions');
			$n2->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns7:SwipeStatus");
			$n2->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ns7', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions');
			$n2->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:CardData");
			$n1->appendChild($n2);

			$n3 = $xml->createElement("ns1:CardType");
			$idText = $xml->createTextNode('MasterCard');
			$n3->appendChild($idText);
			$n2->appendChild($n3);


			$n3 = $xml->createElement("ns1:PAN");
			$idText = $xml->createTextNode('5428376000953619');
			$n3->appendChild($idText);
			$n2->appendChild($n3);

			$n3 = $xml->createElement("ns1:Expire");
			$idText = $xml->createTextNode('0320');
			$n3->appendChild($idText);
			$n2->appendChild($n3);

			$n3 = $xml->createElement("ns1:Track1Data");
			$n3->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n2->appendChild($n3);

			$n2 = $xml->createElement("ns1:EcommerceSecurityData");
			$n2->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n1->appendChild($n2);

			$n1 = $xml->createElement("ns1:TransactionData");
			$n->appendChild($n1);

			$n2 = $xml->createElement("ns8:Amount");
			$n2->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ns8', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions');
			$idText = $xml->createTextNode($data['amount']);
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns9:CurrencyCode");
			$n2->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ns9', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions');
			$idText = $xml->createTextNode('USD');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns10:TransactionDateTime");
			$n2->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ns10', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions');
			$idText = $xml->createTextNode('2013-04-03T13:50:16');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns11:CampaignId");
			$n2->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ns11', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions');
			$n2->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns12:Reference");
			$n2->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ns12', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions');
			$idText = $xml->createTextNode('xyt');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:ApprovalCode");
			$n2->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:CashBackAmount");
			$idText = $xml->createTextNode('0.0');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:CustomerPresent");
			$idText = $xml->createTextNode('Present');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:EmployeeId");
			$idText = $xml->createTextNode('11');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:EntryMode");
			$idText = $xml->createTextNode('Keyed');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:GoodsType");
			$idText = $xml->createTextNode('NotSet');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:IndustryType");
			$idText = $xml->createTextNode('Ecommerce');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:InternetTransactionData");
			$n2->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:InvoiceNumber");
			$idText = $xml->createTextNode('802');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:OrderNumber");
			$idText = $xml->createTextNode('629203');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:IsPartialShipment");
			$idText = $xml->createTextNode('false');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:SignatureCaptured");
			$idText = $xml->createTextNode('false');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:FeeAmount");
			$idText = $xml->createTextNode('0.0');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:TerminalId");
			$n2->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:LaneId");
			$n2->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:TipAmount");
			$idText = $xml->createTextNode('0.0');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:BatchAssignment");
			$n2->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:PartialApprovalCapable");
			$idText = $xml->createTextNode('NotSet');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:ScoreThreshold");
			$n2->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$n1->appendChild($n2);

			$n2 = $xml->createElement("ns1:IsQuasiCash");
			$idText = $xml->createTextNode('false');
			$n2->appendChild($idText);
			$n1->appendChild($n2);

			$root->appendChild($n);
			
			return $xml;
		} else {
			return NULL;
		}
    }
	
	/* 
     * create capture xml as per the api format.
	 */
	private function cap_XML($TransactionId, $amount){
	    
		if(isset($TransactionId) && isset($amount)) {
			$xml = new DOMDocument("1.0", "UTF-8");

			$root = $xml->createElement("ChangeTransaction");

			$xml->appendChild($root);
			$root->setAttribute('xmlns', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions/Rest');
			$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:i', 'http://www.w3.org/2001/XMLSchema-instance');
			$root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:type', 'Capture');
			
			$n = $xml->createElement("ApplicationProfileId");
			$idText = $xml->createTextNode(NorthAmericanBancard::$applicationprofileid);
			$n->appendChild($idText);
			$root->appendChild($n);
			
			$n = $xml->createElement("DifferenceData");
			$n->setAttribute('xmlns:d2p1', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions');
			$n->setAttribute('xmlns:d2p2', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions/Bankcard');
			$n->setAttribute('xmlns:d2p3', 'http://schemas.ipcommerce.com/CWS/v2.0/TransactionProcessing');
			$n->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:type', 'd2p2:BankcardCapture');
			$root->appendChild($n);
			
			$n1 = $xml->createElement("d2p1:TransactionId");
			$idText = $xml->createTextNode($TransactionId);
			$n1->appendChild($idText);
			$n->appendChild($n1);
			
			$n1 = $xml->createElement("d2p2:Amount");
			$idText = $xml->createTextNode($amount);
			$n1->appendChild($idText);
			$n->appendChild($n1);
			
			$n1 = $xml->createElement("d2p2:TipAmount");
			$idText = $xml->createTextNode(0.0);
			$n1->appendChild($idText);
			$n->appendChild($n1);
			
			return $xml;
		} else {
			return NULL;
		}
	}
	
	/* 
     * create Adjust xml as per the api format.
	 */
	private function adjust_XML($TransactionId, $amount){
	    
		if(isset($TransactionId) && isset($amount)) {
			$xml = new DOMDocument("1.0");

			$root = $xml->createElement("Adjust");

			$xml->appendChild($root);
			$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:i', 'http://www.w3.org/2001/XMLSchema-instance');
			$root->setAttribute('xmlns', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions/Rest');
			$root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:type', 'Adjust');
			
			$n = $xml->createElement("ApplicationProfileId");
			$idText = $xml->createTextNode(NorthAmericanBancard::$applicationprofileid);
			$n->appendChild($idText);
			$root->appendChild($n);
			
			$n = $xml->createElement("BatchIds");
			$n->setAttribute('xmlns:d2p1', 'http://schemas.microsoft.com/2003/10/Serialization/Arrays');
			$n->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:nil', 'true');
			$root->appendChild($n);
			
			$n = $xml->createElement("MerchantProfileId");
			$idText = $xml->createTextNode(NorthAmericanBancard::$merchantprofileid);
			$n->appendChild($idText);
			$root->appendChild($n);
			
			$n = $xml->createElement("DifferenceData");
			$n->setAttribute('xmlns:ns1', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions');
			$root->appendChild($n);
		
			
			$n1 = $xml->createElement("ns2:Amount");
			$n1->setAttribute('xmlns:ns2', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions');
			$idText = $xml->createTextNode($amount);
			$n1->appendChild($idText);
			$n->appendChild($n1);
			
			$n1 = $xml->createElement("ns3:TransactionId");
			$n1->setAttribute('xmlns:ns3', 'http://schemas.ipcommerce.com/CWS/v2.0/Transactions');
			$idText = $xml->createTextNode($TransactionId);
			$n1->appendChild($idText);
			$n->appendChild($n1);
			
			
			return $xml;
		} else {
			return NULL;
		}
	}
	
	/*
   * Returns an error object, corresponding to the HTTP status code returned by NorthAmericanBancard.
	 */
	 
	private static function errorFromStatus($status) {
	switch ($status) {
			case '200': 
				return null;
			case '201': 
				return null;
			case '400':
				return new NorthAmericanBancard_BadRequestError();
			case '500': 
				return new NorthAmericanBancard_InternalServerError();
			case '5000': 
				return new NorthAmericanBancard_SessionTokenExpireError();	
			default: 
				return new NorthAmericanBancard_UnexpectedError('Unexpected HTTP response: ' . $status);
		}
	}
	
}
