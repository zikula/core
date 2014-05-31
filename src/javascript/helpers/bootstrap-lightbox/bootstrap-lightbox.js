/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
( function($) {

    function updatePictureInLightbox($a, $input, index) {

        if ($input.size() < 2) {
            $('#bootstrap-lightbox .bootstrap-lightbox-backward').hide();
            $('#bootstrap-lightbox .bootstrap-lightbox-forward').hide();
        } else if (index == 0) {
            $('#bootstrap-lightbox .bootstrap-lightbox-backward').hide();
            $('#bootstrap-lightbox .bootstrap-lightbox-forward').show();
        } else if (index == $input.size() - 1) {
            $('#bootstrap-lightbox .bootstrap-lightbox-backward').show();
            $('#bootstrap-lightbox .bootstrap-lightbox-forward').hide();
        } else {
            $('#bootstrap-lightbox .bootstrap-lightbox-backward').show();
            $('#bootstrap-lightbox .bootstrap-lightbox-forward').show();
        }

        var image = new Image();
        image.src = $a.attr('href');
        originalImagelWidth = image.width;
        originalImagelHeight = image.height;


        // position and resize  lightbox
        var $lightboxImage = $('#bootstrap-lightbox img');
        var windowHeight = $( window ).height();
        var windowWidth = $( window ).width();

        $lightboxImage.css({"margin-top": (windowHeight-originalImagelHeight)/2});
        $lightboxImage.css({"margin-left": (windowWidth-originalImagelWidth)/2});
        $lightboxImage.css({"width": originalImagelWidth});

        // add image to light box
        $lightboxImage.attr('src', $a.attr('href'));
        var caption = $a.attr('title');
        if (caption !== "") {
            $('#bootstrap-lightbox .bootstrap-lightbox-caption').text(caption);
        }

        $( ".bootstrap-lightbox-forward").unbind( "click" );
        $('.bootstrap-lightbox-forward').click(function() {
            updatePictureInLightbox($input.eq(index+1), $input, index+1)
        });
        $( ".bootstrap-lightbox-backward").unbind( "click" );
        $('.bootstrap-lightbox-backward').click(function() {
            updatePictureInLightbox($input.eq(index-1), $input, index-1)
        });

    }

    $.fn.extend({
        lightbox: function() {
            var $input = $(this);

            if ($('#bootstrap-lightbox').length == 0) {
                $(document.body).append(
                    '<div class="modal fade" id="bootstrap-lightbox" tabindex="-1" role="dialog" aria-labelledby="bootstrap-lightbox-label" aria-hidden="true">'+
                        '<div style="position:absolute; top:10px; right:20px;">'+
                            '<span class="fa-stack fa-lg" style="font-size: 180%;">'+
                                '<i class="fa fa-square fa-stack-2x" style="color:#696969"></i>'+
                                '<i class="fa fa-times fa-stack-1x fa-inverse pointer" data-dismiss="modal" aria-hidden="true"></i>'+
                            '</span>'+
                        '</div>'+
                        '<img  data-dismiss="modal" />'+

                        '<div class="bootstrap-lightbox-backward pointer">'+
                            '<i class="fa fa-chevron-left"></i>'+
                        '</div>'+

                        '<div class="bootstrap-lightbox-forward pointer">'+
                            '<i class="fa fa-chevron-right"></i>'+
                        '</div>'+

                        '<div class="bootstrap-lightbox-caption-container">'+
                            '<div class="bootstrap-lightbox-caption"></div>'+
                        '</div>'
                );
            }

            $($input).each(function(index) {
                $(this).on("click", function(event){
                    event.preventDefault();
                    updatePictureInLightbox($(this), $input, index);
                    $('#bootstrap-lightbox').modal('show');
                });
            });
        }
    });
})(jQuery);