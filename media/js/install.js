$(document).ready(function() {       
  
  var query = 'fapi.php/artifacts/getAllArtifacts';     
  var params = {};
  
  function updateCurrent()
  {    
    $.post(query, params, 
      function(data) 
      { 
        data = $.parseJSON(data);
        var pageNum;  
        var obj;
             
        if($('.active-page').length > 0)
        {   
          pageNum = $('.active-page').attr('id');
          pageNum = pageNum.replace('page-',''); 
          obj = { pagenum: pageNum };   
          $('.active-page').remove();   
        }
        else {
          pageNum = 1;
          obj = { pagenum: 1 };
        }

        var pageTemplate = $('#page-template').jqote(obj);  
        $('#pages').append(pageTemplate); 
        $('#page-'+pageNum).addClass('active-page');

        var arLen = data.artifacts.length;
        for ( var i=0, len=arLen; i<len; ++i )
        { 
          var artifact = data.artifacts[i];      
          var realCount = i + 1;
          if((realCount % 3) == 0) {
            artifact.class = 'end-row';
          } else {
            artifact.class = '';
          } 
          var artifactTemplate = $('#artifact-template').jqote(artifact);       
          $('.active-page .artifacts').append(artifactTemplate);
        }
        
        var pageCount = data.count / 20;
        if(pageCount < 1) { pageCount = 1 };   
         updatePagination(pageCount);  
        if($('#pagination').children().length < pageCount)
        {
          updatePagination(pageCount);
        }
      }
    );
  } 
  
  function updatePagination(pages)
  {     
    $('#pagination').children().remove(); 
    
    for (var i=0; i<pages; ++i)
    {  
      var obj = { pagenum: i + 1 }; 
      var paginationTemplate = $('#pagination-template').jqote(obj);       
      $('#pagination').append(paginationTemplate);     
    }
  } 
  
  // Initialization call.
  updateCurrent();
  
  $('.page-nav').click(function() 
  {   
    $('.active-page').fadeOut().removeClass('active-page');
    var pageNum = $(this).attr('id');  
    pageNum = pageNum.replace('pagination_','');     
    $('.active-page-nav').removeClass('.active-page-nav');
    $(this).addClass('.active-page-nav');
    if($('#page-'+pageNum).length)
    {   
      $('#page-'+pageNum).fadeIn().addClass('active-page');  
    }
    else
    {
      var limit = 20; 
      var skip = pageNum * limit - 20;   
      params.offset = skip;     
      $.post(query, params, 
        function(data) 
        { 
          data = $.parseJSON(data); 
          var obj = { pagenum: pageNum };    
          var pageTemplate = $('#page-template').jqote(obj);   
          $('#pages').append(pageTemplate); 
          $('#page-'+pageNum).addClass('active-page');

          var arLen = data.artifacts.length;
          for ( var i=0, len=arLen; i<len; ++i )
          { 
            var artifact = data.artifacts[i];  
            var realCount = i + 1;
            if((realCount % 3) == 0) {
              artifact.class = 'end-row';
            } else {
              artifact.class = '';
            }
            var artifactTemplate = $('#artifact-template').jqote(artifact);       
            $('#page-' + pageNum + ' .artifacts').append(artifactTemplate);
          }
        }
      );
    }  
  }); 
  
  $('#type-selector').selectmenu();  
  
  
  $('#type-selector').change(function() {    
    var type = $('#type-selector option:selected').val();     
    if(type !== 'all') {
      params.type = type;
    } 
    updateCurrent();
  }); 
  
  
  $('.thumb-wrap').live({  
    mouseover: function() {   
      $(this).children('.thumb-bg-over').stop().animate({opacity: 1.0} ); 
      $(this).children('.links').stop().animate({opacity: 1.0} )
    },
    mouseout: function() {   
      $(this).children('.thumb-bg-over').stop().animate({opacity: 0.0} );   
      $(this).children('.links').stop().animate({opacity: 0.0 } )
        
    },
       
  });
  
});