<?php  

// no direct access
defined( '_Forge' ) or die( 'Restricted access' );        

// ------------------------------------------------------------------------

/**
 * Timer Class keeps us from hitting max execution time.
 *
 * Borrowed from the Akeeba Timer Class.  
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
class Forge_Timer
{    
  /**
   * Time we started at.
   *
   * @var int $startTime
   **/
  var $startTime;   
  
  /**
   * The maximum time allow for execution.
   *
   * @var int $startTime
   **/
  var $maxExecTime; 
     
  /**
   * Constructor.
   * 
   * @param string $name Name of the currently active step.
   * @return void 
   * @todo Take bias from configuration file.
   **/
  public function __construct() 
  {
    $this->startTime = $this->microtimeFloat();     
    $bias = 0.75;   

    if(@function_exists('ini_get'))
    {
      $phpMaxExecTime = @ini_get("maximum_execution_time");
      if((!is_numeric($phpMaxExecTime)) || ($phpMaxExecTime == 0)) 
      {
        $phpMaxExecTime = @ini_get("max_execution_time");
        if((!is_numeric($phpMaxExecTime)) || ($phpMaxExecTime == 0)) {
          $phpMaxExecTime = 14;  
        }     
      }
    }
    else {
      $phpMaxExecTime = 14;
    } 
    
    $phpMaxExecTime--;
    $this->maxExecTime = $phpMaxExecTime * $bias;
  }   

// ------------------------------------------------------------------------  
 
  /**                       
   * Singleton function.  
   *
   * @return obj Forge_Timer::
   */
  public function &getInstance()
  {
    static $instance;    
    
    if(!is_object($instance)) 
      $instance = new Forge_Timer();    
      
    return $instance;
  }

// ------------------------------------------------------------------------  

  /**
   * Wake-up function to reset internal timer when we get unserialized.
   * 
   * @return void 
   **/ 
   public function __wakeup()
   {
     $this->startTime = $this->microtimeFloat();
   }
 
// ------------------------------------------------------------------------  
   
  /**
   * Gets the number of seconds left, before we hit the "must break" threshold       
   *
   * @return float
   */ 
  public function getTimeLeft()
  {
    return $this->maxExecTime - $this->getRunningTime();
  } 
   
// ------------------------------------------------------------------------  

  /**
   * Gets the time elapsed since object creation.  
   *
   * @return int
   */
  public function getRunningTime()
  {
    return $this->microtimeFloat() - $this->startTime;
  }

// ------------------------------------------------------------------------  
  
  /**
   * Returns the current timestamp in decimal seconds    
   *
   * @return int
   */
  private function microtimeFloat()
  {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
  }  

// ------------------------------------------------------------------------  
  
  /**
   * Reset the timer. It should only be used in CLI mode!
   */
  public function resetTime()
  {
    $this->startTime = $this->microtimeFloat();
  }
}