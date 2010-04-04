/**
 * Copyright 2009 Zikula Foundation - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */ 


    jQuery(document).ready(function() {

        var bpos = "";
        var perc = 0 ;
        var minperc = 0 ;
        $('#newpassword').css( {backgroundPosition: "0 0"} );

        $('#newpassword').keyup(function(){
            $('#result').html('&nbsp;&nbsp;'+passwordStrength($('#newpassword').val(),$('#usernamehidden').val())) ;
            perc = passwordStrengthPercent($('#newpassword').val(),$('#usernamehidden').val());

            bpos=" $('#colorbar').css( {backgroundPosition: \"0px -" ;
            bpos = bpos + perc + "px";
            bpos = bpos + "\" } );";
            bpos=bpos +" $('#colorbar').css( {width: \"" ;
            bpos = bpos + (perc * 2) + "px";
            bpos = bpos + "\" } );";
            eval(bpos);
            $('#percent').html(" " + perc  + "% ");
        })
    })
