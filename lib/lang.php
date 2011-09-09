<?php

// no direct access
defined( '_Forge' ) or die( 'Restricted access' );        

// ------------------------------------------------------------------------

/**
 * Language handling class. Borrowed from Akeeba.
 *    
 * @package     ForgeInstaller
 * @subpackage  core
 * @version     1.0 Beta
 * @author      Ken Erickson AKA Bookworm http://bookwormproductions.net
 * @copyright   Copyright 2009 - 2011 Design BreakDown, LLC.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv3       
 */     
class FI_Text
{    
  /**
   * An associative array of translation strings
   * @var array
   */
  var $_lang;   
  
  /**
   * Class constructor. Loads the translation files for the installer,
   * honoring user's browser settings.        
   *
   * @return Forge_Text
   */
  public function __construct()
  {
    // Load default language (English)
    $langEnglish = $this->parseLangFile(JPATH_INSTALLATION.DS.'lang'.DS.'en.ini');

    // Try to get user's preffered language (set in browser's settings and transmitted through the request)
    $prefLang     = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    $prefFileName = JPATH_INSTALLATION.DS.'lang'.DS.$prefLang.'.ini';   
    
    if( file_exists($prefFileName) && ($prefLang != 'en') ) 
    {
      $langLocal   = $this->parseLangFile($prefFileName);
      $this->_lang = array_merge($langEnglish, $langLocal);
      unset( $langLocal );
      unset( $langEnglish );
    } 
    else {
      $this->_lang = $langEnglish;
    }
  }
  
// ------------------------------------------------------------------------     

  /**
   * Singleton implementation
   *
   * @return obj $instance
   */
  public function &getInstance()
  {
    static $instance;

    if(!is_object($instance))
      $instance = new Forge_Text();

    return $instance;
  }               
  
// ------------------------------------------------------------------------    
  
  /**
   * Parses a language file into a lang key + lang text array.
   *  
   * @param string $filename Name of language file. Should contain full path to file.
   * @return array $ret
   */
  public function parseLangFile($filename)
  {
    $ret = array();      
    
    if(!file_exists($filename)) return array();    
    
    $lines = file($filename);   
    
    foreach($lines as $line)
    {
      $line = ltrim($line);
      if( (substr($line,0,1) == '#') || (substr($line,0,2) == '//') ) continue;
      $entries = explode('=',$line,2);
      if(isset($entries[1])) $ret[$entries[0]] = rtrim($entries[1],"\n");
    }   
    
    return $ret;
  }
  
// ------------------------------------------------------------------------

  /**
   * Performs the real translation of the static _() function      
   *
   * @param $key string Translation key
   * @return string Translation text
   */
  public function _realTranslate($key)
  {
    if(array_key_exists($key, $this->_lang))
      return $this->_lang[$key];
    else
      return $key;
  }
  
// ------------------------------------------------------------------------

  /**
   * Returns the translation text of a given key         
   *
   * @param $key string Translation key
   * @return string Translation text
   */
  public function _($key)
  { 
    static $instance;

    if(!is_object($instance))
      $instance = new Forge_Text();
      
    return $instance->_realTranslate($key);
  }
} 