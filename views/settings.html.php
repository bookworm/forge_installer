<!doctype html>
<html lang="en" class="no-js">
<head>
  <meta charset="utf-8">
  <!--[if IE]><![endif]-->

  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title></title>
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;"> 
  <link rel="stylesheet" href="http://<?php echo $path;?>/media/css/forms.css">  
  <link rel="stylesheet" href="http://<?php echo $path;?>/media/css/uniform.css">
  <link rel="stylesheet" href="http://<?php echo $path;?>/media/css/style.css">   
  <script src="http://<?php echo $path;?>/media/js/jquery.min.js"></script>   
  <script src="http://<?php echo $path;?>/media/js/jquery.uniform.min.js"></script>  
  <script src="http://<?php echo $path;?>/media/js/jquery.validEngine.js"></script>   
  <script src="http://<?php echo $path;?>/media/js/settings.js"></script>     
</head> 
<body>   
  <div id="main" class="wrapper"> 
    <h1 class="pagetitle">Now we need your Forge API keys.</h1>    
    <div id="main-area">
      <div id="main-area-top"></div>
      <div id="main-area-center">
        <div id="main-area-inner">  
          <form id="settings" action="<?=url_for('saveSettings')?>" method="post">   
            <div class="field-container">
              <label for="forgeConfig[pubKey]">
                <span>Public Key</span>
            	</label> 
            	<div class="field-wrap"> 
                <input type="text" name="forgeConfig[pubKey]" value="" id="pub-key" class="validate[required]" />   
              </div>
            </div>   
            <div class="field-container">
              <label for="forgeConfig[privateKey]">
                <span>Private Key</span>
            	</label>
              <div class="field-wrap">
                <input type="text" name="forgeConfig[privateKey]" value="" id="priv-key" class="validate[required,funcCall[checkKeys]]]" />
              </div>         
            </div>
            <input type="submit" value="Save">
          </form>
        </div> 
      </div>  
      <div id="main-area-bottom"></div>   
    </div>    
    <div id="steps-status-wrap">
      <ul id="steps-status">
          <li class="done one"> 
            <div class="icon"></div>
            <div class="label">Config Check</div>
          </li>
          <li class="two active">  
            <div class="icon"></div>
            <div class="label">Forge Login</div>
          </li>
          <li class="three"> 
            <div class="icon"></div>
            <div class="label">Settings</div>
          </li>
          <li class="four">  
            <div class="icon"></div>
            <div class="label">Install</div>
          </li>
      </ul>   
    </div>
  </div>   
</body>
</html>