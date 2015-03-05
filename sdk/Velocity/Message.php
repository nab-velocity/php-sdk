<?php

class Velocity_Message
{
  public function __construct(){
  }

  public static $descriptions = array(
  
    'errtransparentjs' => 'Due to some technical issue, Security validation failed',
	'errsessionxmlnotset' => 'Authentication failed due to session token not get form gateways',
	'errpostmethod' => 'Requestet url of gateway is not valid.',
	'errgetmethod' => 'Requested url of gateway is not valid.',
	'errputmethod' => 'Requested url of gateway is not valid.',
	'errunknown' => 'some unknown technical issue.',
	'errsignon' => 'invalid security token',
	'errmrchtid' => 'Invalid merchant credential',
	'errpannum' => 'Invalid credit card number',
	'errexpire' => 'Invalid expiry month and/or year',
	'errcvdata' => 'Invalid CVV data',
	'erstatecode' => 'Invalid state code or gateway service not available or some unknown error',
	
	// capture
	'errcapsesswfltransid' => 'Authentication failed due to Security token not validate',
	'errcaptransidamount' => 'Invalid Transaction id for Settlement',
	
	// adjust
	'erradjustsesswfltransid' => 'Adjustment failed due Security.',
	'erradjtransidamount' => 'Adjustment failed due invalid previous transaction id and/or amount',
	
	// undo
	'errundotransid' => 'Transaction failed due to security.',
	'errundosesswfltransid' => 'Authentication failed due to Security token not validate',
	
	// returnbyid
	'errreturndataarray' => 'Transaction failed due to security.',
	'errreturntranidwid' => 'Authentication failed due to Security token not validate',
	'erramtnotset' => 'Amount must be set!',
	'errcarddatatokennotset' => 'Invalid card detail.',
  );
}
