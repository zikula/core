/**
 * Copyright 2009 Zikula Foundation - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */


    jQuery(document).ready(function() {

        var bpos = "";
        var perc = 0 ;
        var minperc = 0 ;
        $('#users_pass').css( {backgroundPosition: "0 0"} );

        $('#users_pass').keyup(function(){
            $('#result').html('&nbsp;&nbsp;'+passwordStrength($('#users_pass').val(),$('#users_uname').val())) ;
            perc = passwordStrengthPercent($('#users_pass').val(),$('#users_uname').val());

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
