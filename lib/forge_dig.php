<?php  

// no direct access
defined( '_Forge' ) or die( 'Restricted access' ); 
 
// Import Prequisites
jimport('joomla.filesystem.file'); 
jimport( 'joomla.filesystem.folder' );      

// ------------------------------------------------------------------------

/**
 * The Dig class handles/generates/manages excavations.     
 * You should construct this and then pass in the artifacts.
 *
 * Note: For an explanation of terminology please see the glossary in the docs.
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
class Forge_Dig
{       
  /**
   * Contains The Artifacts. AKA extensions to be installed.
   *
   * @var array $artifacts
   **/
  var $artifacts = array();  
  
  /**
   * The current excavation.
   *
   * @var mixed $onExcavation
   **/
  var $onExcavation = null;       
  
  /**
   * Contains a count of the total tasks for all artifacts. 
   *
   * @var int $totalTasks
   **/
  var $totalTasks = 0;  
  
  /**
   * What task number we are on out of the total tasks from ALL Artifacts.
   *
   * @var int $onTaskOfTotal
   **/ 
  var $onTaskOfTotal = 0;
  
  /**
   * Holds the generated Excavation class objects.
   *
   * @var array $excavations
   **/      
  var $excavations = array();    
  
  /**
   * Holds a reference to the failed Excavations.
   *
   * @var mixed $failedExcavations
   **/
  var $failedExcavations;    
  
  /**
   * Holds an instance of the logger class.
   *
   * @var object
   **/
  var $log;
  
  /**                       
   * The Dig Class Doesn't care about where the artifacts come from.
   *
   * @note You should call Forge_Dig::getInstannce($artifacts); and not instantiate directly.
   *
   * @param array $artifacts An array containing the artifacts in this Dig.
   */   
  public function __construct($artifacts)
  {  
    $this->log = KLogger::instance(TMP_PATH.DS.'log', KLogger::INFO);
    $this->log->logInfo('The dig has begun');      
    $this->artifacts = $artifacts;       
  }
  
// ------------------------------------------------------------------------

  /**                       
   * Starts things.
   *
   * @return void
   */ 
  public function start()
  {
    $this->_jExcavate();     
    $this->startDig();
  }
  
// ------------------------------------------------------------------------
  
  /**                       
   * Singleton function.  
   *
   * @return obj Forge_Dig::
   */
  public function &getInstance($artifacts = null)
  {
    static $instance;     
    
    if(!is_object($instance))
      $instance = new Forge_Dig($artifacts);   
      
    return $instance;
  }

// ------------------------------------------------------------------------
  
  /**                       
   * Begins digging.
   * 
   * @return void
   */
  public function startDig()
  {
    if(file_exists(TMP_PATH . DS . 'dig_restart_needed')) 
    {
      JFile::delete(TMP_PATH . DS . 'dig_restart_needed');           
      
      $unserialized = unserialize(file_get_contents(TMP_PATH . DS . 'dig'));  
      
      $this->onTaskOfTotal = $unserialized['onTaskOfTotal'];   
      $this->onExcavation  = $unserialized['onExcavation'];     
      $this->totalTasks    = $unserialized['totalTasks'];      
      $this->artifacts     = $unserialized['artifacts'];      
      $this->excavations   = $unserialized['excavations'];
      unset($unserialized);  
    }                       
    else
    {    
      foreach($this->artifacts as $key => $artifact) 
      {                 
        if(!file_exists('Excavation' . '_' . $artifact->ext_name . '_completed'))
        { 
          if($artifact->ext_name == 'JCore') $this->excavations[] = new Forge_Excavate($artifact, $this->totalTasks, false);
          else { $this->excavations[] = new Forge_Excavate($artifact, $this->totalTasks); }
        }                
      }            
      foreach($this->excavations as $key => $excavation)
      {   
        $ext_name  = $excavation->artifact->ext_name;   
                
        $artifactTasks                       = $excavation->getTasks();
        @$this->artifacts[$ext_name]->tasks  = $artifactTasks;
        
        $filename        = 'Excavation' . '_' . $ext_name . '_start'; 
        $artifactEncoded = serialize($this->artifacts[$ext_name]);  
        file_put_contents(TMP_PATH . DS . 'excavations'. DS . $filename, $artifactEncoded);   
      }   
    }    
    
    $this->serializeDig();  
    $this->doExcavations();      
    
    if($this->finishDig() == true) 
      die('Finished Dig'); 
    else  
      $this->startDig();
  }  

