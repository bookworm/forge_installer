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
 * Sub Excavation class for Modules.
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
class Excavate_Module extends Excavate_Core 
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
   * The active element form the manifest. In this case $this->root->getElementByPath('files');
   *
   * @var string
   **/ 
  var $element;
  
  /**
   * Module name attribute from the manifest file.
   *
   * @var string
   **/     
  var $mname; 
  
  /**
   * The Database row.
   *
   * @var string
   **/ 
  var $row;
  
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
    
    $name       = $this->manifest->getElementByPath('name');
    $name       = JFilterInput::clean($name->data(), 'string');
    $this->name = $name;         
    
    if ($cname = $this->root->attributes('client')) 
    {
      $client = JApplicationHelper::getClientInfo($cname, true);
      
      if ($client === false) 
        return false;    
        
      $basePath = $client->path;
      $this->clientId = $client->id;
    } 
    else
    {
      // No client attribute was found so we assume the site as the client
      $cname = 'site';
      $basePath = JPATH_SITE;
      $this->clientId = 0;
    }     
    
    $this->element = $this->root->getElementByPath('files');              
    
    if (is_a($this->element, 'JSimpleXMLElement') && count($this->element->children())) 
    {
      $files = $this->element->children();
      foreach ($files as $file)
      {
        if ($file->attributes('module')) {
          $this->mname = $file->attributes('module');
          break;
        }
      }
    }
    
    $this->parent->setPath('extension_root', $basePath.DS.'modules'.DS.$this->mname);
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
    if(!$this->parent->parseFiles($this->element, -1))
      return false;  
    if(!$this->parent->parseMedia($this->root->getElementByPath('media'), $this->clientId))
      return false;        
    if(!$this->parent->parseLanguages($this->root->getElementByPath('languages'), $this->clientId))
      return false;   
      
    return true;
  }     
  
// ------------------------------------------------------------------------

  /**
   * Checks to see if the module has already been installed.
   *
   * @return bool 
   * @todo everything
   */  
  public function tdtsk_checkAlreadyInstalled()
  {

  }    
  
// ------------------------------------------------------------------------

  /**
   * Inserts the SQL for the module.
   *
   * @return bool
   */ 
  public function task_insertSQL()
  {   
    $errorMSG = 'Failed to insert SQL for: ' . $this->parent->artifact->name;   
    
    $this->row = JTable::getInstance('module');
    $this->row->title = $this->name;
    $this->row->ordering = $this->row->getNextOrder( "position='left'" );
    $this->row->position = 'left';
    $this->row->showtitle = 1;
    $this->row->iscore = 0;
    $this->row->access = $this->clientId == 1 ? 2 : 0;
    $this->row->client_id = $this->clientId;
    $this->row->module = $this->mname;
    $this->row->params = $this->parent->getParams();
    
    if(!$this->row->store() === null)
    {  
      $this->log->logError($com->getErrorMsg());
      $this->log->logError($errorMSG);    
      $this->errorMSG = $errorMSG;
      return false;         
    }    
    
    return true;  
  }   
  
// ------------------------------------------------------------------------

  /**
   * Removes the SQL for the module.  
   *
   * @return bool  
   * @todo everything 
   */   
  public function task_insertSQL_rollback() 
  {   
  } 
  
// ------------------------------------------------------------------------

  /**
   * Inserts the SQL for the menu items.
   *
   * @return bool
   */     
  public function task_insertMenuSQL()
  { 
    $errorMSG = 'Failed to insert Menu SQL for: ' . $this->parent->artifact->name;
    
    $query = 'INSERT INTO `#__modules_menu` ' .
        ' VALUES ('.(int) $this->row->id.', 0 )';
    $this->db->setQuery($query);
       
    if(!$this->db->query())
    { 
      $this->log->logError($this->db->getErrorMsg()); 
      $this->log->logError($errorMSG);   
      $this->errorMSG = $errorMSG; 
      return false;
    }         
    
    return true;
  }  
  
// ------------------------------------------------------------------------

  /**
   * Removes the SQL for the menu items.
   *
   * @return bool  
   * @todo everything
   */   
  public function task_insertMenuSQL_rollback()
  {
    
  }       
  
// ------------------------------------------------------------------------

  /**
   * Finishes up the Excavation/installation by copying the manifest file.
   *
   * @return bool
   */ 
  public function task_finish()
  {
    if(!$this->parent->copyManifest(-1))   
    {
      $this->log->logError('Failed to copy manifest for: ' . $this->parent->artifact->name);
      $this->errorMSG = 'Failed to copy manifest for: ' . $this->parent->artifact->name;   
      return false; 
    }
      
    return true;
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