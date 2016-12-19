'use strict';

function zikulaRoutesToday(format)
{
    var timestamp, todayDate, month, day, hours, minutes, seconds;

    timestamp = new Date();
    todayDate = '';
    if (format !== 'time') {
        month = new String((parseInt(timestamp.getMonth()) + 1));
        if (month.length === 1) {
            month = '0' + month;
        }
        day = new String(timestamp.getDate());
        if (day.length === 1) {
            day = '0' + day;
        }
        todayDate += timestamp.getFullYear() + '-' + month + '-' + day;
    }
    if (format === 'datetime') {
        todayDate += ' ';
    }
    if (format != 'date') {
        hours = new String(timestamp.getHours());
        if (hours.length === 1) {
            hours = '0' + hours;
        }
        minutes = new String(timestamp.getMinutes());
        if (minutes.length === 1) {
            minutes = '0' + minutes;
        }
        seconds = new String(timestamp.getSeconds());
        if (seconds.length === 1) {
            seconds = '0' + seconds;
        }
        todayDate += hours + ':' + minutes;// + ':' + seconds;
    }

    return todayDate;
}

// returns YYYY-MM-DD even if date is in DD.MM.YYYY
function zikulaRoutesReadDate(val, includeTime)
{
    // look if we have YYYY-MM-DD
    if (val.substr(4, 1) === '-' && val.substr(7, 1) === '-') {
        return val;
    }

    // look if we have DD.MM.YYYY
    if (val.substr(2, 1) === '.' && val.substr(5, 1) === '.') {
        var newVal = val.substr(6, 4) + '-' + val.substr(3, 2) + '-' + val.substr(0, 2);
        if (true === includeTime) {
            newVal += ' ' + val.substr(11, 5);
        }

        return newVal;
    }
}

function zikulaRoutesValidateNoSpace(val)
{
    var valStr;
    valStr = new String(val);

    return (valStr.indexOf(' ') === -1);
}

/**
 * Runs special validation rules.
 */
function zikulaRoutesPerformCustomValidationRules(objectType, currentEntityId)
{
    jQuery('.validate-nospace').each( function() {
        if (!zikulaRoutesValidateNoSpace(jQuery(this).val())) {
            document.getElementById(jQuery(this).attr('id')).setCustomValidity(/*Zikula.__(*/'This value must not contain spaces.'/*, 'zikularoutesmodule_js')*/);
        } else {
            document.getElementById(jQuery(this).attr('id')).setCustomValidity('');
        }
    });
}