// ------------------------------------------------------------------------
  
  /**                       
   * Do all the excavations.
   * 
   * @return void          
   */
  public function doExcavations()
  {  
    foreach($this->excavations as $key => $excavation)
    {
      $ext_name  = $excavation->artifact->ext_name;  
      $this->onExcavation = $this->excavations[$key];   
         
      $timer = Forge_Timer::getInstance();
      if($timer->getTimeLeft() > 0)
      { 
        $this->serializeStatus();
        if($excavation->doExcavation() == false) {
          $this->failedExcavation($excavation);   
          return false;
          break;
        } 
        else 
        {   
          // Rename excavation file.     
          $fileold = JFile::makeSafe('Excavation' . '_' . $ext_name . '_start');
          $filenew = JFile::makeSafe('Excavation' . '_' . $ext_name . '_completed');
          $path    = TMP_PATH . DS . 'excavations';                                       
             
          renameFile($fileold, $filenew, $path);    
          $this->log->logInfo('Finished Excavation On: '. $excavation->artifact->name);   
                    
          // Unset array values      
          $this->onExcavation = null;
          unset($this->artifacts[$ext_name]);  
          unset($this->excavations[$key]);        
        }
      }
      else { $this->pauseDig(); }
    } 
    return true;
  }     

// ------------------------------------------------------------------------

  /**                       
   * Serializes the current dig to the dig file.
   * 
   * @return void          
   */ 
  public function serializeDig()
  {
    $serialized = array(); 
    $serialized['onTaskOfTotal']   = $this->onTaskOfTotal;    
    $serialized['totalTasks']      = $this->totalTasks;     
    $serialized['artifacts']       = $this->artifacts;
    $serialized['excavations']     = $this->excavations;  
    
    if(!is_null($this->onExcavation))
      $serialized['onExcavation']    = $this->onExcavation;
      
    $serialized = serialize($serialized);   
    file_put_contents(TMP_PATH . DS . 'dig', $serialized);
  }   
  
// ------------------------------------------------------------------------

  /**                       
   * Serializes the current Dig status.
   * 
   * @return void          
   */   
  public function serializeStatus()
  {  
    if(file_exists(TMP_PATH . DS . 'dig_status')) 
      $serialized = unserialize(file_get_contents(TMP_PATH . DS . 'dig_status'));
    else
      $serialized = array();      
    
    $serialized['onTaskOfTotal'] = $this->onTaskOfTotal;   
    $serialized['totalTasks']    = $this->totalTasks;    
    
    if(!is_null($this->onExcavation))
    {
      $serialized['onArtifact']                = $this->onExcavation->artifact->ext_name;   
      $serialized['currentArtifactTask']       = $this->onExcavation->onTask;    
      $serialized['currentArtifactTotalTasks'] = $this->onExcavation->tasksCount;
    }          
    
    $serialized = serialize($serialized);   
    file_put_contents(TMP_PATH . DS . 'dig_status', $serialized);
  }    
  
// ------------------------------------------------------------------------

  /**                       
   * Gets the status.
   * 
   * @return void 
   * @todo Check for instance and don't unserialize if there is an instance.         
   */  
  public function getStatus()
  {
    return unserialize(file_get_contents(TMP_PATH . DS . 'dig_status')); 
  }

// ------------------------------------------------------------------------
   
  /**                       
   * Pre-pend Joomla! Core Install Excavation
   * 
   * @return void
   */
  public function _jExcavate()
  {    
    $jArtifact = array(   
      'name'     => 'JCore',
      'type'     => 'joomla', 
      'ext_name' => 'JCore', 
    );    
    
    $artifacts = array();          
    
    if(!file_exists(TMP_PATH . DS . 'excavations' . DS . 'Excavation_JCore_completed'))   
    {  
      $artifacts['JCore'] = (object) $jArtifact;
      $this->artifacts = array_merge($artifacts, $this->artifacts);           
      unset($artifacts);
    }     
  }

