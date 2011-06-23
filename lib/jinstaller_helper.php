<?php           

// no direct access
defined( '_Forge' ) or die( 'Restricted access' ); 

// Import Prequisites
jimport('joomla.application.helper');   
jimport('joomla.error.error');

class Forge_Jinstaller_Helper
{ 
  /**
   * Constructor.
   */
  public function __construct()
  {
    $this->db = JFactory::getDBO();    
    
    $config = Forge_Configuration::getInstance();  
    $this->db->select($config->config['db']);
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
      $instance = new Forge_Jinstaller_Helper();   
      
    return $instance;
  } 
  
 
// ------------------------------------------------------------------------

  /**                       
   * Checks if a component is already installed.
   *    
   * @param string $name Name of the component to check.
   * @return bool
   */   
  public function isComponentInstalled($name)
  {
    $query = 'SELECT name' .
        ' FROM #__components' .
        ' WHERE parent = 0' .
        ' ORDER BY iscore, name';
    $this->db->setQuery($query);
    $rows = $this->db->loadObjectList();     
    
    foreach($rows as $row) {
      if($row->name == $name) return true; 
    }   
    
    return false;
  }  
  
// ------------------------------------------------------------------------

  /**                       
   * Checks if a module is already installed.
   *    
   * @param string $name Name of the module to check.
   * @return bool
   */   
  public function isModuleInstalled($name)
  {
    $query = 'SELECT module' .
        ' FROM #__modules' .
        ' ORDER BY module';
    $this->db->setQuery($query);
    $rows = $this->db->loadObjectList();     

    foreach($rows as $row) {
      if(str_replace('mod_', '', $row->module) == $name) return true; 
    }   

    return false;
  } 
  
  
// ------------------------------------------------------------------------

  /**                       
   * Checks if a plugin is already installed.
   *    
   * @param string $name Name of the plugin to check.
   * @return bool
   */   
  public function isPluginInstalled($name)
  {
    $query = 'SELECT name' .
        ' FROM #__plugins' .
        ' ORDER BY name';
    $this->db->setQuery($query);
    $rows = $this->db->loadObjectList();     

    foreach($rows as $row) {
      if($row->name == $name) return true; 
    }   

    return false;
  }  
  
// ------------------------------------------------------------------------

  /**                       
   * Checks if a template is already installed.
   *    
   * @param string $name Name of the template to check.
   * @return bool
   */   
  public function isTemplateInstalled($name)
  {
    $query = 'SELECT template' .
        ' FROM #__templates_menu' .
        ' ORDER BY template';
    $this->db->setQuery($query);
    $rows = $this->db->loadObjectList();     

    foreach($rows as $row) {
      if($row->template == $name) return true; 
    }   

    return false;
  } 
 
// ------------------------------------------------------------------------

  /**                       
   * Splits a string of SQL queries up into their separate queries.
   *    
   * @param string $sql The sql string
   * @return array $queries The SQl queries for your looping pleasure.
   */  
  public function splitSql($sql)
  {
    $sql       = trim($sql);
    $sql       = preg_replace("/\n\#[^\n]*/", '', "\n".$sql);
    $buffer    = array ();
    $ret       = array ();
    $in_string = false;

    for ($i = 0; $i < strlen($sql) - 1; $i ++) 
    {
      if ($sql[$i] == ";" && !$in_string)
      {
        $ret[] = substr($sql, 0, $i);
        $sql = substr($sql, $i +1);
        $i = 0;
      }

      if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\") {
        $in_string = false;
      }
      elseif (!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset ($buffer[0]) || $buffer[0] != "\\")) {
        $in_string = $sql[$i];
      }
      if (isset ($buffer[1])) {
        $buffer[0] = $buffer[1];
      }
      $buffer[1] = $sql[$i];
    }

    if (!empty ($sql))
      $ret[] = $sql;
      
    return ($ret);
  }
}