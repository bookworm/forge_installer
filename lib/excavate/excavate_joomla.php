<?php

// no direct access
defined( '_Forge' ) or die( 'Restricted access' );        

// ------------------------------------------------------------------------

/**
 * Sub Excavation class for Installing the Joomla! core. 
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
class Excavate_Joomla extends Excavate_Core
{       
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
  }  

// ------------------------------------------------------------------------
  
  /**
   * Inserts SQL from a file into the Joomla! DB.
   * 
   * @param string $filename Name of the file to get the SQL from.
   */
  public function _insertSQL($filename)
  {     
    $sqlString = file_get_contents(JPATH_INSTALLATION.DS.'sql'.DS.$filename.'.sql');  
    
    $queries = Forge_Jinstaller_Helper::splitSql($sqlString);     
    
    foreach ($queries as $query)
    {  
      $query = trim($query);
      if($query != '' && $query {0} != '#')
      {  
        $this->db->setQuery($query);
        if(!$this->db->query()) {  
          echo $this->db->getErrorMsg(); 
          $this->log->logError($this->db->getErrorMsg());
          return false;    
        }
      }
    } 
    unset($sqlString);     
    return true;    
  }
  
/*** 
  // ------------------------------------------------------------------------------------------
  ** Begin task Section. Make Sure to keep your tasks in the order they should be executed. 
  // ------------------------------------------------------------------------------------------
**/    
  
// ------------------------------------------------------------------------

  /**
   * Inserts the SQL for Joomla!.
   *
   * @return bool
   */   
  public function task_insertSQL()
  {    
    if(!$this->_insertSQL('joomla')) 
      return false;
        
    return true;
  }   
   
// ------------------------------------------------------------------------

  /**
   * Inserts the Joomla! sample data.
   *
   * @return bool
   */  
  public function task_insertSampleData()
  {
    if(!$this->_insertSQL('sample_data'))
      return false;
      
    return true;                        
  }   
  
// ------------------------------------------------------------------------

  /**
   * Inserts the Joomla! sample data.
   *
   * @return bool
   */  
  public function task_createAdminUser()
  {     
    jimport('joomla.user.helper'); 
    $config = Forge_Configuration::getInstance();  
    
    $errorMSG = 'Failed to create admin user for: ' . $this->parent->artifact->name;
    $host     = $config->config['host'];
    $user     = $config->config['user'];
    $password = $config->config['password'];
    $dbname   = $config->config['db'];
    $dbprefix = $config->config['dbprefix'];                                                                                      
     
    $this->db = JFactory::getDBO();  
    
    $session = file_get_contents(TMP_PATH.DS.'session.txt'); 
    $session = unserialize($session);     
    
    $adminPassword = $session['adminUserDetails']['adminPassword'];
    $adminEmail    = $session['adminUserDetails']['adminEmail']; 
    $adminLogin    = 'admin';
    
    $salt = JUserHelper::genRandomPassword(32);
    $crypt = JUserHelper::getCryptedPassword($adminPassword, $salt);
    $cryptpass = $crypt.':'.$salt;   
    
    $installdate  = date('Y-m-d H:i:s');
    $nullDate     = $this->db->getNullDate();
    $query = "INSERT INTO #__users VALUES (62, 'Administrator', 'admin', ".$this->db->Quote($adminEmail).", ".$this->db->Quote($cryptpass).", 'Super Administrator', 0, 1, 25, '$installdate', '$nullDate', '', '')";
    $this->db->setQuery($query);  
    
    if (!$this->db->query())
    {
      // is there already and existing admin in migrated data
      if ($this->db->getErrorNum() == 1062 )
      {
        $vars['adminLogin'] = JText::_('Admin login in migrated content was kept');
        $vars['adminPassword'] = JText::_('Admin password in migrated content was kept');
        return true;
      }
      else
      {
        $this->log->logError($this->db->getErrorMsg());
        $this->log->logError($errorMSG);
        $this->errorMSG = $errorMSG;
        return false;
      }
    }

    // add the ARO (Access Request Object)
    $query = "INSERT INTO #__core_acl_aro VALUES (10,'users','62',0,'Administrator',0)";
    $this->db->setQuery($query);
    if (!$this->db->query())
    {
      $this->log->logError($this->db->getErrorMsg());
      $this->log->logError($errorMSG);
      $this->errorMSG = $errorMSG;
      return false;
    }

    // add the map between the ARO and the Group
    $query = "INSERT INTO #__core_acl_groups_aro_map VALUES (25,'',10)";
    $this->db->setQuery($query);
    if (!$this->db->query())
    {
      $this->log->logError($this->db->getErrorMsg());
      $this->log->logError($errorMSG);
      $this->errorMSG = $errorMSG;
      return false;
    }
    
    return true;
  } 
}