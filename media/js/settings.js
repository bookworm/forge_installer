function checkKeys(field, rules, i, options) {
  $('#pub-key').validationEngine('showPrompt', 'Checking DB Keys Please Wait', 'load');
  $.post('index.php/check-keys', { pubKey: $('#pub-key').val(), privateKey: $('#priv-key').val() }, 
    function(data) {
      var json = $.parseJSON(data);    
            
      if(json.failed == true) {
        $('#pub-key').validationEngine('showPrompt', json.message, 'red');
      } else {
        $('#pub-key').validationEngine('showPrompt', json.message, 'pass');  
      }
    }
  );  
  return;
}

$(function(){
  $("input, textarea, select, button").uniform();   
  
  $('#settings').validationEngine({
		ajaxSubmit: false,
		success :  false,
		failure : function() {}
	});
});  

