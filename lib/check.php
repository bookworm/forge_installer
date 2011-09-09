<?php 

// no direct access
defined( '_Forge' ) or die( 'Restricted access' );        

// ------------------------------------------------------------------------

/**
 * Compatibility and settings checking class.
 *    
 * @package     ForgeInstaller
 * @version     1.0 Beta
 * @author      Ken Erickson AKA Bookworm http://bookwormproductions.net
 * @copyright   Copyright 2009 - 2011 Design BreakDown, LLC.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv3       
 */
class JCheck 
{       
  /**
   * Contains all the options/settings. Both the required and optional.
   *
   * @var array $phpOptions 
   **/   
  var $phpOptions;    
   
  /**
   * Constructor Function. Keeps the PHP Gods Happy.
   *
   * @return void
   **/
  public function __construct()
  {
    $this->check();
  }  

// ------------------------------------------------------------------------
       
  /**
   * Loops through the required and optional arrays and checks each value. 
   * Puts the resulting array into $this->phpOptions;
   *
   * @return void
   **/
  public function check()
  {
    $phpRequired = $this->options('required');
    $phpOptions  = array();   
    //$phpRecommended = $this->options('recommended');
    
    foreach($phpRequired as $key => $val)  
    {     
      $newObj             = $val;
      $newObj['optional'] = false;
      $phpOptions[$key]   = $newObj;
    }
    
    /*
    foreach($phpRecommended as $key => $val)  
    {         
      $key = count($phpOptions) + $key;
      $newObj = $val;
      $newObj['optional'] = true;
      $phpOptions[$key] = $newObj; 
    } 
    */           
    
    $this->phpOptions = $phpOptions; 
    unset($phpRequired);
    // unset($phpRecommended);  
    unset($phpOptions);
  }       
  
// ------------------------------------------------------------------------
  
  /**
   * Loops through the required array and sees if any of the required settings are unmet.
   *
   * @return void
   **/
  public function failed()
  { 
    return arrayFind('1', $this->options('required'), true, true); 
  } 
  
// ------------------------------------------------------------------------

  /**
   * Holds the required and recommended setting's arrays.
   *
   * @return array
   **/
  public function options($needed = 'required')
  {
    $phpRequired[] = array (
      'label' => Forge_Text::_('PHP_VERSION').'>= 5.2',
      'state' => !(phpversion() < '5.2')
    ); 
    
    $phpRequired[] = array (
      'label' => '- '.Forge_Text::_('ZLIB_SUPPORT'),
      'state' => extension_loaded('zlib')   
    );   
    
    $phpRequired[] = array (
      'label' => '- '.Forge_Text::_('XML_SUPPORT'),
      'state' => extension_loaded('xml')
    );  
    
    $phpRequired[] = array (
      'label' => '- '.Forge_Text::_('MYSQL_SUPPORT'),
      'state' => (function_exists('mysql_connect') || function_exists('mysqli_connect'))
    ); 
    
    $phpRequired[] = array (
      'label' => '- '.Forge_Text::_('CURL_SUPPORT'),
      'state' => extension_loaded('curl')
    );   
    
    $phpRequired[] = array (
      'label' => '- '.Forge_Text::_('PERMISSION_CHECK'),
      'state' => $this->fsPermissionsCheck()
    );

    if(file_exists(JPATH_SITE.DS.'configuration.php') AND is_writable(JPATH_SITE.DS.'configuration.php')) $cW = true;  
    else { $cW = is_writable(JPATH_SITE); }  
    
    $phpRequired[] = array (
      'label'  => 'configuration.php '.Forge_Text::_('WRITABLE'),
      'state'  => $cW ? true : false,
    );
    
    if($needed == 'required') return $phpRequired;
    else if($needed == 'recommended') return $phpRecommended;   
  }
  
// ------------------------------------------------------------------------
    