// ------------------------------------------------------------------------

  /**                       
   * Pause Dig.
   * 
   * @return void   
   * @todo Make this better       
   */      
   public function pauseDig()
   {  
     $this->serializeDig();   
     file_put_contents(TMP_PATH . DS .'dig_restart_needed', "Dig Restart Needed");  
     $this->log->logInfo('Dig Paused');  
     die('Dig Paused');
   }

// ------------------------------------------------------------------------

  /**                       
   * Either finishes the dig and kills our php script or restarts the dig with new artifacts. 
   * 
   * @return bool
   */    
  public function finishDig()
  {  
    jimport( 'joomla.filesystem.folder' );              
    $files = JFolder::files(TMP_PATH . DS . 'excavations', '_start');
       
    if(!empty($files) AND empty($this->artifacts))
    {       
      $this->artifacts = array(); 
      
      foreach($files as $key => $file) {    
        $artifact = unserialize(file_get_contents(TMP_PATH . DS . 'excavations' . $file));       
        $this->artifacts[$artifact->ext_name] = $artifact;         
      }  
         
      return false;
    }  
    elseif(!empty($this->artifacts)) {       
      return false; 
    } 
    else 
    {        
      // Rename dig file.
      $fileold = JFile::makeSafe('dig'); 
      $now     = strftime("%d-%H-%S", time());       
      $filenew = JFile::makeSafe('dig' . '_' . 'completed_'.$now);
      $path    = TMP_PATH; 
         
      renameFile($fileold, $filenew, $path); 
           
      $this->serializeStatus();     
      
      $this->log->logInfo('The dig has finished');          
           
      return true; 
    }
  }   
  
// ------------------------------------------------------------------------

  /**                       
   * Updates the total tasks count.
   * 
   * @param int $totalTasks The new total tasks count.
   * @param bool $resave Whether or not to re-serialize and re-save the dig to a file.
   * @param bool $replace Whether to replce or add to $this->totalTasks
   * @return void          
   */
  public function updateTotalTasks($totalTasks, $resave = true, $replace = false)
  {          
    if($replace == false)
      $this->totalTasks = $this->totalTasks + $totalTasks;
    else
      $this->totalTasks = $totalTasks;       
    
    if($resave == true) {  
      $this->serializeDig();   
      $this->serializeStatus();
    } 
  }  
    
// ------------------------------------------------------------------------

  /**                       
   * Updates the total tasks count.
   * 
   * @param bool $resave Whether or not to re-serialize and re-save the dig to a file.  
   * @return void          
   */ 
  public function incrementOnTaskOfTotal($resave = true, $increment = true)
  {
    if($increment == true) $this->onTaskOfTotal++;
       
    if($resave == true) {  
      $this->serializeDig();   
      $this->serializeStatus();
    }
  }   
  
// ------------------------------------------------------------------------

  /**                       
   * We failed an excavation so log and kill the script.
   * 
   * @return void   
   * @todo Alert the user.       
   */
  public function failedExcavation($excavation)
  {
    // Failed the dig. So log and kill the dig.      
    $this->log->logError("Failed on excavation: ". $excavation->artifact->name);  
    die("Failed on excavation: ". $excavation->artifact->name);
  }      
  
// ------------------------------------------------------------------------

  /**                       
   * Returns the onTaskOfTotal either from the instance or from the serialized dig file.
   * 
   * @return void          
   */  
  public function getOnTaskOfTotal()
  {  
    if(!is_object($this)) {
      return $this->$onTaskOfTotal;
    }
    else {   
      $dig = unserialize(file_get_contents(TMP_PATH . DS . 'dig_status'));   
      return $dig['onTaskOfTotal'];
    }
  }    
 
// ------------------------------------------------------------------------

  /**                       
   * Whether or not the Dig needs to be restarted.
   * 
   * @return bool          
   */
  public function restartNeeded()
  {
    return file_exists(TMP_PATH . DS .'dig_restart_needed');
  } 

// ------------------------------------------------------------------------

  /**                       
   * Appends an artifact to be executed on dig restart/start.
   * 
   * @param object $artifact Artifact.
   * @return bool          
   */
  public function appendArtifact($artifact)
  {
    $filename = 'Excavation' . '_' . $artifact->ext_name . '_start';
    $artifactEncoded = serialize($artifact);  
    file_put_contents(TMP_PATH . DS . 'excavations'. DS . $filename, $artifactEncoded);
  }
}    