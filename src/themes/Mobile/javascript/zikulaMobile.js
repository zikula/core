jQuery(document).ready(function() {
    jQuery('.z-formbuttons').children().each(
         function(){
             jQuery(this).attr('data-role','button');
             
             // add icons
             // TODO: This just works with lang = en
             if (jQuery(this).attr('title') == 'Cancel') {
                  jQuery(this).attr('data-icon','delete');
             } else if (jQuery(this).attr('title') == 'Save') {
                  jQuery(this).attr('data-icon','check');
             } else if (jQuery(this).attr('title') == 'Search now') {
                  jQuery(this).attr('data-icon','search');
             }                                        
             // remove img
             jQuery(this).children().each(
                function(){
                    jQuery(this).remove();
                }
             );
             
         }
    );
     
    jQuery('.z-menulinks').attr('data-role','controlgroup');
    jQuery('.z-menulinks').attr('data-type','horizontal');
      
    jQuery('.z-menulinks').children().each(
        function() {
            jQuery(this).children().each(
                function() {
                    if (jQuery(this).prop('tagName') == 'A') {
                        jQuery(this).attr('data-role', "button");
                    } else {
                        jQuery(this).remove();
                    }
                }
            );
        }
    );
      
    jQuery('a').attr('data-ajax','false');



});