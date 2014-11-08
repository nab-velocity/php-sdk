<?php

class NorthAmericanBancard_Processor 
{
	public $sessionToken;
	private $connection;

	/*
	* Returns a `NorthAmericanBancard_Processor` object for the specified `$sessionToken`.
	*/
	public static function find($sessionToken) {
		return new self($sessionToken);
	}


	public function __construct ($sessionToken) {
		$this->sessionToken = $sessionToken;
		$this->connection = NorthAmericanBancard_Connection::instance();
	}

	/*
	* Authorize a payment_method for a particular amount.
	* Parameters:
	*
	* * `$amount`: amount to authorize
	*
	* * `$options`: an optional array of additional values to pass in. Accepted values are:
	*   * `description`: description for the transaction
	*   * `descriptor_name`: descriptor_name for the transaction
	*   * `descriptor_phone`: descriptor_phone for the transaction
	*   * `currency_code`: the currency code used for this transaction (eg, USD)
	*
	* Returns a NorthAmericanBancard.Transaction containing the processor's response.
	*/
	
	public function authorize($amount, $options = array()) {
		$options = array_merge($options, array('Session_token' => $this->sessionToken, 'amount' => $amount));
		list($error, $response) = $this->connection->post('Txn/'.$options['workflowid'], $this->prepareTransactionData($options));
		return $this->handleResponse($error, $response);
	}

	/*
	* Returns a new `NorthAmericanBancard_Transaction` object, associated with the 
	* request.
	*/

	private function handleResponse($error, $response) { 
		$transaction = new NorthAmericanBancard_Transaction();
		$transaction->handleResponse($error, $response);
		return $transaction;
	}

	/*
	* Wraps transaction data in an additional transaction object,
	* according to spec.
	*/
	
	private function prepareTransactionData($data) {
		if (!isset($data['currency_code']) || $data['currency_code']=='') { $data['currency_code'] = 'USD'; }
		return array('transaction' => $data);
	}
  
}
