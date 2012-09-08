jQuery(document).ready(function() {
    jQuery('.z-formbuttons').children().each(
         function(){
             jQuery(this).attr('data-role','button');
             // remove img
             jQuery(this).children().each(
                function(){
                    jQuery(this).remove();
                }
             );

             
         }
    );

    jQuery('.z-bt-ok').attr('data-icon','check');
    jQuery('.z-bt-cancel').attr('data-icon','delete');
    jQuery('.z-bt-preview').attr('data-icon','eye-open');
    jQuery('.z-bt-new').attr('data-icon','faplus');
    jQuery('.z-bt-save').attr('data-icon','check');
    jQuery('.z-bt-edit').attr('data-icon','pencil');
    jQuery('.z-bt-archive').attr('data-icon','gear');
    jQuery('.z-bt-delete').attr('data-icon','delete');
    jQuery('.z-bt-filter').attr('data-icon','filter');
     
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