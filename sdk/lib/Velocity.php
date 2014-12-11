<?php

abstract class VelocityCon {

  const VERSION = '1.0';
  public static $applicationprofileid;
  public static $merchantprofileid;
  public static $site;
  public static $identitytoken;
  public static $workflowid;
  public static $debug;  
  
  /* 
   * setups method set the data provide for configuration. 
   * @param srting $applicationprofileid provided by velocity.
   * @param srting $merchantprofileid provided by velocity.
   * @param srting $site provided by velocity base url of gateway for rest request.
   * @param srting $identitytoken provided by velocity.
   * @param srting $workflowid provided by velocity.
   * @param boolean $debug use for check the request and response of gateway. 
   */
  public static function setups($applicationprofileid = null, $merchantprofileid = null, $site = null, $identitytoken = null, $workflowid = null, $debug = false) {
	self::$applicationprofileid = $applicationprofileid;
	self::$merchantprofileid = $merchantprofileid;
	self::$site = $site;
	self::$identitytoken = $identitytoken;
	self::$workflowid = $workflowid;
	self::$debug = $debug;
  }
}

require_once 'Velocity/Helpers.php';
require_once 'Velocity/Errors.php';
require_once 'Velocity/XmlParser.php';
require_once 'Velocity/Message.php';
require_once 'Velocity/XmlCreator.php';
require_once 'Velocity/Connection.php';
require_once 'Velocity/Transaction.php';
require_once 'Velocity/Processor.php';

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
      throw new Exception('Velocity-client-php requires the ' . $ext . ' extension.');
    }
  }
}

checkDependencies();

