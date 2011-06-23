<?php          

// no direct access
defined( '_Forge' ) or die( 'Restricted access' );   

// Includes
include_once 'excavate_core.php';   
include_once 'excavate_component.php';
include_once 'excavate_joomla.php';
include_once 'excavate_language.php';
include_once 'excavate_module.php';
include_once 'excavate_plugin.php'; 
include_once 'excavate_template.php';  
include_once 'helpers.php';

// ------------------------------------------------------------------------

/**
 * Excavation class essentially installs package into Joomla!.   
 *
 * @note Acts as a container around an excavation.
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
class Forge_Excavate
{  
  /**
   * Contains a count of the tasks.
   *
   * @var int $tasksCount
   **/  
  var $tasksCount = 0; 
  
  /**
   * References the Artifact being excavated.
   *
   * @var object $artifact
   **/  
  var $artifact = array();   
 
  /**
   * The Task Currently being executed.
   *
   * @var int
   **/ 
  var $onTask = 0;    
  
  /**
   * The type of artifact being excavated.
   *
   * @var string
   **/
  var $type;
  
  /**
   * The name of sub Excavate Class for the type of artifact we are Excavating.
   *
   * @var string
   **/
  var $className;       
  
  /**
   * Holds the sub Excavate Class for the type of artifact we are Excavating.
   *
   * @var object
   **/
  var $excavateClass;
  
  /**
   * Methods from Excavate Class for the type of artifact we are Excavating.
   *
   * @var array
   **/
  var $classMethods = array();  
  
  /**
   * Array containing the tasks for this Excavation.
   *
   * @var array
   **/
  var $tasks = array();               
  
  /**
   * Reference to the Dig parent.     
   *
   * @note This is deceptive because at the moment there can be only one active Dig at a time.  
   *  $this->dig is always set to Forge_Dig::getInstance();    
   *
   * @var obj
   **/
  var $dig;   
  
  /**
   * Holds the manifest XML file for the Artifact being Excavated.
   *
   * @var string
   **/  
  var $manifest;  
  
  /**
   * Whether or not this Excavation has succeeded.
   *
   * @var bool
   **/    
  var $success = true;       
  
  /**
   * Reference to the package file for the Artifact being Excavated
   *
   * @var mixed
   **/
  var $package; 
  
  /**
   * Whether or not to retrieve the package.
   *
   * @var bool
   **/
  var $shouldRetrievePackage = true;     
  
  /**
   * Holds an instance of the logger class.
   *
   * @var object
   **/
  var $log;      
                
  /**
   * Error Message. Used only if the excavation fails.
   *
   * @var string
   **/
  var $errorMSG;   
  
  /**
   * Holds a  Forge Jinstaller Class.
   *
   * @var object
   **/  
  var $forgeJinstaller;  
  
  /**
   * Path to the manifest file.
   *
   * @var string
   **/
  var $manifestPath;  
  
  /**
   * Count of tasks in the parent Dig.
   *
   * @var int
   **/
  var $digTasks; 
  
     
  /**                       
   * Constructor.
   *
   * @param array $artifact The Artifact to Excavate.
   * @param int $digTasks Number of totalTasks in the Dig object.
   * @param bool $shouldRetrievePackage Whether or not to retrieve the package.  
   */
  public function __construct($artifact, $digTasks, $shouldRetrievePackage = true)
  {   
    $this->log = KLogger::instance(TMP_PATH.DS.'log', KLogger::INFO);
    
    $this->artifact               = $artifact;
    $this->digTasks               = $digTasks;  
    $this->type                   = $artifact->type;        
    $this->className              = 'Excavate' . '_' . ucwords($this->type);  
    $this->excavateClass          = new $this->className;
    $this->excavateClass->parent  = $this;
    $this->classMethods           = get_class_methods($this->excavateClass);    
    $this->shouldRetrievePackage  = $shouldRetrievePackage;
           
    $this->dig = Forge_Dig::getInstance();  
    $this->forgeJinstaller = Forge_Jinstaller::getInstance();
  }    
  
// ------------------------------------------------------------------------
   
  /**                       
   * Generates and returns the number of tasks for the Artifact.
   *
   * @return int $this->tasksCount
   */
  public function getTasks()
  {
    foreach ($this->classMethods as $methodName) 
    {  
      if(strpos($methodName, 'task_') !== false) 
      {       
        $taskName      = str_replace(array('task_', '_before', '_after', '_rollback'), '', $methodName);  
        $this->tasks[$taskName] = $taskName; 
          
        if(strpos_array($methodName, array('_before', '_after', '_rollback')) == false)       
          $this->tasksCount++; 
      }
    }  
    
    $this->dig->updateTotalTasks($this->tasksCount);
    return $this->tasksCount;
  }  
  
