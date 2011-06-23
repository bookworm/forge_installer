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
  <script src="http://<?php echo $path;?>/media/js/jsettings.js"></script>     
</head> 
<body>   
  <div class="wrapper" id="main"> 
    <div id="main-area">
      <div id="main-area-top"></div>
      <div id="main-area-center">
        <div id="main-area-inner">  
          <form id="jsettings" action="<?=url_for('jsaveSettings')?>" method="post">     
            
            <fieldset id="db-settings">
              <div class="sub-title db">
                <h2>
                  <span>Database Settings</span>
                </h2> 
              </div> 

              <div class="field-container">    
                <label for="config[host]">
                  <span>Host Name</span>
              	</label>
              	<div class="field-wrap">  
                  <input type="text" name="config[host]" value="localhost" id="hostname" class="validate[required]">
                </dvi>
              </div> 
              <div class="field-container">    
                <label for="config[user]">
                  <span>Username</span>
              	</label>   
              	<div class="field-wrap">  
                  <input type="text" name="config[user]" value="" class="validate[required]" id="dbuser">
                </div>
              </div>
              <div class="field-container">    
                <label for="config[password]">
                  <span>Password</span>
              	</label> 
              	<div class="field-wrap">
                  <input type="text" name="config[password]" value="" class="validate[required]" id="dbpass">
                </div>
              </div>
              <div class="field-container">    
                <label for="config[db]">
                  <span>Database Name</span>
              	</label>
              	<div class="field-wrap"> 
                  <input type="text" name="config[db]" value="" class="validate[required,funcCall[checkDB]]" id="dbname">    
                </div>
              </div>   
              <div class="field-container">    
                <label for="config[dbprefix]">
                  <span>Table Prefix</span>
              	</label>
              	<div class="field-wrap"> 
                  <input type="text" name="config[dbprefix]" value="jos_" class="validate[required,funcCall[checkDB]]"  id="dbprefix"> 
                </div>
              </div>
            </fieldset> 
                  
            <fieldset id="ftp-settings">
              <div class="sub-title db">
                <h2><span>FTP Layer</span></h2>  
              </div>          
              
              <div class="field-container">
              	<label for="config[ftp_enable]">
              		<span>Enable/Disable FTP</span>
              	</label>  
                <input type="checkbox" value="" name="config[ftp_enable]" checked="checked" id="ftp_enable"> 
              </div>  
              <div class="field-container">    
                <label for="config[ftp_user]">
                  <span id="ftp_usermsg">FTP User</span>
              	</label>
              	<div class="field-wrap"> 
                  <input type="text" name="config[ftp_user]" value="" id="ftp-user">
                </div>
              </div>
              <div class="field-container">    
                <label for="config[ftp_pass]">
                  <span id="ftp_passmsg">FTP Password</span>
              	</label> 
              	<div class="field-wrap"> 
                  <input type="text" name="config[ftp_pass]" value="" id="ftp-pass">    
                </div>
              </div>
              <div class="field-container">    
                <label for="config[ftp_root]">
                  <span id="ftp_rootmsg">FTP Root Path</span>
              	</label>
              	<div class="field-wrap"> 
                  <input type="text" name="config[ftp_root]" value="" id="ftp-rootpath">
                </div>
              </div>
              <div class="field-container">    
                <label for="config[ftp_host]">
                  <span id="ftp_hostmsg">FTP Host</span>
              	</label>
              	<div class="field-wrap"> 
                  <input type="text" name="config[ftp_host]" value="127.0.0.1" id="ftp-hostname">
                </div>
              </div>
              <div class="field-container">    
                <label for="config[ftp_port]">
                  <span id="ftp_portmsg">FTP Port</span>
              	</label>
              	<div class="field-wrap"> 
                  <input type="text" name="config[ftp_port]" value="21" id="ftp-port">
                </div>
              </div>   
            </fieldset>      
            
            <fieldset id="basic-settings">    
              <div class="sub-title db">
                <h2><span>Basic Settings</span></h2>  
              </div>  
              
              <div class="field-container">    
                <label for="config[sitename]">
                  <span id="sitenamemsg">Site Name</span>
              	</label> 
              	<div class="field-wrap">
                  <input type="text" name="config[sitename]" value="" class="validate[required,funcCall[checkStuff]]" id="sitename">
                </div>
              </div> 
            </fieldset>   
            
            <fieldset id="admin-user"> 
              <div class="sub-title db">
                <h2><span>Admin User</span></h2>  
              </div>
              
              <div class="field-container">    
                <label for="user[adminEmail]">
                  <span id="adminEmailmsg">Your E-mail</span>
              	</label>
              	<div class="field-wrap"> 
                  <input type="text" name="user[adminEmail]" value="" class="validate[required]" id="email">
                </div>
              </div> 
              <div class="field-container">    
                <label for="user[adminPassword]">
                  <span id="adminPasswordmsg">Admin Password</span>
              	</label>
              	<div class="field-wrap"> 
                  <input type="text" name="user[adminPassword]" value="" class="validate[required]" id="pass">
                </div>
              </div> 
              <div class="field-container">    
                <label for="user[confirmAdminPassword]">
                  <span id="confirmAdminPasswordmsg">Confirm Admin Password</span>
              	</label>  
              	<div class="field-wrap">
                  <input type="text" name="user[confirmAdminPassword]" value="" class="validate[required]" id="pass-confirm">
                </div>
              </div>   
            </fieldset>

            <div class="button-container"><input type="submit" value="Save"></div>      
          </form>
          <div class="clearfix"></div>
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
          <li class="two done">  
            <div class="icon"></div>
            <div class="label">Forge Login</div>
          </li>
          <li class="three active"> 
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
