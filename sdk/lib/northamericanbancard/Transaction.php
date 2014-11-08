<?php

/*
 * This class represents a NorthAmericanBancard Transaction.
 * It can be used to query and capture/void/credit/reverse transactions.
 */

class NorthAmericanBancard_Transaction 
{
	/* -- Properties -- */

	private $isNew;
	private $connection;

	public $attributes = array();
	public $messages = array();
	public $errors = array();

	/* -- Class Methods -- */


	public function __construct($attributes = array()) {
		$this->attributes = array_merge($this->attributes, $attributes);
		$this->connection = NorthAmericanBancard_Connection::instance();
	}

	/* -- Methods -- */

	/*
	* Captures an authorization. Optionally specify an `$amount` to do a partial capture of the initial
	* authorization. The default is to capture the full amount of the authorization.
	*/
	public function capture($amount = null, $options = array()) {
		$amount = isset($amount) ? $amount : $this->amount;
		$options = array_merge($options, array('amount' => $amount));
		list($error, $response) = $this->connection->put($this->path($options['workflowid'], $this->attributes['TransactionId'], $options['method']), $options);
		return $response;
		//return $this->handleResponse($error, $response);
	}

	/*
	* Adjust this transaction. If the transaction has not yet been captured and settled it can be Adjust to 
	* A previously authorized amount (incremental or reversal) prior to capture and settlement. 
	*/
	public function adjust($amount = null, $options = array()) {
	    $amount = isset($amount) ? $amount : $this->amount;
		$options = array_merge($options, array('amount' => $amount)); 
		list($error, $response) = $this->connection->put($this->path($options['workflowid'], $this->attributes['TransactionId'], $options['method']), $options);
		return $this->handleResponse($error, $response);
	}
	
	/* path for according to request needed */
	private function path($arg1, $arg2, $rtype) {
		if(isset($arg1) && isset($arg2) && isset($rtype) && ( $rtype == 'capture' || $rtype == 'adjust' ) ) {
			$path = 'Txn/'.$arg1.'/'.$arg2;
			return $path;
		} else {
			throw new Exception(NorthAmericanBancard_Message::$descriptions['errcapadjpath']);
		}
	}

	/*
	* Parses the NorthAmericanBancard response for messages (info or error) and updates 
	* the current transaction's information. If an HTTP error is 
	* encountered, it will be thrown from this method.
	*/

	public function handleResponse($error, $response) {
		if ($error) {
			$this->attributes['success'] = false;
			if (isset($response) && isset($response['error'])) {
				$this->updateAttributes($response['error']);
			}
		} else {
		    if (isset($response) && isset($response['BankcardTransactionResponsePro'])) {
				$this->updateAttributes($response['BankcardTransactionResponsePro']);
		    } else if(isset($response) && isset($response['BankcardCaptureResponse'])) {
				$this->updateAttributes($response['BankcardCaptureResponse']);
			}
		}

		if(!empty($response)) {
		  $this->processResponseMessages($response);
		}
	}

	/*
	* Finds message blocks in the NorthAmericanBancard response, creates a `NorthAmericanBancard_Message`
	* object for each one and stores them in either the `messages` or the
	* `errors` internal array, depending on the message type.
	*/
	private function processResponseMessages($response = array()) {
		$messages = self::extractMessagesFromResponse($response);  
		$this->messages = array();
		$this->errors = array();

		if (!function_exists('byImportance')) {
			function byImportance($a, $b) {
				$order = array('is_blank', 'not_numeric', 'too_short', 'too_long', 'failed_checksum');
				$a = array_search($a['key'], $order);
				$b = array_search($b['key'], $order);
				$a = $a === FALSE ? 0 : $a;
				$b = $b === FALSE ? 0 : $b;

				return ($a < $b ? -1 : ($a > $b ? 1 : 0));
			}
		}

		usort($messages, 'byImportance');

		foreach ($messages as $message) {
			$message['subclass'] = isset($message['subclass']) ? $message['subclass'] : $message['class'];
			$message['$t']       = isset($message['$t'])       ? $message['$t']       : null;
			$message['context']  = empty($message['context'])  ? 'system.general'     : $message['context'];

			$m = new NorthAmericanBancard_Message($message['subclass'], $message['context'], $message['key'], $message['$t']);

			if ($message['subclass'] === 'error') {
				if (isset($this->errors[$message['context']])) {
				    $this->errors[$message['context']][] = $m;
				} else {
				    $this->errors[$message['context']] = array($m);
				}
			} else {
				if (isset($this->messages[$message['context']])) {
				    $this->messages[$message['context']][] = $m;
				} else {
				    $this->messages[$message['context']] = array($m);
				}
			}
		}
	}

	/*
	* Finds all messages returned in a NorthAmericanBancard response, regardless of
	* what part of the response they were in.
	*/
	private static function extractMessagesFromResponse($response = array()) {
	$messages = array();

	foreach ($response as $key => $value) {
		if ($key === 'messages' && is_array($value)) {
			$messages = array_merge($messages, $value);
		} elseif (is_array($value)) {
			$res = self::extractMessagesFromResponse($value);
				if(!empty($res)) {
				  $messages = array_merge($messages, $res);
			}
		}
	}

	return $messages;
	}

	/*
	* Updates the internal `attributes` array with newly returned information.
	*/
	public function updateAttributes($attributes = array()) {
		// sometimes the returned transaction would not have all of the
		// original transaction's data, so this makes sure we don't
		// overwrite data that we already have with blank values
		foreach ($attributes as $key => $value) {
			if ($value !== '' || !isset($this->attributes[$key])) {
				$this->attributes[$key] = $value;
			}
		}
	}
}
