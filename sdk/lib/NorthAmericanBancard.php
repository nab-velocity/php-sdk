<?php

abstract class NorthAmericanBancard{

  const VERSION = '1.0';
  public static $applicationprofileid;
  public static $merchantprofileid;
  public static $site;
  public static $debug;  
  
  /* 
   * setups method set the data provide for configuration. 
   */
  public static function setups($applicationprofileid = null, $merchantprofileid = null, $site = null, $debug = false) {
	self::$applicationprofileid = $applicationprofileid;
	self::$merchantprofileid = $merchantprofileid;
	self::$site = $site;
	self::$debug = $debug;
  }
}

require_once dirname(__FILE__) . '/NorthAmericanBancard/Helpers.php';
require_once dirname(__FILE__) . '/NorthAmericanBancard/Errors.php';
require_once dirname(__FILE__) . '/NorthAmericanBancard/XmlParser.php';
require_once dirname(__FILE__) . '/NorthAmericanBancard/Message.php';
require_once dirname(__FILE__) . '/NorthAmericanBancard/Connection.php';
require_once dirname(__FILE__) . '/NorthAmericanBancard/Transaction.php';
require_once dirname(__FILE__) . '/NorthAmericanBancard/Processor.php';



/* 
 * check php version if below 5.2.1 then throw exception msg.
 */
if (version_compare(PHP_VERSION, '5.2.1', '<')) {
  throw new Exception('PHP version >= 5.2.1 required');
}

/* 
 * check the dependency of curl, simplexml, openssl loaded or not.
 */
function checkDependencies(){
  $extensions = array('curl', 'SimpleXML', 'openssl');
  foreach ($extensions AS $ext) {
    if (!extension_loaded($ext)) {
      throw new Exception('NorthAmericanBancard-client-php requires the ' . $ext . ' extension.');
    }
  }
}

checkDependencies();

