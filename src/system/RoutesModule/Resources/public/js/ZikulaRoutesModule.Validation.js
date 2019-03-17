'use strict';

function zikulaRoutesValidateNoSpace(val) {
    var valStr;
    valStr = new String(val);

    return (valStr.indexOf(' ') === -1);
}

/**
 * Runs special validation rules.
 */
function zikulaRoutesExecuteCustomValidationConstraints(objectType, currentEntityId) {
}
