var phpDox = {

    load: function() {
        $.ajax({
           url: "list.xhtml",
           context: $('.list'),
           success: function(data) {
              $('.classlist').append($(data).find('.classlist').children());
              $('.content').animate({height:$('.classlist').height()+100},200);
           }        
        });        
    },
    
    toggleNamespace: function(id) {
    	$('#'+id).toggle('slow');
    	return false;
    },
    
    loadClass: function(classname) {
        $.ajax({
            url: "classes/" + classname + '.xhtml',
            context: $('.main'),
            success: function(data) {
               $('.main').html($(data).find('#class').children());
               var newHeight = $('.main').height();
               if ($('.classlist').height() < newHeight) {
            	   $('.content').animate({height:newHeight+100},200);   
               } else {
            	   $('.content').animate({height:$('.classlist').height()+100},200);
               }
            }     
         });
        return false;
    }

};
