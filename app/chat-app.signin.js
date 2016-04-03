/**
 * chat-app.signin.js
 * 
 * Description:
 *   Handle dynamic signin/signup form and corresponding form handlers.
 * 
 * Requires:
 *   chat-app.form-handler.js
 *   chat-app.account.js
 */

// These global comments appease JSLint and also serves as documentation that a
// global var or function was expected to be provided by another JS file prior
// to this one being run. See http://www.jslint.com/help.html#global for more. 

// $ is function from jQuery used to find and return jQuery objects.
/* global $*/

// Use the existing namespace object if it is available.
var chatApp = chatApp || {};

// Execute script only after when else in the page has loaded and it's ready.
$(document).ready(function() {
    /**
     * When the user clicks "Sign in", modify the form to remove additional
     * fields from the "Sign up" form. Also, prevent the event from triggering
     * the section anchor link.
     * @param event obj The event object that was captured when the user clicked
     *   on the details element that the handler was attached to.
     */ 
    var signInHandler = function(event) {
        // Clear form text.
        $('#form-messages').text('');

        // Show the account signin form.
        $('#signup-form').hide();
        $('#signin-form').show();
        $('title').text('CS 80 - Chat App - Sign in');

        // Prevents the event from actually navigating through to the link.
        if (event) {
            event.preventDefault();
            return false;
        }
    };


    /**
     * Sign-in a user as a success callback to a post request to the server.
     * @param response obj Response object that contains 'status' and 'data'
     *   properties.
     * @param formMessages jQueryObj A jQuery object that represents the message
     *   container that displays error/success messages from the POST request.
     * @param form jQueryObj A jQuery object that represents the form that made
     *   the POST request.
     */
    var signInSuccessCallback = function(response, formMessages, form) {
        if (response['status'] === 'success') {
            // Use the default success callback.
            chatApp.displayFormMessages(response, formMessages, form);

            // Set the username to display in the page header.
            chatApp.setUsername(response['data']['username']);

            // TODO: Redirect the user to the main page in 2 seconds.
            // TODO: Add a message to formMessages to let the user know they
            // are being redirected.
        }
    };


    /**
     * When the user changes the password or confirm password input box on form
     * signup, check to see if the two password inputs match.
     * @param event obj The event object that was captured when the user clicked
     *   on the details element that the handler was attached to.
     */ 
    var signUpConfirmPassHandler = function(event) {
        // TODO: implement a check to make sure that the confirm password and
        // password fields match.
    };
    

    /**
     * When the user clicks "Sign up", modify the form to include additional
     * fields. Also, prevent the event from triggering the section anchor link.
     * @param event obj The event object that was captured when the user clicked
     *   on the details element that the handler was attached to.
     */ 
    var signUpHandler = function(event) {
        // Clear form text.
        $('#form-messages').text('');

        // Show the account signup form.
        $('#signin-form').hide();
        $('#signup-form').show();
        $('title').text('CS 80 - Chat App - Sign up');

        // Prevents the event from actually navigating through to the link.
        if (event) {
            event.preventDefault();   
        }
        return false;
    };
    
    /**
     * On successful response of a user sign up, prompt the user to sign in.
     * @param response obj Response object that contains 'status' and 'data'
     *   properties.
     * @param formMessages jQueryObj A jQuery object that represents the message
     *   container that displays error/success messages from the POST request.
     * @param form jQueryObj A jQuery object that represents the form that made
     *   the POST request.
     */
    var signUpSuccessCallback = function(response, formMessages, form) {
        if (response['status'] === 'success') {
            // Show the sign-in form.
            signInHandler();

            // Use the default success callback.
            chatApp.displayFormMessages(response, formMessages, form);

            // Append the form message to ask the user to sign in.
            $(formMessages).append(' Please sign in.');
        }
    };

    // Attach handler functions to signin and signup links.
    $('#signin-link').click(signInHandler);
    $('#signup-link').click(signUpHandler);
    
    // TODO: On any changes of the password or confirm password inputs, trigger
    // the function signUpConfirmPassHandler to set a red border style on the
    // two inputs.

    // Set up the ajax requests for the signup and signin forms.
    chatApp.setFormHandler('#signin-form', '#form-messages',
        signInSuccessCallback);
    chatApp.setFormHandler('#signup-form', '#form-messages',
        signUpSuccessCallback);

    // Set up a sign out event on the header link.
    $('#header-signout-link').click(chatApp.signOut);
    
    // Set up a sign in event on the header link.
    $('#header-signin-link').click(signInHandler);
    
    // Sets the messages link 'My Messages' to display a username if signed-in.
    chatApp.updateUsername();
});