<?php   

// Load Stuff.          
require_once 'loader.php';   
require_once 'lib/limonade.php';   

$session = file_get_contents(TMP_PATH.DS.'session.txt'); 
$session = unserialize($session);    

$forge = new Forge_API_Glue($session['forgeConfig']['pubKey'], $session['forgeConfig']['privateKey']); 

unset($session);

$dig = Forge_Dig::getInstance(); 
$dig->start();