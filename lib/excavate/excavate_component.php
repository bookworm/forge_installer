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
class Excavate_Component extends Excavate_Core
{
  /**
   * Reference to Joomla! JFactory::getDBO() Object.
   *
   * @var obj
   **/
  var $db;
  
  /**
   * Holds the manifest XML file for the Artifact being Excavated.
   *
   * @var object JSimpleXMLElement
   **/  
  var $manifest;
   
  /**
   * Root element in the manifest file.
   *
   * @var object JSimpleXMLElement
   **/
  var $root;
  
  /**
   * Name element from manifest file.
   *
   * @var string
   **/
  var $name; 
  
  /**
   * Admin element from the manifest file.
   *
   * @var object JSimpleXMLElement
   **/     
  var $adminElement;
  
  /**
   * Install element from the manifest file.
   *
   * @var object JSimpleXMLElement
   **/     
  var $installElement; 
  
  /**
   * Uninstall element from the manifest file.
   *
   * @var object JSimpleXMLElement
   **/
  var $uninstallElement; 
  
  /**
   * Installation script path + filename
   *
   * @var string
   **/        
  var $installScript;
  
  /**
   * Constructor
   *
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
    $this->manifest = $this->root;
    $name           = $this->manifest->getElementByPath('name');
    $name           = JFilterInput::clean($name->data(), 'cmd');
    $this->name     = $name; 
    
    $this->adminElement     = $this->manifest->getElementByPath('administration');
    $this->installElement   = $this->manifest->getElementByPath('install');
    $this->uninstallElement = $this->manifest->getElementByPath('uninstall');      
    
    $this->parent->setPath('extension_site', JPath::clean(JPATH_SITE.DS."components".DS.strtolower("com_".str_replace(" ", "", $this->name))));
    $this->parent->setPath('extension_administrator', JPath::clean(JPATH_ADMINISTRATOR.DS."components".DS.strtolower("com_".str_replace(" ", "", $this->name))));
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
    if(!JFolder::create($this->parent->getPath('extension_administrator'))) return false;  
    if(!JFolder::create($this->parent->getPath('extension_administrator'))) return false;   
 
    return true;
  }   
  
// ------------------------------------------------------------------------

  /**
   * Copies the files to the installation folder.
   *
   * @return bool
   */  
  public function task_copy()
  {
    foreach($this->manifest->children() as $child)
    {
      if(is_a($child, 'JSimpleXMLElement') && $child->name() == 'files') 
      {
        if ($this->parent->parseFiles($child) == false) 
        {  
          $this->log->logError('Failed to parse files for: ' . $this->parent->artifact->name);   
          $this->errorMSG = 'Failed to parse files for: ' . $this->parent->artifact->name;
          return false;
        }
      }
    }  
    
    foreach($this->adminElement->children() as $child)
    {
      if(is_a($child, 'JSimpleXMLElement') && $child->name() == 'files') 
      {
        if($this->parent->parseFiles($child, 1) == false) 
        {
          # $this->parent->abort();  
          $this->log->logError('Failed to parse files for: ' . $this->parent->artifact->name);   
          $this->errorMSG = 'Failed to parse files for: ' . $this->parent->artifact->name;
          return false;
        }
      }
    }    
    
    $this->parent->parseMedia($this->manifest->getElementByPath('media'));
    $this->parent->parseLanguages($this->manifest->getElementByPath('languages'));
    $this->parent->parseLanguages($this->manifest->getElementByPath('administration/languages'), 1); 

    $installScriptElement = $this->manifest->getElementByPath('installfile');
    if (is_a($installScriptElement, 'JSimpleXMLElement')) 
    {
      $installScriptFilename = $installScriptElement->data();  
      $path['src']           = $this->parent->getPath('source').DS.$installScriptFilename;
      $path['dest']          = $this->parent->getPath('extension_administrator').DS.$installScriptFilename; 
      $this->installScript   = $installScriptFilename;
        
      if($this->parent->copyFiles(array ($path)) == false)
      {
        $this->log->logError('Failed to copy files for: ' . $this->parent->artifact->name);   
        $this->errorMSG = 'Failed to copy files for: ' . $this->parent->artifact->name;   
        return false;
      }
    }   
    
    $uninstallScriptElement = $this->manifest->getElementByPath('uninstallfile');
    if (is_a($uninstallScriptElement, 'JSimpleXMLElement')) 
    {
      $uninstallScriptFilename = $uninstallScriptElement->data();
      $path['src']  = $this->parent->getPath('source').DS.$uninstallScriptFilename;
      $path['dest'] = $this->parent->getPath('extension_administrator').DS.$uninstallScriptFilename;  
      if($this->parent->copyFiles(array ($path)) == false)
      {
        $this->log->logError('Failed to copy files for: ' . $this->parent->artifact->name);   
        $this->errorMSG = 'Failed to copy files for: ' . $this->parent->artifact->name;   
        return false;
      }                                           
    }   
    
    return true;
  } 
  
// ------------------------------------------------------------------------

