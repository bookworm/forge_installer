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
 * Sub Excavation class for Plugins.
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
class Excavate_Plugin extends Excavate_Core
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
   * The active element form the manifest. In this case $this->root->getElementByPath('files');
   *
   * @var string
   **/ 
  var $element;    
  
  /**
   * The group attribute form the manifest file.
   *
   * @var string
   **/
  var $group;    
  
  /**
   * File type attribute from the manifest file.
   *
   * @var string
   **/
  var $pname;  
  
  /**
   * Name element from manifest file.
   *
   * @var string
   **/
  var $name;    
  
  /**
   * Database object ID.
   *
   * @var mixed
   **/
  var $id;
  
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
    $name       = $this->root->getElementByPath('name');
    $name       = JFilterInput::clean($name->data(), 'string');
    $this->name = $name;
    
    $type = $this->root->attributes('type');

    // Set the installation path
    $this->element = $this->root->getElementByPath('files');
    if (is_a($this->element, 'JSimpleXMLElement') && count($this->element->children())) 
    {
      $files = $this->element->children();
      foreach ($files as $file) 
      {
        if ($file->attributes($type)) {
          $this->pname = $file->attributes($type);
          break;
        }
      }
    }  
    
    $this->group = $this->root->attributes('group');
    $this->parent->setPath('extension_root', JPATH_ROOT.DS.'plugins'.DS.$this->group);  
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
   * Copys the files to the installation folder.
   *
   * @return bool
   */
  public function task_copy() 
  {
    return $this->parent->parseFiles($this->element, -1);
  }       
   
// ------------------------------------------------------------------------

  /**
   * Checks to see if the plugin has already been installed.
   *
   * @return bool 
   * @todo everything
   */   
  public function tdisk_checkAlreadyInstalled()
  {
    $query = 'SELECT `id`' .
        ' FROM `#__plugins`' .
        ' WHERE folder = '.$this->db->Quote($this->group) .
        ' AND element = '.$this->db->Quote($this->pname);
    $this->db->setQuery($query);    
    
    if(!$this->db->query())
    { 
      $this->log->logError($this->db->getErrorMsg()); 
      $this->log->logError($errorMSG);   
      $this->errorMSG = $errorMSG; 
      return false;
    }      
    
    $this->id = $this->db->loadResult();
  } 
  
// ------------------------------------------------------------------------

  /**
   * Inserts the SQL for the plugin.
   *
   * @return bool
   */ 
  public function task_insertSQL()
  { 
    $errorMSG = 'Failed to insert SQL for: ' . $this->parent->artifact->name;   
                    
    $row            = JTable::getInstance('plugin');
    $row->name      = $this->name;
    $row->ordering  = 0;
    $row->folder    = $this->group;
    $row->iscore    = 0;
    $row->access    = 0;
    $row->client_id = 0;
    $row->element   = $this->pname;
    $row->params    = $this->parent->getParams();

    // Editor plugins are published by default
    if ($this->group == 'editors') 
      $row->published = 1;

    if(!$row->store() === null)
    {  
      $this->log->logError($row->getErrorMsg());
      $this->log->logError($errorMSG);    
      $this->errorMSG = $errorMSG;
      return false;         
    }    
    
    return true;
  }
     
// ------------------------------------------------------------------------

  /**
   * Finishes up the Excavation/installation by copying the manifest file.
   *
   * @return bool
   */  
  public function task_finish()
  {
    return $this->parent->copyManifest(-1);
  } 
  
// ------------------------------------------------------------------------

  /**
   * Uninstall task.
   *
   * @return bool                     
   * @todo Everything.
   */  
  public function task_finish_rollback()
  { 
  }
}