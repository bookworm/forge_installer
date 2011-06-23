function checkDB(field, rules, i, options) {
  $('#hostname').validationEngine('showPrompt', 'Checking DB Settings Please Wait', 'load');
  $.post('index.php/check-db', { hostname: $('#hostname').val(), username: $('#dbuser').val(), password: $('#dbpass').val() }, 
    function(data) {
      var json = $.parseJSON(data);    
      //$('#hostname').validationEngine('hidePrompt');  
            
      if(json.failed == true) {
        $('#hostname').validationEngine('showPrompt', json.message, 'red');
      } else {
        $('#hostname').validationEngine('showPrompt', json.message, 'pass');  
      }
    }
  );  
  return;
}  

function checkStuff(field, rules, i, options)
{
  if($('#ftp_enable').attr('checked'))
  {
    $('#ftp_enable').validationEngine('showPrompt', 'Checking FTP Settings Please Wait', 'load'); 
            
    $.post('index.php/check-ftp', 
    { 
      ftpUser: $('#ftp-user').val(), 
      ftpPass: $('#ftp-pass').val(), 
      ftpHost: $('#ftp-hostname').val(), 
      ftpRootpath: $('#ftp-rootpath').val(),  
      ftpPort: $('#ftp-port').val()
    }, 
      function(data) {
        var json = $.parseJSON(data);    

        if(json.failed == true) {
          $('#ftp_enable').validationEngine('showPrompt', json.message, 'red');
        } else {
          $('#ftp_enable').validationEngine('showPrompt', json.message, 'pass');  
        }
      }
    ); 
  }
  
  return;
}

$(function(){
  $("input, textarea, select, button").uniform();   
  
  $('#jsettings').validationEngine({
		ajaxSubmit: false,
		success :  false,
		failure : function() {}
	});
});  