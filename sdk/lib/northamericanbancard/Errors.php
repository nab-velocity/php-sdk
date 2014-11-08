<?php

class NorthAmericanBancard_Error extends Exception
{
	public function __construct($message, $name) {
		parent::__construct($message);
		$this->name = $name;
	}
}

# 400
class NorthAmericanBancard_BadRequestError extends NorthAmericanBancard_Error
{
	public function __construct() { 
		parent::__construct('Bad Request', 'badRequestError');
	}
}

# 500
class NorthAmericanBancard_InternalServerError extends NorthAmericanBancard_Error
{
	public function __construct() {
		parent::__construct('Internal Server Error', 'internalServerError');
	}
}

# 5000
class NorthAmericanBancard_SessionTokenExpireError extends NorthAmericanBancard_Error
{
	public function __construct() {
		parent::__construct('Session Token has been expired', 'ExpiredTokenFault');
	}
}

# Everything else
class NorthAmericanBancard_UnexpectedError extends NorthAmericanBancard_Error
{
	public function __construct($message) {
		parent::__construct($message, 'unexpectedError');
	}
}