// ------------------------------------------------------------------------

  /**                       
   * Does an Excavation. 
   * Gets the package and then executes the tasks.  
   *
   * @return bool
   */   
  public function doExcavation()
  { 
    $this->log->logInfo('Starting Excavation On: '. $this->artifact->name);
     
    if($this->shouldRetrievePackage == true) 
    {    
      if($this->retrievePackage() == false) 
      {  
        $this->success = false;  
        $this->log->logError('Failed to retrieve package for: ' . $this->artifact->name);
        return false;     
      }
    } 
    
    $this->excavateClass->init();  
       
    $this->executeTasks();  
    return $this->success;    
  }  

// ------------------------------------------------------------------------

  /**                       
   * Retrieves a package.
   *
   * @return bool
   */   
  public function retrievePackage()
  {  
    $this->package = Forge_Package::retrievePackage($this->artifact); 
    
    if ($this->package == false)
      return false;
    else
      $this->setPath('source', $this->package['dir']);    
    
    return true;
  }  

// ------------------------------------------------------------------------

  /**                       
   * Executes the tasks one by one.
   *
   * @return void
   */   
  public function executeTasks()
  {     
    foreach($this->tasks as $key => $task) 
    { 
      $timer = Forge_Timer::getInstance();  
      if($timer->getTimeLeft() > 0)
      {
        if(!$this->executeTask($task) == false)
        {
          $this->success = true; 
          unset($this->tasks[$key]); 
          $this->dig->serializeStatus();  
        } 
        else { 
          $this->success = false;
          return false; 
       }  
      } 
      else { $this->dig->pauseDig(); }   
    }             
    
    $this->success = true;  
    return $this->success;
  }
   
// ------------------------------------------------------------------------

  /**                       
   * Executes a specific as well as before and after hooks if they exist.         
   *
   * @note Does NOT execute the task method itself but passes it to Forge_Excavate::executeSpecificTask().
   *  A task in this context is both the task and its before/after tasks.
   *  
   * @param string $taskName Name of the task to execute.
   * @return bool    
   * @see Forge_Excavate::executeSpecificTask()
   */   
  public function executeTask($taskName)
  {
    $type = $this->artifact->type;
    $log = KLogger::instance(TMP_PATH.DS.'log' , KLogger::INFO);
    
    if(arrayFind('task_'.$taskName.'_before', $this->classMethods,  true)) 
    {     
      if(!$this->executeSpecificTask('task_'.$taskName.'_before')) 
        return false;
    }   

    if(!$this->executeSpecificTask('task_'.$taskName)) 
      return false;       

    $this->updateTaskCount();     
      
    if(arrayFind('task_'.$taskName.'_after', $this->classMethods, true)) 
    {  
      if(!$this->executeSpecificTask('task_'.$taskName.'_after')) 
        return false;  
    } 
    
    return true;
  } 

// ------------------------------------------------------------------------

  /**                       
   * Executes a specific task via name.  
   *  
   * @param string $taskName Specific name of the task to execute. MUST include the name of the before or after hook.
   *  i.e exactly match the method name.
   * @return bool    
   */  
  public function executeSpecificTask($taskName)
  { 
    $this->log->logInfo("Executing task $taskName. For: ". $this->artifact->name); 
    $result = call_user_func_array(array($this->excavateClass, $taskName), $this->artifact); 
    
    if(!$result)
    {
      $this->log->logInfo("Failure on $taskName. For: ". $this->artifact->name);
      $this->errorMSG = $this->excavateClass->errorMSG;
      return false;
    }  
    else {
      $this->log->logInfo("Completed $taskName. For: ". $this->artifact->name);
      return true;    
    }  
  }    
  
// ------------------------------------------------------------------------

  /**                       
   * Executes a specific as well as before and after hooks if they exist.
   *  
   * @param string $taskName Name of the task to execute.
   * @return bool
   */     
  public function updateTaskCount()
  {        
    $this->onTask++; 
    $this->dig->incrementOnTaskOfTotal();   
  }       
  
// ------------------------------------------------------------------------
  
  /**
   * Get an Excavation path by name
   *
   * @param string  $name   Path name
   * @param string  $default Default value
   * @return string Path
   */
  public function getPath($name, $default=null)
  {
    return (!empty($this->_paths[$name])) ? $this->_paths[$name] : $default;
  }    
  
// ------------------------------------------------------------------------

  /**
   * Sets an Excavation path by name
   *
   * @param string  $name Path name
   * @param string  $value  Path 
   * @return void
   */
  public function setPath($name, $value)
  {
    $this->_paths[$name] = $value;
  }  

