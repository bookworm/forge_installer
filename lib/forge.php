<?php

// no direct access
defined( '_Forge' ) or die( 'Restricted access' );        

// ------------------------------------------------------------------------

/**
 * Core Forge Class. Gets the Dig Going.
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
class Forge
{ 
  /**
   * Holds a list of the artifacts.
   *
   * @note This is a true list containing just the artifact names, i.e array('Notepad', 'K2');
   *                                
   * @var array $artifacts
   **/ 
  var $artifacts = array();  
  
  /**
   * Forge Configuration Options. Not currently utilized.
   *                              
   * @var array $config          
   * @todo Utilize this to set Forge options.
   **/ 
  var $config;
  
  /**
   * Constructor Function. Keeps the PHP Gods Happy.
   * 
   * @param array $config Config array.
   * @return void
   **/
  public function __construct($config = null)
  { 
    if(!is_null($config)) 
      $this->config = $config;
    else
    { 
      if(file_exists(FORGE_PATH.DS.'config'.DS.'config.json'))
      {     
        $config       = file_get_contents(FORGE_PATH.DS.'config'.DS.'config.json');
        $config       = preg_replace( '/\s*(?!<\")\/\*[^\*]+\*\/(?!\")\s*/' , '' , $config);  
        $config       = preg_replace('#/\*[^*]*\*+([^/*][^*]*\*+)*/#', '' , $config); 
        $this->config = json_decode($config);   
        
        unset($config);
      }      
    }          
    
    foreach($this->config->artifacts as $artifact) {
      $this->artifacts[] = strtolower($artifact);
    }
  }
  
// ------------------------------------------------------------------------

  /**                       
   * Singleton function.  
   *
   * @return obj Forge_Configuration::
   */
  public function &getInstance($config = null)
  {
    static $instance; 

    if(!is_object($instance))
      $instance = new Forge($config);   

    return $instance;
  }   
}