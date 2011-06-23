<?php          

// no direct access
defined( '_Forge' ) or die( 'Restricted access' );        

// ------------------------------------------------------------------------

/**
 * Core excavation class. Extend by all the other excavation classes.
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
class Excavate_Core
{ 
  /**
   * Reference to the Excavation parent.
   *
   * @var obj
   **/          
  var $parent;
  
  /**
   * Whether or not this Excavation has succeeded.
   *
   * @var bool
   **/    
  var $success = true;
  
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
   * Constructor.
   */
  public function __construct()
  { 
    $this->log = KLogger::instance(TMP_PATH.DS.'log', KLogger::INFO);
  }  
  
// ------------------------------------------------------------------------

  /**
   * Called after creation once the parent is accessible.
   */
  public function init() { } 
}