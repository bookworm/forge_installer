<!doctype html>
<html lang="en" class="no-js">
<head>
  <meta charset="utf-8">
  <!--[if IE]><![endif]-->

  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title>Configuration Check</title>
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">
  <link rel="stylesheet" href="http://<?php echo $path;?>/media/css/style.css">  
</head> 
<body class="check">
  <div id="main" class="wrapper">
    <?php if ($failed == false): ?> 
      <?php echo $lang->_(''); ?>  
    <h1 class="pagetitle"> <?php echo $lang->_('CONFIG_CHECK'); ?> </h1>    
    <div id="main-area">
      <div id="main-area-top"></div>
      <div id="main-area-center">
        <div id="main-area-inner">   
            <div class="sub-title">
              <h2><span><?php echo $lang->_('REQUIRED'); ?></span></h2>
            </div>    
            <div class="clearfix"></div>
            <ul id="required-settings" class="check-list">   
            <?php foreach($options as $option): ?>
              <?php if($option['optional'] == false): ?>
                <?php $state = ($option['state'] == true ? 'true' : 'false'); ?>  
                <li class="<?php echo $state; ?>">  
                  <div class="icon"></div>
                  <div class="label">
                    <?php echo $option['label']; ?>
                  </div>    
                </li>    
              <?php endif; ?>
            <?php endforeach; ?>  
            </ul>
            <div class="clearfix"></div> 
            <div class="sub-title recommended">
              <h2><span><?php echo $lang->_('RECOMMENDED'); ?></span></h2>
            </div>   
            <?php foreach($options as $option): ?>
              <?php if($option['optional'] == true): ?>  
                <?php $state = ($option['state'] == true ? 'true' : 'false'); ?>  
                <li class="<?php echo $state; ?>">
                  <div class="label">
                    <?php echo $option['label']; ?>
                  </div>    
                </li>    
              <?php endif; ?>
            <?php endforeach; ?>
          </ul> 
        </div>
      </div>
      <div id="main-area-bottom"></div>
    </div>        
    <div id="steps-status-wrap">
      <ul id="steps-status">
          <li class="active one"> 
            <div class="icon"></div>
            <div class="label">Config Check</div>
          </li>
          <li class="two">  
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
      <a id="next" href="<?=url_for('settings')?>">
        <span>next</span>
        <div class="icon"></div>
      </a>
    </div>
    <?php endif; ?>
  </div>
</body>
</html>