  /**
   * Inserts some sql from queries in the XML file.
   *
   * @return bool
   */ 
  public function task_insertSQLFromXML()
  {
    if(!$this->parent->parseQueries($this->manifest->getElementByPath('install/queries'))) 
    {
      $this->log->logError('Failed to parse queries for: ' . $this->parent->artifact->name);   
      $this->errorMSG = 'Failed to parse queries for: ' . $this->parent->artifact->name;
      return false; 
    }
    if(!$this->parent->parseSQLFiles($this->manifest->getElementByPath('install/sql'))) 
    {
      $this->log->logError('Failed to parse SQL files for: ' . $this->parent->artifact->name);   
      $this->errorMSG = 'Failed to parse SQL files for: ' . $this->parent->artifact->name;
      return false;    
    }     
    
    return true;
  }   
  
// ------------------------------------------------------------------------

  /**
   * Builds the menu entry for the component.
   *
   * @return bool
   */   
  public function task_buildAdminMenus()
  {
    $db = $this->db; 
    $option = strtolower("com_".str_replace(" ", "", $this->name));    
    
    $errorMSG = 'Failed to build admin menus for: ' . $this->parent->artifact->name;
       
    if (is_a($menuElement, 'JSimpleXMLElement')) 
    {     
      $menuElement = & $this->adminElement->getElementByPath('menu');
      $db_name = $menuElement->data();
      $db_link = "option=".$option;
      $db_menuid = 0;
      $db_parent = 0;
      $db_admin_menu_link = "option=".$option;
      $db_admin_menu_alt = $menuElement->data();
      $db_option = $option;
      $db_ordering = 0;
      $db_admin_menu_img = ($menuElement->attributes('img')) ? $menuElement->attributes('img') : 'js/ThemeOffice/component.png';
      $db_iscore = 0;
      // use the old params if a previous entry exists
      $db_params = $exists ? $oldparams : $this->parent->getParams();
      // use the old enabled field if a previous entry exists
      $db_enabled = $exists ? $oldenabled : 1;

      // This works because exists will be zero (autoincr)
      // or the old component id
      $query = 'INSERT INTO #__components' .
        ' VALUES( '.$exists .', '.$db->Quote($db_name).', '.$db->Quote($db_link).', '.(int) $db_menuid.',' .
        ' '.(int) $db_parent.', '.$db->Quote($db_admin_menu_link).', '.$db->Quote($db_admin_menu_alt).',' .
        ' '.$db->Quote($db_option).', '.(int) $db_ordering.', '.$db->Quote($db_admin_menu_img).',' .
        ' '.(int) $db_iscore.', '.$db->Quote($db_params).', '.(int) $db_enabled.' )';
      $db->setQuery($query); 
      
      if(!$db->query())
      { 
        $this->log->logError($db->getErrorMsg());
        $this->log->logError($errorMSG); 
        $this->errorMSG = $errorMSG;   
        return false;
      }
    }
    else 
    {
      $query = 'SELECT id' .
          ' FROM #__components' .
          ' WHERE `option` = '.$db->Quote($option) .
          ' AND parent = 0';
      $db->setQuery($query);
      $menuid = $db->loadResult();

      if (!$menuid) 
      {
        $db_name = $this->get('name');
        $db_link = "";
        $db_menuid = 0;
        $db_parent = 0;
        $db_admin_menu_link = "";
        $db_admin_menu_alt = $this->get('name');
        $db_option = $option;
        $db_ordering = 0;
        $db_admin_menu_img = "";
        $db_iscore = 0;
        $db_params = $this->parent->getParams();
        $db_enabled = 1;

        $query = 'INSERT INTO #__components' .
          ' VALUES( "", '.$db->Quote($db_name).', '.$db->Quote($db_link).', '.(int) $db_menuid.',' .
          ' '.(int) $db_parent.', '.$db->Quote($db_admin_menu_link).', '.$db->Quote($db_admin_menu_alt).',' .
          ' '.$db->Quote($db_option).', '.(int) $db_ordering.', '.$db->Quote($db_admin_menu_img).',' .
          ' '.(int) $db_iscore.', '.$db->Quote($db_params).', '.(int) $db_enabled.' )';
        $db->setQuery($query);
        if(!$db->query())
        { 
          $this->log->logError($db->getErrorMsg()); 
          $this->log->logError($errorMSG);   
          $this->errorMSG = $errorMSG; 
          return false;
        }
        $menuid = $db->insertid();
      }
    }              
    
    $ordering = 0;
    $submenu = $this->adminElement->getElementByPath('submenu');
    if (!is_a($submenu, 'JSimpleXMLElement') || !count($submenu->children())) {
      return true;
    }
    
    foreach ($submenu->children() as $child) 
    {
      if (is_a($child, 'JSimpleXMLElement') && $child->name() == 'menu') 
      {
        $com = JTable::getInstance('component');
        $com->name = $child->data();
        $com->link = '';
        $com->menuid = 0;
        $com->parent = $menuid;
        $com->iscore = 0;
        $com->admin_menu_alt = $child->data();
        $com->option = $option;
        $com->ordering = $ordering ++;
        
        // Set the sub menu link
        if ($child->attributes("link")) {
          $com->admin_menu_link = str_replace('&amp;', '&', $child->attributes("link"));
        } 
        else 
        {
          $request = array();
          if ($child->attributes('act')) {
            $request[] = 'act='.$child->attributes('act');
          }
          if ($child->attributes('task')) {
            $request[] = 'task='.$child->attributes('task');
          }
          if ($child->attributes('controller')) {
            $request[] = 'controller='.$child->attributes('controller');
          }
          if ($child->attributes('view')) {
            $request[] = 'view='.$child->attributes('view');
          }
          if ($child->attributes('layout')) {
            $request[] = 'layout='.$child->attributes('layout');
          }
          if ($child->attributes('sub')) {
            $request[] = 'sub='.$child->attributes('sub');
          }
          $qstring = (count($request)) ? '&'.implode('&',$request) : '';
          $com->admin_menu_link = "option=".$option.$qstring;
        }

        // Set the sub menu image
        if ($child->attributes("img")) {
          $com->admin_menu_img = $child->attributes("img");
        } else {
          $com->admin_menu_img = "js/ThemeOffice/component.png";
        }

        // Store the submenu
        if(!$com->store() === null)
        {  
          $this->log->logError($com->getErrorMsg());
          $this->log->logError($errorMSG);    
          $this->errorMSG = $errorMSG;
          return false;         
        }
      }
    }   
    return true;
  }   
  
// ------------------------------------------------------------------------

  /**
   * Runs a installation script found in the manifest.
   *
   * @return bool
   */
  public function task_runInstallScript()
  {
    if ($this->installScript) 
    {
      if (is_file($this->parent->getPath('extension_administrator').DS.$this->installScript))
      {
        require_once $this->parent->getPath('extension_administrator').DS.$this->installScript;
        if (function_exists('com_install')) 
        {
          if(!com_install()) 
          {   
            $errorMSG = 'Failed to run installer function com_install() for : ' . $this->parent->artifact->name;  
            $this->log->logError($errorMSG);   
            $this->errorMSG = $errorMSG;
            return false;   
          }
        }
      }
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
    if(!$this->parent->copyManifest()) 
    {  
      $this->log->logError('Failed to copy manifest for: ' . $this->parent->artifact->name);
      $this->errorMSG = 'Failed to copy manifest for: ' . $this->parent->artifact->name;
      return false;
    }    
    
    return true;  
  }   
}