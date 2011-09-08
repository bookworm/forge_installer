<?php   

// no direct access
defined( '_Forge' ) or die( 'Restricted access' );        

// ------------------------------------------------------------------------

/**
 * Load.
 *    
 * @package     ForgeInstaller
 * @subpackage  core
 * @version     1.0 Beta
 * @author      Ken Erickson AKA Bookworm http://bookwormproductions.net
 * @copyright   Copyright 2009 - 2011 Design BreakDown, LLC.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2       
 * please visit the DBD club site http://club.designbreakdown.com for support. 
 * Do not e-mail (or god forbid IM or call) me directly.
 */
class JConfiguration
{ 
  /**
   * Holds JConfig Class Vars. 
   * Pulled from either configuration.php or configuration.php-dist
   *
   * @var array $config.
   **/
  var $config;   
  
  /**
   * Whether or not the configuration.php exists.
   * If it doesn't both functions inside and outside this class need to know to use configuration.php-dist
   *
   * @var bool $jconfigExists
   **/
  var $jconfigExists = false;       
  
  /**
   * Constructor.  
   *
   * @return obj Forge_Configuration::    
   * @see Forge_Configuration::parseConfigFile()
   */
  public function __construct()
  {
    if(file_exists(JPATH_SITE.DS.'configuration.php')) { 
      $this->config        = $this->parseConfigFile(JPATH_SITE . DS . 'configuration.php');   
      $this->jconfigExists = true;
    } 
    else {
      $this->config = $this->parseConfigFile(dirname(dirname(__FILE__)) . DS . 'config' . DS .  'jconfig-dist.php'); 
    }
  }

// ------------------------------------------------------------------------

  /**                       
   * Singleton function.  
   *
   * @return obj Forge_Configuration::
   */
  public function &getInstance()
  {
    static $instance;   
    
    if(!is_object($instance))
      $instance = new Forge_Configuration();      
      
    return $instance;
  }

// ------------------------------------------------------------------------
  
  /**                       
   * Parses configuration file variables and puts them into an array. 
   *   
   * @param string $filename Name of the file to load. Must include full path.
   * @return array $ret.
   */
  public function parseConfigFile($filename)
  {
    $ret = array();  
    include_once $filename;   
    
    if(class_exists('JConfig'))
    {   
      foreach(get_class_vars('JConfig') as $key => $value) {
        $ret[$key] = $value;
      }
    }      
    
    return $ret;
  }   

// ------------------------------------------------------------------------
  
  /**                       
   * Saves the configuration array to configuration.php.
   *   
   * @param array  $configArray Configuration object array.
   * @return void    
   * @see Forge_Configuration:genDefaultConfigObj()  
   * @see Forge_Configuration::genConfigFile()     
   * @todo Use write file function to handle non existent file_put_contents.
   */
  public function saveConfig($configArray)
  { 
    $newConfig = array_merge($this->config, $configArray);   
    $newConfig = $this->genConfigFile($newConfig);     
    
    if(is_writable(JPATH_SITE))
      file_put_contents(JPATH_SITE.DS.'configuration.php', $newConfig);
  }  

// ------------------------------------------------------------------------
  
  /**                       
   * Generates a configuration.php file from a configuration array.
   *   
   * @return string $out  
   * @see Forge_Configuration::saveConfig() 
   */
  public function genConfigFile($config)
  {
    $out =  "<?php\n";
    $out .= "class JConfig {\n";
    
    foreach($config as $name => $value)
    {
      if(is_array($value))
      {
        $temp = '(';
        foreach($value as $key => $data)
        {
          if(strlen($temp) > 1) $temp .= ', ';
          $temp .= '\'$key\' => \'$data\'';
        }
        $temp .= ')';
        $value = 'array '.$temp;
      }
      else  {
        $value = "'".addslashes($value)."'";
      }
      $out .= "\t" . 'var $' . $name . " = ". $value .";\n";
    }

    $out .= '}' . "\n";

    return $out;
  }       
}