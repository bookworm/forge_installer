<?php    

// Requires
require_once 'jinstaller_helper.php';

// no direct access
defined( '_Forge' ) or die( 'Restricted access' ); 

// Import Prequisites
jimport('joomla.filesystem.file');  
jimport('joomla.filesystem.folder');   
jimport('joomla.application.helper');   
jimport('joomla.filter.filterinput');          
jimport('joomla.error.error');

// ------------------------------------------------------------------------

/**
 * Installer stuff taken from Joomla!. 
 *
 * @note  
 *  Keeping stuff here helps keep the excavation classes clean.     
 *  Decided to extensively modify and simplify the stuff from Joomla!.  
 *  We don't need backwards compatibility, we can assume valid working packages etc.
 *  So thats why we use our own classes instead of the core one.
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
class Forge_Jinstaller
{    
  /**
   * Reference to Joomla! JFactory::getDBO() Object.
   *
   * @var obj
   **/
  var $db;   
  
  /**
   * Holds an instance of the logger class.
   *
   * @var object
   **/
  var $log;
               
  /**
   * Constructor.
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
   * Singleton.
   *
   * @return object An installer object
   */
  public function &getInstance()
  {
    static $instance;

    if (!isset ($instance))
      $instance = new Forge_Jinstaller();
      
    return $instance;
  }   

// ------------------------------------------------------------------------
  
  /**
   * Finds a package file
   * 
   * @param string $path Path To Src folder.  
   * @param bool $returnObject Whether or not to return the XML object. 
   *  Defaults to false, in which case it returns the path to the manifest file.
   * @return bool True on success, False on error
   */
  public function findManifest($path, $returnObject = false)
  {      
    // Get an array of all the xml files from the installation directory
    $xmlfiles = JFolder::files($path, '.xml$', 1, true);
    // If at least one xml file exists
    foreach ($xmlfiles as $file)
    {
      // Is it a valid joomla installation manifest file?        
      $manifest = self::_isManifest($file);
      if (!is_null($manifest)) 
      {    
        if($returnObject)
          return $manifest;    
        else
          return $file;
      }
    } 
  }
 
// ------------------------------------------------------------------------
  
  /**
   * Is the xml file a valid Joomla installation manifest file
   *
   * @param string $file File + path.    
   * @return mixed $xml A JSimpleXML document, or null if the file failed to parse
   */
  public function _isManifest($file)
  {        
    // Initialize variables
    $null = null;
    $xml  =& JFactory::getXMLParser('Simple');

    if (!$xml->loadFile($file)) {
      unset ($xml);
      return $null;
    }

    $root = $xml->document;
    
    // Valid manifest file return the object
    return $xml;
  }       
  
// ------------------------------------------------------------------------

  /**
   * Copies the installation manifest file to the extension folder in the given client
   *
   * @param int  $cid Where to copy the installfile [optional: defaults to 1 (admin)]    
   * @param string $destPath The destination path. 
   * @param string $srcPath The source path.
   * @return  bool True on success, False on error
   */
  public function copyManifest($cid=1, $destPath, $srcPath)
  {
    // Get the client info
    #jimport('joomla.application.helper');
    #$client = JApplicationHelper::getClientInfo($cid);  
    
    $path['src']  = $srcPath;
    $path['dest'] = $destPath.DS.basename($srcPath);  
     
    return $this->copyFiles(array ($path), true);
  }
  
// ------------------------------------------------------------------------ 

  /**
   * Parse through a queries element of the
   * installation manifest file and take appropriate action.
   *
   * @param object  $element  The xml node to process
   * @return mixed  Number of queries processed.
   */
  public function parseQueries($element)
  {
    $db = $this->db;
    
    if (!is_a($element, 'JSimpleXMLElement') || !count($element->children())) {
      // Either the tag does not exist or has no children therefore we return zero files processed.
      return 0;
    }
    
    $queries = $element->children();
    if (count($queries) == 0)
      return false;
     
    foreach ($queries as $query) {
      $db->setQuery($query->data());
    }          
    return (int) count($queries); 
  } 
  
// ------------------------------------------------------------------------

  /**
   * Method to extract the name of a discreet installation sql file from the installation manifest file.
   *
   * @param object $element The xml node to process     
   * @param string $path Path to the installation folder.
   * @return mixed  Number of queries processed or False on error
   */
  public function parseSQLFiles($element, $path)   
  {
    // Initialize variables
    $queries = array();
    $db = & $this->db;
    $dbDriver = strtolower($db->get('name'));
    if ($dbDriver == 'mysqli') {
      $dbDriver = 'mysql';
    }
    $dbCharset = ($db->hasUTF()) ? 'utf8' : ''; 
    
    if (!is_a($element, 'JSimpleXMLElement') || !count($element->children())) {
      // Either the tag does not exist or has no children therefore we return zero files processed.
      return 0;
    }
    
    $files = $element->children();
    if (count($files) == 0) {
      return 0;
    }  
    
    $sqlfile = '';   
    
    foreach ($files as $file)
    {
      $fCharset = (strtolower($file->attributes('charset')) == 'utf8') ? 'utf8' : '';
      $fDriver  = strtolower($file->attributes('driver'));
      if ($fDriver == 'mysqli') {
        $fDriver = 'mysql';
      }          
      
      if( $fCharset == $dbCharset && $fDriver == $dbDriver) 
      {  
        $sqlfile = $file->data();     
        $buffer = file_get_contents($path.DS.$sqlfile);
        
        // Create an array of queries from the sql file
        $queries = Forge_Jinstaller_Helper::splitSql($buffer);
        
        // Process each query in the $queries array (split out of sql file).
        foreach ($queries as $query)
        {
          $query = trim($query);
          if ($query != '' && $query{0} != '#') {
            $db->setQuery($query);
          }
        }
      }      
    }   
    
    return (int) count($queries);
  }
  
