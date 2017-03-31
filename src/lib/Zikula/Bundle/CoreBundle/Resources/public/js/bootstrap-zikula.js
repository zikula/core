// Copyright Zikula Foundation, licensed MIT.

( function($) {

    // use bootstrap noConflict. See http://getbootstrap.com/javascript/#js-noconflict
    var bootstrapButton = $.fn.button.noConflict()
    $.fn.bootstrapBtn = bootstrapButton

    /**
     * Confirmation modal
     * 
     * Usage: <a data-toggle="confirmation" data-title="..." data-text="..." href="...">...</a>
     */
    $(document).on('click.bs.modal.data-api', '[data-toggle="confirmation"]', function (e) {
        
        e.preventDefault();
        
        var $this = $(this);
        var title = $this.data('title') || '';
        var text  = $this.data('text') || '';
        
        if ($("#confimationModal").length === 0) {
            var Modal = '<div class="modal fade" id="confimationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title">'+title+'</h4></div><div class="modal-body">' + text + '</div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' + Zikula.__('No') + '</button><button id="confirmationOkButton" type="button" class="btn btn-primary" data-dismiss="modal">' + Zikula.__('Yes') + '</button></div></div></div></div>';
            $(document.body).append(Modal);
            $(document).on('click', '#confirmationOkButton', function (e) {
                window.location = $this.attr('href');
            });
        }
        
        $('#confimationModal').modal({}, this).one('hide', function () {
            $this.is(':visible') && $this.focus();
        });
    });
    
    /**
     * Return a value of input.
     *
     * @param element e The input element
     *
     * @return string Value
     */
    function getValueOfElement(e) {
        if ($(e).is(':checkbox')) {
            return $(e).is(':checked') ? '1' : '0';
        } else if ($(e).is(':radio')) {
            var name = $(e).attr('name');
            return $('input[name="' + name + '"]:checked').val();
        } else {
            return $(e).val();
        }
    }
    
    $(document).ready(function() {
        
        
        // remove class hide because bootstrap is using important, that is not
        // working with jQuery.show();
        // $('.hide').hide().removeClass('hide');
        
        /**
        * Input switch container. 
        * 
        * This code shows/hide an container dependent on a value of an input 
        * element.
        * 
        * Example: 
        * <input type="text" name="abc" value="a">
        * <div data-switch="abc" data-switch-value="a">...</div>
        * 
        * This example shows the div container if the input value is equal "a"
        */
        $('[data-switch]').each(function() {
            var containerElement = $(this);
            var containerValues = containerElement.data('switch-value') || '0';
            containerValues = containerValues.toString();
            containerValues = containerValues.split(',');
            var inputName = containerElement.data('switch');
            var inputElement = $('[name="'+inputName+'"]');

            var inputValue = getValueOfElement(inputElement);
            if ($.inArray(inputValue, containerValues) === -1) {
                containerElement.hide();
            }

            inputElement.change(function() {
                inputValue = getValueOfElement(inputElement); 
                if ($.inArray(inputValue, containerValues) === -1) {
                    containerElement.slideUp();
                } else {
                    containerElement.slideDown();
                }
            });
        });

        $('.tooltips').each(function() {
            var placement = 'top';
            if ($(this).hasClass('tooltips-bottom')) {
                placement = 'bottom';
            }
            $(this).tooltip({placement: placement, animation: false});
        });
    });
})(jQuery);
