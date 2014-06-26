// Copyright Zikula Foundation 2014 - license GNU/LGPLv3 (or at your option, any later version).

( function($) {

    $(document).ready(function() {

        // set username and email input to lower case
        $('.to-lower-case').blur( function() {
            $(this).val($(this).val().toLowerCase())
        });

        $('.z-module-zikulausersmodule input[data-match]').each(function() {

            var $this = $(this);
            var match = $this.data('match');

            // check if fields match
            var e1 = $this[0];
            var e2 = document.getElementById(match.substr(1));
            var checkMatch = function() {
                if (e1.value != e2.value) {
                    e2.setCustomValidity($this.data('match-error-message'));
                } else {
                    e2.setCustomValidity('');
                }
            };
            e1.addEventListener('change', checkMatch, false);
            e2.addEventListener('keyup', checkMatch, false);
        });


        $('.z-module-zikulausersmodule input[data-min]').each(function() {

            var $this = $(this);

            var checkMinLengthValidity = function() {
                var length = $this.val().length;
                if ($this.attr('required') !== "required" && length === 0) {
                    $this[0].setCustomValidity('');
                } else if (length < $this.data('min')) {
                    $this[0].setCustomValidity($this.data('min-error-message'));
                } else {
                    $this[0].setCustomValidity('');
                }
            };
            $this[0].addEventListener('change', checkMinLengthValidity, false);

        });

    });

})(jQuery);