// ------------------------------------------------------------------------

  /**
   * Method to parse through a files element of the installation manifest.
   *
   * @param obj $element  The xml node to process
   * @param int $cid     Application ID of application to install to       
   * @param string $destPath The destination path. 
   * @param string $srcPath The source path.
   * @return bool True on success
   */
  public function parseFiles($element, $cid=0, $destPath, $srcPath)
  { 
    jimport('joomla.application.helper');
     
    $copyfiles = array();
    $client    = JApplicationHelper::getClientInfo($cid);        
    
    if (!is_a($element, 'JSimpleXMLElement') || !count($element->children())) {
      // Either the tag does not exist or has no children therefore we return zero files processed.
      return 0;
    }
         
    $files = $element->children();  
    
    if (count($files) == 0)
      return true;

    $destination = $destPath;
    if ($folder = $element->attributes('folder'))
      $source = $srcPath.DS.$folder; 
    else
      $source = $srcPath;
      
    // Process each file in the $files array (children of $tagName).
    foreach ($files as $file)
    {
      $path['src']  = $source.DS.$file->data();
      $path['dest'] = $destination.DS.$file->data();

      // Is this path a file or folder?
      $path['type'] = ( $file->name() == 'folder') ? 'folder' : 'file';

      /*
       * Before we can add a file to the copyfiles array we need to ensure
       * that the folder we are copying our file to exits and if it doesn't,
       * we need to create it.
       */
      if (basename($path['dest']) != $path['dest']) 
      {
        $newdir = dirname($path['dest']);

        if (!JFolder::create($newdir)) {  
          $this->log->logError('Failed to create folder: ' . $newdir);
          return false;
        }
      }
      
      // Add the file to the copyfiles array
      $copyfiles[] = $path;
    }    
    
    return $this->copyFiles($copyfiles); 
  }

// ------------------------------------------------------------------------

  /**
   * Method to parse through a languages element of the installation manifest and take appropriate
   * action.
   *
   * @param object $element The xml node to process
   * @param int   $cid      Application ID of application to install to    
   * @param string $srcPath  The source path.       
   * @return bool True on success
   */
  public function parseLanguages($element, $cid=0, $srcPath)
  {     
    jimport('joomla.application.helper');
    
    $copyfiles = array ();
    $client =& JApplicationHelper::getClientInfo($cid);    
    
    if (!is_a($element, 'JSimpleXMLElement') || !count($element->children())) {
      // Either the tag does not exist or has no children therefore we return zero files processed.
      return 0;
    }
    
    $files = $element->children();
    if (count($files) == 0)
      return true; 
    
    $destination = $client->path.DS.'language'; 
       
    if ($folder = $element->attributes('folder'))
      $source = $srcPath.DS.$folder;
    else
      $source = $srcPath; 
    
    // Process each file in the $files array (children of $tagName).
    foreach ($files as $file)
    {
      if ($file->attributes('tag') != '') 
      {
        $path['src']  = $source.DS.$file->data();
        $path['dest'] = $destination.DS.$file->attributes('tag').DS.basename($file->data());

        // If the language folder is not present, then the core pack hasn't been installed... ignore
        if (!JFolder::exists(dirname($path['dest']))) {
          continue;
        }
      }
      else {
        $path['src']  = $source.DS.$file->data();
        $path['dest'] = $destination.DS.$file->data();
      } 
      
      if (basename($path['dest']) != $path['dest']) {
        $newdir = dirname($path['dest']);

        if (!JFolder::create($newdir)) {
          $this->log->logError('Failed to create folder: ' . $newdir);
          return false;
        }
      }

      // Add the file to the copyfiles array
      $copyfiles[] = $path;
    }  
    
    return $this->copyFiles($copyfiles);    
  }          
  
