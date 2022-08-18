'use strict';

function zikulaRoutesValidateNoSpace(val) {
    var valStr;

    valStr = '' + val;

    return -1 === valStr.indexOf(' ');
}

/**
 * Runs special validation rules.
 */
function zikulaRoutesExecuteCustomValidationConstraints(objectType, currentEntityId) {
}
