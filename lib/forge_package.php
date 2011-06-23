<?php

// no direct access
defined( '_Forge' ) or die( 'Restricted access' );    

// Import Prequisites
jimport('joomla.filesystem.file');   
jimport('joomla.filesystem.folder');
jimport('joomla.application.helper');   
jimport('joomla.filter.filterinput');     
jimport('joomla.filesystem.path');  
jimport('joomla.filesystem.archive');

// ------------------------------------------------------------------------

/**
 * Package Handling class.  
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
class Forge_Package
{
  /**                       
   * Empty Constructor
   *
   */    
  public function __construct() { } 
  
// ------------------------------------------------------------------------
   
  /**                       
   * Retrieves a package and extracts it to the tmp folder.
   *  
   * @param array $artifact The Artifact to get package for. 
   * @return array $package Array containing the directory, filename etc where the package was extracted to.
   */
  public function retrievePackage($artifact) 
  {   
    $log      = KLogger::instance(TMP_PATH.DS.'log', KLogger::INFO);     
    $filename = $artifact->type . '_' . $artifact->ext_name . '_' . $artifact->version . '.zip'; 
    
    if(file_exists(JPATH_SITE.DS.'forge'.DS.'vendor'.DS.'cache'.DS.$filename))    
      $package = Forge_Package::extractPackage(JPATH_SITE.DS.'forge'.DS.'vendor'.DS.'cache'.DS.$filename, $artifact);    
    else  
    {      
      if(Forge_Package::getPackage($artifact) == false) {
        $log->logError('Couldn\'t get the package for: ' . $artifact->ext_name);      
        return false;
      }
      else
        $package = Forge_Package::extractPackage(JPATH_SITE.DS.'forge'.DS.'vendor'.DS.'cache'.DS.$filename, $artifact);       
    }         
    
    return $package; 
  }                  
  
// ------------------------------------------------------------------------

  /**                       
   * Gets a package using the Forge API and downloads it to the vendor cache.
   *  
   * @param array $artifact The Artifact to get package for. 
   * @return mixed False if failed or Filename of package. 
   * @see Forge_Package::downloadPackage()
   */   
  public function getPackage($artifact)
  {    
    $log = KLogger::instance(TMP_PATH.DS.'log', KLogger::INFO);        
    
    $url = $artifact->package_uri;   
    if(empty($url))  
    {
      $log->logError('Couldn\'t get the package url from API: ' . $artifact->ext_name);      
      return false;
    }
        
    return Forge_Package::downloadPackage($url, $artifact); 
  }
  
// ------------------------------------------------------------------------

  /**                       
   * Downloads a package to the vendor cache.
   * 
   * @param string $url URL to download the package from. 
   * @param array $artifact The Artifact to get package for. 
   * @return string Filename of package.
   * @see Forge_Package::getPackage()
   */ 
  public function downloadPackage($url, $artifact)
  { 
    $log = KLogger::instance(TMP_PATH.DS.'log', KLogger::INFO);     
         
    $php_errormsg = 'Error Unknown';
    ini_set('track_errors', true);
    ini_set('user_agent', "Forge DBD Jumpstart");     
    
    $inputHandle = @ fopen($url, "r");
    $error = strstr($php_errormsg,'failed to open stream:');      
    
    if(!$inputHandle) { 
      $log->logError("Couldn't download ". $artifact->ext_name . "from $url");      
      return false;
    }              
     
    $meta_data = stream_get_meta_data($inputHandle);     
    $target = JPATH_SITE.DS.'forge'.DS.'vendor'.DS.'cache'.DS.$artifact->type . '_' . $artifact->ext_name . '_' . $artifact->version . '.zip';    
    
    $contents = null;        
    
    while (!feof($inputHandle)) {
      $contents .= fread($inputHandle, 4096);
    }
    
    JFile::write($target, $contents);
    fclose($inputHandle);      
    unset($contents);            
    
    return $target;                      
  }        
  
// ------------------------------------------------------------------------

  /**                       
   * Extracts package to a temp directory.
   * 
   * @param string $filename Name of package filename + path.
   * @param array $artifact The Artifact to get package for. 
   * @return string Package filename, extractdir and dir.
   * @see Forge_Package::getPackage()
   */ 
  public function extractPackage($filename, $artifact)
  {     
    $log = KLogger::instance(TMP_PATH.DS.'log', KLogger::INFO);
        
    $archivename = $filename;
    $tmpdir      = uniqid('install_');  
    
    $extractdir  = JPath::clean(TMP_PATH.DS.'installation'.DS.$artifact->ext_name);
    $archivename = JPath::clean($archivename);  
       
    $result = JArchive::extract( $archivename, $extractdir);   
    
    if($result == false) {  
      $log->logError("Failed to extract ". $artifact->name . "from $filename");      
      return false;
    }   
    
    $retval['extractdir'] = $extractdir;
    $retval['packagefile'] = $archivename;
    
    $dirList = array_merge(JFolder::files($extractdir, ''), JFolder::folders($extractdir, ''));
    
    if (count($dirList) == 1) 
    {
      if (JFolder::exists($extractdir.DS.$dirList[0]))
        $extractdir = JPath::clean($extractdir.DS.$dirList[0]);
    }  
    
    $retval['dir'] = $extractdir;    
    
    return $retval;
  }  
}