// ------------------------------------------------------------------------    

  /**
   * Method to parse through a media element of the installation manifest and take appropriate
   * action.
   *
   * @param obj $element  The xml node to process
   * @param int $cid     Application ID of application to install to       
   * @param string $srcPath The source path.
   * @return bool True on success
   */
  public function parseMedia($element, $cid=0, $srcPath)
  {
    $copyfiles = array ();

    jimport('joomla.application.helper');
    $client =& JApplicationHelper::getClientInfo($cid);   
    
    if (!is_a($element, 'JSimpleXMLElement') || !count($element->children())) {
      // Either the tag does not exist or has no children therefore we return zero files processed.
      return 0;
    }
    
    $files = $element->children();
    if (count($files) == 0)
      return true;
    
    // Destination
    $folder = ($element->attributes('destination')) ? DS.$element->attributes('destination') : null;
    $destination = JPath::clean(JPATH_ROOT.DS.'media'.$folder);        
    
    if ($folder = $element->attributes('folder'))
      $source = $srcPath.DS.$folder; 
    else 
      $source = $srcPath;
    
    foreach ($files as $file)
    {
      $path['src']  = $source.DS.$file->data();
      $path['dest'] = $destination.DS.$file->data();

      // Is this path a file or folder?
      $path['type'] = ( $file->name() == 'folder') ? 'folder' : 'file';

      if (basename($path['dest']) != $path['dest']) 
      {
        $newdir = dirname($path['dest']);

        if (!JFolder::create($newdir)) {     
          $this->log->logError('Failed to create directory: ' .$newdir);
          return false;
        }
      }

      // Add the file to the copyfiles array
      $copyfiles[] = $path;
    }

    return $this->copyFiles($copyfiles);     
  }  
  
// ------------------------------------------------------------------------
  
  /**
   * Copy files from source directory to the target directory
   *
   * @param array $files array with filenames
   * @param boolean $overwrite True if existing files can be replaced. Default = true.
   * @return bool True on success
   */
  public function copyFiles($files, $overwrite = false)
  {
    foreach ($files as $file)
    {   
      $filesource = JPath::clean($file['src']);
      $filedest = JPath::clean($file['dest']);
      $filetype = array_key_exists('type', $file) ? $file['type'] : 'file';     
        
      if (!file_exists($filesource)) {
        $this->log->logError('File does not exist: ' . $filesource);
        return false;  
      } 
      else
      { 
        if ($filetype == 'folder') 
        {
          if (!(JFolder::copy($filesource, $filedest, null, $overwrite))) {     
            $this->log->logError('Failed to copy folder to: '. $filesource . $filedest);
            return false;
          }
        } 
        else 
        {
          if (!(JFile::copy($filesource, $filedest))) {     
            $this->log->logError('Failed to copy file to: '. $filesource . $filedest);
            return false;
          }
        }  
      }
    }  
    return count($files);
  }      
  
// ------------------------------------------------------------------------

  /**
   * Method to parse through a files element of the installation manifest and remove
   * the files that were installed
   *
   * @param object $element The xml node to process
   * @param int    $cid   Application ID of application to remove from        
   * @param string $srcPath The source path. Not needed if removing languages of media.
   * @return  bool  True on success
   */
  public function removeFiles($element, $cid=0, $srcPath = null)
  {
    $removefiles = array ();
    $retval = true;

    jimport('joomla.application.helper');
    $client =& JApplicationHelper::getClientInfo($cid);
    
    $files = $element->children();
    if (count($files) == 0) {
      return true;
    }  
    
    switch ($element->name())
    {
      case 'media':
        if ($element->attributes('destination')) {
          $folder = $element->attributes('destination');
        } else {
          $folder = '';
        }
        $source = $client->path.DS.'media'.DS.$folder;
        break;

      case 'languages':
        $source = $client->path.DS.'language';
        break;

      default:
        $source = $srcPath;
        break;
    }    
    
    foreach ($files as $file)
    {   
      
      /*
       * If the file is a language, we must handle it differently.  Language files
       * go in a subdirectory based on the language code, ie.
       *
       *    <language tag="en_US">en_US.mycomponent.ini</language>
       *
       * would go in the en_US subdirectory of the languages directory.
       */
      if ($file->name() == 'language' && $file->attributes('tag') != '') {
        $path = $source.DS.$file->attributes('tag').DS.basename($file->data());

        // If the language folder is not present, then the core pack hasn't been installed... ignore
        if (!JFolder::exists(dirname($path))) {
          continue;
        }
      } else {
        $path = $source.DS.$file->data();
      }       
      
      /*
       * Actually delete the files/folders
       */
      if (is_dir($path)) {
        $val = JFolder::delete($path);
      } else {
        $val = JFile::delete($path);
      }

      if ($val === false) {
        $retval = false;
      }    
    }   
    
    return $retval;
  }
  
// ------------------------------------------------------------------------

  /**
   * Method to parse the parameters of an extension, build the INI
   * string for it's default parameters, and return the INI string.
   *
   * @return  string  INI string of parameter values
   */
  public function getParams($manifest) 
  {
    // Get the manifest document root element
    $root = & $manifest->document;

    // Get the element of the tag names
    $element =& $root->getElementByPath('params');

    // Get the array of parameter nodes to process
    $params = $element->children();
    if (count($params) == 0) {
      return null;
    }

    // Process each parameter in the $params array.
    $ini = null;
    foreach ($params as $param) 
    {
      if (!$name = $param->attributes('name')) {
        continue;
      }

      if (!$value = $param->attributes('default')) {
        continue;
      }

      $ini .= $name."=".$value."\n";
    }  
    
    return $ini;
  }  
}