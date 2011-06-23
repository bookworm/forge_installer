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

dispatch_get('/status', 'getStatus');
  function getStatus()
  {  
    $status = Forge_Dig::getStatus();  
    
    if(!isset($_GET['prevLine'])) 
      $prevLine = -1;    
    else
      $prevLine = (int) $_GET['prevLine'];     
      
    $lines  = file(TMP_PATH.DS.'log'.DS.'log_'.date('Y-m-d').'.txt'); 
    $result = array('status' => $status, 'messages' => array());
    
    foreach ($lines as $k => $line)
    {
      if($k > $prevLine AND !empty($line)) 
      {   
        preg_match_all('/[\{]{1,}[^}]*[}]{1,}/', $line, $matchMessages); 
        preg_match('/[\|]{1,}[^|]*[|]{1,}/', $line, $matchLevels[0]);
        $push = array('line' => $k, 'message' => str_replace('{', '', str_replace('}', '', $matchMessages[0][0])), 'level' => str_replace('|', '', $matchLevels[0][0]));
        $result['messages'][] = $push;
      }
    }   
         
    return json_encode($result);   
  }  
  
dispatch_get('/startdig', 'startDig');
  function startDig()
  {    
    $forge   = Forge::getInstance();
    $session = file_get_contents(TMP_PATH.DS.'session.txt');   
    $session = unserialize($session);
    $fapi    = Forge_API::getInstance($session['forgeConfig']['pubKey'], $session['forgeConfig']['privateKey']);
     
    $artifacts = $fapi->getArtifacts($forge->artifacts);   
            
    $dig = Forge_Dig::getInstance($artifacts);      
    $dig->start();  
  } 

run();