<?php

class NorthAmericanBancard_Message
{
  public function __construct(){
  }

  public static $descriptions = array(
    'errtransparentjs' => 'Post data is not set from transparent redirect.',
	// authorize
	'errauthsesstoken' => 'session token is not set for authorize request!',
	'erraurhavswflid' => 'PaymentAccountDataToken and/or workflowid is not set!',
	'erraurhattraray' => 'After authorization attribute array is not!',
	'errauthtrandata' => 'transaction data array not set for authorize request',
	// capture
	'errcapsesswfltransid' => 'for capture sessiontoken, workflowid and/or transaction id is not set!',
	'errcaptransidamount' => 'transaction id and/or amount not set!',
	'errcapxml' => 'Some value not set in xml for capture!',
	'errcapsesstoken' => 'Session token not set for capture request',
	// adjust
	'erradjustsesswfltransid' => 'for adjust sessiontoken, workflowid and/or transaction id is not set!',
	'errcapadjpath' => 'capture or adjust request path not set proper!',
	'erradjtransidamount' => 'adjust request transaction id and/or amount not set!',
	'erradjxml' => 'Some value not set in xml for adjust!',
	'erradjsesstoken' => 'Session token not set for adjust request',

  );
}
