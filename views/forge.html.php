<!doctype html>
<html lang="en" class="no-js">
<head>
  <meta charset="utf-8">
  <!--[if IE]><![endif]-->

  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title>Install Stuff</title>
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">
  <link rel="stylesheet" href="http://<?php echo $path;?>/media/css/style.css">  
  <link rel="stylesheet" href="http://<?php echo $path;?>/media/css/jqui/vader.css">
  <script src="http://<?php echo $path;?>/media/js/jquery.min.js"></script>     
  <script src="http://<?php echo $path;?>/media/js/jQote.js"></script>   
  <script src="http://<?php echo $path;?>/media/js/jquery.ui.js"></script>   
  <script src="http://<?php echo $path;?>/media/js/install.js"></script>  
  
  <script type="text/html" id="artifact-template">
  <![CDATA[
    <li class="artifact <%= this.class %>" id="artifact_<%= this.ext_name %>">  
      <a href="#" class="install-artifact install-artifact-icon hideTxt">Install <%= this.name %></a>
      <div class="thumb-wrap"> 
        <ul class="links" style="opacity: 0;">       
          <li class="view-info">    
            <a href="#" class="view-info-link">View Info</a>
          </li>    
          <li class="or">    
            <a href="#" class="or">Or</a>
          </li>    
          <li class="install-artifact">    
            <a href="#" class="install-artifact install-artifact-link">Install</a>
          </li>
        </ul> 
        <div class="thumb-bg"></div>
        <div class="thumb-bg-over"></div>
        <img src="http://localhost:3000/images/uploads/thumb.png" alt="" />
      </div>
      <div class="meta">   
          <div class="title-wrap">
            <h3 class="title"><%= this.name %></h3>
          </div>
      </div>
    </li>
  ]]>
  </script>     
  
  <script type="text/html" id="page-template">
  <![CDATA[
    <div id="page-<%= this.pagenum %>" class="page">  
      <ul class="artifacts"> 
      </ul>
    </div>      
  ]]>
  </script> 
  
  <script type="text/html" id="pagination-template">
  <![CDATA[
    <li class="pagination-wrap-<%= this.pagenum %>">     
      <a href="#" id="pagination_<%= this.pagenum %>"><%= this.pagenum %></a>
    </li>  
  ]]>
  </script>  
  
</head>     
<body class="check">
  <div id="main" class="wrapper">
    <h1 class="pagetitle">Install Stuff</h1>    
    <div id="main-area">
      <div id="main-area-top"></div>
      <div id="main-area-center">
        <div id="main-area-inner">    
          <div id="options">
            <select id="type-selector" name="">
              <option value="all">All</option>
              <option value="template">Templates</option>    
              <option value="component">Components</option>
              <option value="plugin">Plugins</option>
              <option value="module">Modules</option>       
            </select>
          </div>
          <div id="pages">
          </div>
          <div id="pagination-wrap"> 
            <a href="#" id="prev-page" class="grayed">Previous Page</a>    
            <ul id="pagination">
            <?php for($i = 1; $i <= $pageNum; $i++): ?>      
              <li class="pagination-wrap-<?php echo $i;?>">     
                <a href="#" id="pagination_<?php echo $i;?>" <?php if($i == 1): ?> class="active page-nav" <?php endif; ?> ><?php echo $i; ?></a>
              </li>
            <?php endfor; ?>           
            </ul>   
            <?php if($pageNum > 1): ?>
            <a href="#" id="next-page">Next Page</a>   
            <?php else: ?> 
            <a href="#" id="next-page" class="grayed">Next Page</a> 
            <?php endif; ?>
          </div>
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
          <li class="three done"> 
            <div class="icon"></div>
            <div class="label">Settings</div>
          </li>
          <li class="four active">  
            <div class="icon"></div>
            <div class="label">Install</div>
          </li>
      </ul>   
    </div>
  </div>
</body>
</html>