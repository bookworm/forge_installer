<?php

// Determine Site Path. This is the url path we will use it for adding css/js, links to pages etc.
$path = $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
$path = str_replace('/' . basename(__FILE__), '', $path);   
define('SITE_URL', $path);   

// Load Stuff.          
require_once 'loader.php';   
require_once 'lib/limonade.php';

// Set Error Reporting Level.
error_reporting(E_ALL);     

requireOnceDir('lib/forgeAPI');     
     
dispatch_post('/artifacts/get/:name', 'getArtifact');       
  function getArtifact()
  { 
    $session = file_get_contents(TMP_PATH.DS.'session.txt');   
    $session = unserialize($session);
     
    $forge = Forge::getInstance();
    $fapi  = Forge_API::getInstance($session['forgeConfig']['pubKey'], $session['forgeConfig']['privateKey'], array('decode' => false));  
    
    return $fapi->getArtifact(params('name'));          
  } 
  
dispatch_post('/artifacts/getAllArtifacts', 'getAllArtifacts');       
  function getAllArtifacts()
  {  
    $session = file_get_contents(TMP_PATH.DS.'session.txt');   
    $session = unserialize($session);

    $forge = Forge::getInstance();
    $fapi  = Forge_API::getInstance($session['forgeConfig']['pubKey'], $session['forgeConfig']['privateKey'], array('decode' => false));  
     
    if(!empty($_POST))
      return $fapi->getAllArtifacts($_POST, true);   
    else
      return $fapi->getAllArtifacts(array(), true); 
  }
    
run();