/**
 * quick example written by Jusuff (Pawel Preneta)
 * To demonstrate how Ajax could be used within Zikula
 * December 15, 2012
 * 
 * ...using jQuery ajax in Zikula (starting from 1.3.4 or 1.3.5) is easy and 
 * you don't need any specific code - just follow jQuery instructions.
 * Here you have some simple example - this request is doing permission test.
 * Copy this and run in javascript console (eg firebug console in Firefox).
 * Make sure jQuery is loaded with noconflict.js file (this file contains 
 * important setup which takes care about csrf tokens).
 * One important thing - notice different arguments order for success and 
 * failure responses. This is ugly mess, but this is how jQuery is handling ajax.
 */
var successHandler = function(result, message, request) {
       console.log('SUCCESS RESPONSE', {
           result: result,
           message: message,
           request: request
       })
   },
   errorHandler = function(request, message, detail) {
       console.log('FAILURE RESPONSE', {
           request: request,
           message: message,
           detail: detail
       })
   },
   post = {
       test_component: 'Permissions::',
       test_instance: '::',
       test_level: '0',
       test_user: 'admin'
   };
// this request should be successful
jQuery.ajax('ajax.php?module=Permissions&func=testpermission', {
   data: post
}).done(successHandler).fail(errorHandler);

// this request should fail
jQuery.ajax('ajax.php?module=Permissions&func=testpermissionFAIL', {
   data: post
}).done(successHandler).fail(errorHandler);