// ------------------------------------------------------------------------

  /**
   * Alias Function to Forge_Jinstaller::copyManifest() 
   *    
   * @return mixed  
   * @see Forge_Jinstaller::copyManifest()
   */     
  public function copyManifest($cid=1)
  {      
    return $this->forgeJinstaller->copyManifest($cid, $this->getPath('extension_root'), $this->getManifestPath()); 
  }     
   
// ------------------------------------------------------------------------

  /**
   * Gets the manifest for the Artifact.
   *    
   * @return mixed  
   * @see Forge_Jinstaller::copyManifest()     
   * @see Forge_Jinstaller::findManifest()   
   */     
  public function getManifest()
  {
    if(!is_object($this->manifest))
      $this->manifest = $this->forgeJinstaller->findManifest($this->getPath('source'), true); 
      
    return $this->manifest; 
  }
  
// ------------------------------------------------------------------------

  /**
   * Returns the manifest file path
   *    
   * @return string 
   * @see Forge_Jinstaller::copyManifest()     
   * @see Forge_Jinstaller::findManifest()   
   */     
  public function getManifestPath()
  {
    if(!is_object($this->manifestPath))
      $this->manifestPath = $this->forgeJinstaller->findManifest($this->getPath('source')); 

    return $this->manifestPath; 
  }   
  
// ------------------------------------------------------------------------

  /**
   * Alias Function to Forge_Jinstaller::parseQueries() 
   *  
   * @param string $elem Part of the XML manifest containing the SQL Queries.  
   * @return mixed  
   * @see Forge_Jinstaller::parseQueries()
   */  
  public function parseQueries($elem)
  {
    return $this->forgeJinstaller->parseQueries($elem);
  }  
  
// ------------------------------------------------------------------------

  /**
   * Alias Function to Forge_Jinstaller::parseSQLFiles() 
   * 
   * @param string $elem Part of the XML manifest containing the SQL Files.  
   * @return mixed  
   * @see Forge_Jinstaller::parseSQLFiles()
   */
  public function parseSQLFiles($elem)
  {
    return $this->forgeJinstaller->parseSQLFiles($elem, $this->getPath('extension_administrator'));
  }
  
// ------------------------------------------------------------------------

  /**
   * Alias Function to Forge_Jinstaller::parseFiles() 
   *
   * @param string $elem Part of the XML manifest containing the files.      
   * @return mixed  
   * @see Forge_Jinstaller::parseFiles()
   */  
  public function parseFiles($elem)
  {
    return $this->forgeJinstaller->parseFiles($elem, $cid=0, $this->getPath('extension_root'), $this->getPath('source'));
  }  
  
// ------------------------------------------------------------------------

  /**
   * Alias Function to Forge_Jinstaller::parseLanguages() 
   *  
   * @param string $elem Part of the XML manifest containing the languages.        
   * @return mixed  
   * @see Forge_Jinstaller::parseLanguages()
   */    
  public function parseLanguages($elem)
  {
    return $this->forgeJinstaller->parseLanguages($elem, $cid=0, $this->getPath('source'));
  }         
  
// ------------------------------------------------------------------------

  /**
   * Alias Function to Forge_Jinstaller::parseMedia() 
   *   
   * @param string $elem Part of the XML manifest containing the media files.     
   * @return mixed  
   * @see Forge_Jinstaller::parseMedia()
   */     
  public function parseMedia($elem) 
  {
    return $this->forgeJinstaller->parseMedia($elem, $cid=0, $this->getPath('source'));
  } 

// ------------------------------------------------------------------------

  /**
   * Alias Function to Forge_Jinstaller::copyFiles() 
   *    
   * @return mixed  
   * @see Forge_Jinstaller::copyFiles()
   */  
  public function copyFiles($files) 
  {
    return $this->forgeJinstaller->copyFiles($files);
  }
  
// ------------------------------------------------------------------------

  /**
   * Alias Function to Forge_Jinstaller::removeFiles() 
   *    
   * @return mixed  
   * @see Forge_Jinstaller::removeFiles()
   */  
  public function removeFiles($elem)
  {
    return $this->forgeJinstaller->removeFiles($elem, $cid=0, $this->getPath('source'));    
  } 
  
// ------------------------------------------------------------------------

  /**
   * Alias Function to Forge_Jinstaller::getParams() 
   *    
   * @return mixed  
   * @see Forge_Jinstaller::getParams()
   */ 
  public function getParams()
  {
    return $this->forgeJinstaller->getParams($this->manifest); 
  }    
}