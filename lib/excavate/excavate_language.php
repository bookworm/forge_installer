<?php

// no direct access
defined( '_Forge' ) or die( 'Restricted access' );    

// Import Prequisites
jimport('joomla.filesystem.file');  
jimport('joomla.filesystem.folder');   
jimport('joomla.application.helper');   
jimport('joomla.filter.filterinput');

// ------------------------------------------------------------------------

/**
 * Sub Excavation class for Languages.
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
class Excavate_Language extends Excavate_Core
{  
  /**
   * Reference to the Excavation parent.
   *
   * @var obj
   **/          
  var $parent;
  
  /**
   * Holds the manifest XML file for the Artifact being Excavated.
   *
   * @var string
   **/  
  var $manifest;
  
  /**
   * Root element in the manifest file.
   *
   * @var string
   **/
  var $root;
   
  /**
   * Name element from manifest file.
   *
   * @var string
   **/
  var $name;
                
  /**
   * Core language pack flag   
   *
   * @var boolean
   */
  var $_core = false; 
  
  /**
   * Holds the active element tag.
   *
   * @var mixed
   */  
   var $tag;
  
  /**
   * Constructor
   */
  public function __construct()
  {     
    $this->log = KLogger::instance(TMP_PATH.DS.'log', KLogger::INFO);  
  }  
  
// ------------------------------------------------------------------------

  /**
   * Called after creation once the parent is accessible.
   */
  public function init()
  { 
    $this->manifest = $this->parent->getManifest();   
    $this->root     = $this->manifest->document;
  } 

/*** 
  // ------------------------------------------------------------------------------------------
  ** Begin task Section. Make Sure to keep your tasks in the order they should be executed. 
  // ------------------------------------------------------------------------------------------
**/     
  
  /**
   * Installation task.
   *
   * Note: Sadly it it looks like this cant be broken up further.
   *  Not a big deal since it only going to be a max of two files but still annoying.   
   *   
   * @return bool
   */
  public function task_install()
  { 
    jimport('joomla.application.helper');
    
    $this->manifest = $this->manifest->document;
    $root = $this->root;     
    
    if($root->attributes('client') == 'both')
    {
      $siteElement = $root->getElementByPath('site');
      $element     = $siteElement->getElementByPath('files');  
      
      if(!$this->_install('site', JPATH_SITE, 0, $element))
        return false;

      $adminElement = $root->getElementByPath('administration');
      $element      = $adminElement->getElementByPath('files');  
      
      if(!$this->_install('administrator', JPATH_ADMINISTRATOR, 1, $element)) 
        return false;

      return true;
    }
    elseif ($cname = $root->attributes('client'))
    {
      $client = JApplicationHelper::getClientInfo($cname, true);  
      
      if ($client === null) 
        return false; 
        
      $basePath = $client->path;
      $clientId = $client->id;
      $element  = $root->getElementByPath('files');

      return $this->_install($cname, $basePath, $clientId, $element);
    }
    else
    {
      // No client attribute was found so we assume the site as the client
      $cname    = 'site';
      $basePath = JPATH_SITE;
      $clientId = 0;
      $element  = $root->getElementByPath('files');

      return $this->_install($cname, $basePath, $clientId, $element);
    } 
  } 
  
// ------------------------------------------------------------------------
  
  /**
   * Helper function for installation.
   *    
   * @note A brief explanation on clients.
   *  Joomla! 1.5 has this concept of clients, which is basically a system for allowing multiple core configruations 
   *  of Joomla to operate side by side. In Joomla! 1.5s case it was only utilized to provide and installer 
   *  client and application client. It could be used to say provide an Ajax mode that only loaded the routing libraries and
   *  none of the rendering overhead.  
   *
   * @param string $cname Name of the client.
   * @param string $basepath Base path to client.
   * @param int    $clientId Id of the client.
   * @param object $element XML Element.
   * @return bool
   */
  public function _install($cname, $basePath, $clientId, &$element)
  {
    $this->manifest = $this->manifest->document;
    $root           = $this->root;
    
    $name       = $this->manifest->getElementByPath('name');
    $name       = JFilterInput::clean($name->data(), 'cmd');
    $this->name = $name;
    
    $tag = $root->getElementByPath('tag');
             
    $this->tag = $tag;    
    $folder    = $tag->data();    
    
    $this->parent->setPath('extension_site', $basePath.DS."language".DS.$this->tag);     
    
    if (is_a($element, 'JSimpleXMLElement') && count($element->children())) 
    {
      $files = $element->children();
      foreach ($files as $file)
      {
        if ($file->attributes('file') == 'meta') {
          $this->_core = true;
          break;
        }
      }
    }
    
    if (!$this->_core) 
    {
      if (!JFile::exists($this->parent->getPath('extension_site').DS.$this->tag.'.xml')) 
      {          
        $errorMSG = 'No core pack exists for the language: '. $this->parent->artifact->name;
        $this->log->logError($errorMSG);   
        $this->errorMSG = $errorMSG;
        return false;
      }
    } 
    
    if(!JFolder::create($this->parent->getPath('extension_site'))) 
      return false;
    
    if(!$this->parent->parseFiles($element))
      return false;
      
    return true;
  }  
     
// ------------------------------------------------------------------------

  /**
   * Uninstall task.
   *
   * @return bool                     
   * @todo Everything.
   */ 
  public function tdtsk_install_rollback()
  {
  }        
}