  /**
   * Permissions Check.
   *
   * @return bool
   **/
  public function fsPermissionsCheck()
  {
    if(!is_writable(JPATH_ROOT.DS.'tmp'))
      return false;
    if(!mkdir(JPATH_ROOT.DS.'tmp'.DS.'test', 0755))
      return false;
    if(!copy(JPATH_ROOT.DS.'tmp'.DS.'index.html', JPATH_ROOT.DS.'tmp'.DS.'test'.DS.'index.html'))
      return false;
    if(!chmod(JPATH_ROOT.DS.'tmp'.DS.'test'.DS.'index.html', 0777))
      return false;
    if(!unlink(JPATH_ROOT.DS.'tmp'.DS.'test'.DS.'index.html'))
      return false;
    if(!rmdir(JPATH_ROOT.DS.'tmp'.DS.'test'))
      return false;  
    if(!is_writable(JPATH_ROOT.DS.'forge'.DS.'vendor'))
      return false;
    if(!is_writable(JPATH_ROOT.DS.'forge'.DS.'vendor'.DS.'cache'))
      return false;  
    if(!is_writable(JPATH_ROOT.DS.'tmp'.DS.'forge'))
      return false;           
        
    return true;
  } 
  
// ------------------------------------------------------------------------
  
  /**
   * Verify the FTP configuration values are valid
   *
   * @param string  $user Username of the ftp user to determine root for
   * @param string  $pass Password of the ftp user to determine root for
   * @return mixed bool true on success otherwise error string. 
   */
  public function FTPVerify($user, $pass, $root, $host='127.0.0.1', $port='21')
  {
    jimport('joomla.client.ftp');   
    $ftp = & JFTP::getInstance($host, $port);

    // Since the root path will be trimmed when it gets saved to configuration.php, we want to test with the same value as well
    $root = rtrim($root, '/');    
    
    // Verify connection
    if (!$ftp->isConnected())
      return 'Failed To Connect';  
      
    // Verify username and password
    if (!$ftp->login($user, $pass))
      return 'Username or password wrong';

    // Verify PWD function
    if ($ftp->pwd() === false)
      return "Your Server doesn't appear to support the PWD function";

    // Verify root path exists
    if (!$ftp->chdir($root))
      return 'The FTP root path does not exist';

    // Verify NLST function
    if (($rootList = $ftp->listNames()) === false)
      return "Your Server doesn't appear to support the NLST function"; 

    // Verify LIST function
    if ($ftp->listDetails() === false)
      return "Your Server doesn't appear to support the LIST function"; 

    // Verify SYST function
    if ($ftp->syst() === false)
      return "Your Server doesn't appear to support the SYST function"; 

    // Verify valid root path, part one
    $checkList = array('CHANGELOG.php', 'COPYRIGHT.php', 'index.php', 'INSTALL.php', 'LICENSE.php');
    if (count(array_diff($checkList, $rootList))) 
      return "Invalid Root Path";   
      
    // Verify RETR function
    $buffer = null;
    if ($ftp->read($root.'/libraries/joomla/version.php', $buffer) === false)
      return "Your Server doesn't appear to support the RETR function";

    // Verify valid root path, part two
    $checkValue = file_get_contents(JPATH_LIBRARIES.DS.'joomla'.DS.'version.php');
    if ($buffer !== $checkValue)
      return "Invalid Root Path";

    // Verify STOR function
    if ($ftp->create($root.'/ftp_testfile') === false)
      return "Your Server doesn't appear to support the STOR function"; 

    // Verify DELE function
    if ($ftp->delete($root.'/ftp_testfile') === false)
      return "Your Server doesn't appear to support the DELE function"; ;

    // Verify MKD function
    if ($ftp->mkdir($root.'/ftp_testdir') === false)
      return "Your Server doesn't appear to support the MKD function"; 

    // Verify RMD function
    if ($ftp->delete($root.'/ftp_testdir') === false)
      return "Your Server doesn't appear to support the RMD function"; 

    $ftp->quit();
    return true;
  }
}