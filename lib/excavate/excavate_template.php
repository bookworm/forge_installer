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
 * Sub Excavation class for Templates.
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
class Excavate_Template extends Excavate_Core
{  
  /**
   * Reference to the Excavation parent.
   *
   * @var obj
   **/          
  var $parent; 
  
  /**
   * Reference to Joomla! JFactory::getDBO() Object.
   *
   * @var obj
   **/
  var $db;      
  
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
   * Client ID. Apparently Joomla! supports multiple clients some sort of future multisite thing I guess.
   *
   * @var string
   **/
  var $clientId;   
    
  /**
   * Name element from manifest file.
   *
   * @var string
   **/
  var $name;
  
  /**
   * Constructor
   */
  public function __construct()
  {
    $this->db  = JFactory::getDBO();   
    $this->log = KLogger::instance(TMP_PATH.DS.'log', KLogger::INFO);       
    
    $config = Forge_Configuration::getInstance();  
    $this->db->select($config->config['db']);
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
   * Determine the paths to install to.
   *
   * @return bool
   */
  public function task_determinePaths()
  {    
    jimport('joomla.application.helper');
    
    if ($cname = $this->root->attributes('client')) 
    {
      $client = JApplicationHelper::getClientInfo($cname, true);  
      
      if ($client === false) 
        return false;        
        
      $basePath       = $client->path;
      $this->clientId = $client->id;
    } 
    else
    {
      // No client attribute was found so we assume the site as the client
      $cname          = 'site';
      $basePath       = JPATH_SITE;
      $this->clientId = 0;
    } 
    
    $name       = $this->root->getElementByPath('name');
    $name       = JFilterInput::clean($name->data(), 'cmd');  
    $this->name = $name;
    
    // Set the template root path
    $this->parent->setPath('extension_root', $basePath.DS.'templates'.DS.strtolower(str_replace(" ", "_", $name)));
          
    return true;
  }
  
// ------------------------------------------------------------------------
  
  /**
   * Creates the installation folder.
   *
   * @return bool
   */
  public function task_create()
  {   
    return JFolder::create($this->parent->getPath('extension_root'));       
  }  
  
// ------------------------------------------------------------------------

  /**
   * Copies the files to the installation folder.
   *
   * @return bool
   */
  public function task_copy()
  {    
    if(!$this->parent->parseFiles($this->root->getElementByPath('files'), -1)) return false;    
     
    $this->parent->parseFiles($this->root->getElementByPath('images'));  
    $this->parent->parseFiles($this->root->getElementByPath('css'));  
    $this->parent->parseFiles($this->root->getElementByPath('media'), $this->clientId);
    $this->parent->parseLanguages($this->root->getElementByPath('languages'));
    $this->parent->parseLanguages($this->root->getElementByPath('administration/languages'), 1);   
    
    if(!$this->parent->copyManifest(-1)) return false;  
    
    $lang = JFactory::getLanguage();
    $lang->load('tpl_'.$this->name);
    return true;
  }  
  
// ------------------------------------------------------------------------

  /**
   * Uninstall task.
   *
   * @return bool                     
   * @todo Everything.
   */  
  public function task_copy_rollback()
  {  
  }
}