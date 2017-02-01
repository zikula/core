Changing the look of bootstrap is quite simple. 

* Please go to the site ``http://getbootstrap.com/customize/`` and configure what you need.
* Download your new customization
* unpack locally your download
* You will find a file named ``boostrap.min.css``
* rename it to your needs (if you want) e.g. ``paula.min.js``
* copy the file into the css folder of your theme
* Now you have to make an adjustment within the file ``theme.yml``. The line ``bootstrapPath: themes/BootstrapTheme/Resources/public/css/cerulean.min.css`` must be adjusted to the path where you have placed your new css file.
* it is wise to store the ``config.json`` which is delivered within the zip file. somewhere in your theme. That makes it much more easy to update later the configuration again.
