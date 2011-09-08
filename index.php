<?php

// Determine Site Path. This is the url path we will use it for adding css/js, links to pages etc.
$path = $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
$path = str_replace('/' . basename(__FILE__), '', $path);   
define('SITE_URL', $path);      
define('JVERSION_SHORT', substr(JVERSION, 0, 3));

// Load Stuff.          
require_once 'loader.php';   
require_once 'lib/limonade.php';

// Set Error Reporting Level.
error_reporting(E_ALL);

dispatch('/', 'check');       
  function check()
  {           
    $steps = new Steps();     
    $steps->step('/');  
    $check = new JCheck();  
    return html('check.html.php', null, array(
                  'failed' => $check->failed(),
                  'path' => SITE_URL, 
                  'options' => $check->phpOptions,
                  'lang' => new Forge_Text()     
                ) 
    );
  }          
    
dispatch('/settings', 'settings');       
  function settings()
  {
    $steps = new Steps();     
    $steps->setStep('/settings'); 
    return html('settings.html.php', null, array(
                  'path' => SITE_URL, 
                  'lang' => new Forge_Text()     
                )
    );  
  }  
  dispatch_post('/savesettings', 'saveSettings');       
    function saveSettings()
    { 
      @$_SESSION['forgeConfig'] = $_POST['forgeConfig']; 
      redirect_to('/joomla-settings');
    }
    
  dispatch_post('/check-keys', 'checkKeys'); 
    function checkKeys()
    { 
      $pubKey     = $_POST['pubKey'];  
      $privateKey = $_POST['privateKey'];
      $status = Forge_API::checkKeys($pubKey, $privateKey);  

      $result = array();     


      if ($status == false) {
        $result['failed'] = true; 
        $result['message'] = 'Private Key Invalid';   
      }
      else {    
        $result['failed'] = false;  
        $result['message'] = 'Private Key Valid';   
      } 

      return json_encode($result);   
    }    
  
dispatch('/joomla-settings', 'jsettings');       
  function jsettings()
  {
    $steps = new Steps();     
    $steps->setStep('/joomla-settings'); 
    return html('jsettings.html.php', null, array(
                  'path' => SITE_URL, 
                  'lang' => new Forge_Text()     
                )
    );  
  }  
  dispatch_post('/jsavesettings', 'jsaveSettings');       
    function jsaveSettings()
    {
      // We will assume the configuration is valid by this point.
      // Save Joomla Settings To File    
      $config = new JConfiguration();
      $config->saveConfig($_POST['config']);     
      @$_SESSION['adminUserDetails'] = $_POST['user'];
      redirect_to('/forgery');
    }      
  dispatch_post('/check-db', 'checkDB'); 
    function checkDB()
    {      
      $hostname = $_POST['hostname'];  
      $username = $_POST['username']; 
      $password = $_POST['password'];       
    
      $result = array();     
    
      $link = mysql_connect($hostname, $username, $password); 
      
      if (!$link) {
        $result['failed'] = true; 
        $result['message'] = 'Error: ' . mysql_error();   
      }
      else {    
        $result['failed'] = false;  
        $result['message'] = 'MYSQL Settings Work';   
      }    
      
      mysql_close($link);
      
      return json_encode($result);
    } 
    
  dispatch_post('/check-ftp', 'checkFTP'); 
    function checkFTP()
    {
      $ftpConfig = array();    
      $result    = array();
        
      $ftpConfig['ftpUser']     = $_POST['ftpUser'];  
      $ftpConfig['ftpPass']     = $_POST['ftpPass']; 
      $ftpConfig['ftpHost']     = $_POST['ftpHost'];       
      $ftpConfig['ftpRootpath'] = $_POST['ftpRootpath'];
      $ftpConfig['ftpPort']     = $_POST['ftpPort'];   
      
      $ftpverfiy = JCheck::FTPVerify($ftpConfig['ftpUser'], $ftpConfig['ftpPass'], $ftpConfig['ftpRootpath'], 
                $ftpConfig['ftpHost'], $ftpConfig['ftpPort']);       
                
                
      if(!$ftpverfiy == true)
      {   
        $result['failed'] = true;  
        $result['message'] = $ftpverify; 
      } 
      else  { 
        $result['failed'] = false;  
        $result['message'] = 'FTP Settings Work';
      }
            
      return json_encode($result);  
    } 
       
dispatch('/forgery', 'forgery');       
  function forgery()
  { 
    $sessSerial = serialize($_SESSION);
    file_put_contents(TMP_PATH.DS.'session.txt', $sessSerial);    
    
    $steps = new Steps();     
    $steps->setStep('/forgery');    
    
    $forge = Forge::getInstance();
    
    $fapi = Forge_API::getInstance($_SESSION['forgeConfig']['pubKey'], $_SESSION['forgeConfig']['privateKey']);    
    
    $artifacts = $fapi->getAllArtifacts();
           
    $pageNum = $artifacts->count / 20; 
    if($pageNum < 1) $pageNum = 1;
            
    return html('forge.html.php', null, array(
                  'path' => SITE_URL, 
                  'lang' => new Forge_Text(), 
                  'pageNum' =>   $pageNum
               )
    );
  } 
  
run();