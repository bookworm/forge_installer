<?php  

// no direct access
defined( '_Forge' ) or die( 'Restricted access' );        

// ------------------------------------------------------------------------

/**
 * Very rudimentary step handling     
 *
 * @note Why all these extra functions? 
 * Well, in the future it might be nice to be able to re-start in the middle of a step. 
 * This stuff helps lay the groundwork for some more advanced restarting and stopping of steps.     
 *
 * @package     ForgeInstaller
 * @subpackage  core
 * @version     1.0 Beta
 * @author      Ken Erickson AKA Bookworm http://bookwormproductions.net
 * @copyright   Copyright 2009 - 2011 Design BreakDown, LLC.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3       
 * please visit the DBD club site http://club.designbreakdown.com for support. 
 * Do not e-mail (or god forbid IM or call) me directly.
 */  
class Steps
{           
  public function __construct() {}
  
// ------------------------------------------------------------------------
  
  /**
   * Redirects to a step stored in the session.
   * Allows a user to return to where they left off. 
   *
   * @param string $name Name of the currently active step.
   * @return void
   **/
  public function redirectToStep($name) 
  {
    if(@$_SESSION['step'] AND @!$_SESSION['step'] == '/')
      redirect_to(@$_SESSION['step']);
    else 
      return; 
  }
  
// ------------------------------------------------------------------------

  /**
   * Sets the step session var.
   * 
   * @param string $name Name of the currently active step.
   * @return void
   **/
  public function setStep($name) 
  {  
    @$_SESSION['step'] = $name;  
  }
  
// ------------------------------------------------------------------------

  /**
   * Only called on index.
   * Warning: If this called on any other step it will cause a re-direct loop.
   * 
   * @param string $name Name of the currently active step.
   * @return void
   **/       
  public function step($name) 
  { 
    $this->setStep($name);
    $this->redirectToStep($name);
  } 
}