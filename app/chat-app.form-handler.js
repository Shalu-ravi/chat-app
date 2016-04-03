/**
 * chat-app.form-handler.js
 * 
 * Description:
 *   Defines a method for setting up a form to send requests async and write
 *   responses to the user in a form messages element. Based on the article from
 *   Matt West: http://blog.teamtreehouse.com/create-ajax-contact-form
 * 
 * Requires:
 *   html-css-sanitizer-minified.js
 * 
 * Provides:
 *   chatApp.setFormHandler(formSelector, formMessagesSelector)
 */

// These global comments appease JSLint and also serves as documentation that a
// global var or function was expected to be provided by another JS file prior
// to this one being run. See http://www.jslint.com/help.html#global for more. 

// $ is function from jQuery used to find and return jQuery objects.
/* global $*/

// html_sanitize is a global function exported from the
// html-css-santizier-minified.js library, used to sanitize malicious HTML/CSS.
/* global html_sanitize*/ 
 
// Use the existing namespace object if it is available.
var chatApp = chatApp || {};

/**
 * Default success callback handler for chatApp.setFormHandler on successful
 * response from a POST request.
 * @param response obj Response object that contains 'status' and 'data'
 *   properties.
 * @param formMessages jQueryObj A jQuery object that represents the messages
 *   container that will display error/success messages from the POST request.
 * @param form jQueryObj A jQuery object that represents the form that made the
 *   POST request.
 */
chatApp.displayFormMessages = function(response, formMessages, form) {
    if (response['status'] === 'success') {
        // Make sure the div has the 'success' CSS class on success.
        $(formMessages).removeClass('error');
        $(formMessages).addClass('success');
        
        // Show the success message in the formMessages div.
        $(formMessages).text(response['data']['message']);
        
        // Clear the form.
        $(form)[0].reset();
    } else {
        // Make sure the div has the 'fail' CSS class on fail.
        $(formMessages).removeClass('success');
        $(formMessages).addClass('error');
        
        // Show the error message in the formMessages div.
        $(formMessages).text(response['data']['message']);
    }
};

/**
 * Sets a form handler to submit a POST request via ajax.
 * @param formSel string CSS selector string to the form element.
 * @param formMsgsSel string CSS selector string to the form messages element,
 *   which will display error/success messages from the POST request.
 * @param responseCallbackFn function Executes if a POST response was received.
 */
chatApp.setFormHandler = function(formSel, formMsgsSel, responseCallbackFn) {
    // Get the form.
    var form = $(formSel);

    // Get the div that will show the form's messages.
    var formMessages = $(formMsgsSel);
    
    // Add an event to form submission to intercept the default submit behavior
    // of the browser and instead send the POST request by JS in the background.
    $(form).submit(function(event) {
        // Prevent the form from being submitted by the browser.
        if (event) {
            event.preventDefault();
        }

        // Serialize the form data into key/value pairs.
        var formData = $(form).serialize();

        // Sanitize the address specified on the 'action' attribute, as someone
        // could have potentially added things with malicious intent.
        var actionUrl = html_sanitize($(form).attr('action'));

        // Send the POST request to the form handler via jQuery's ajax method.
        $.ajax({
            type: 'POST',
            // Use the URL specified on the action attribute of the form.
            url: actionUrl,
            data: formData
        }).done(function(response) {
            // Execute the success callback function if provided.
            if ($.isFunction(responseCallbackFn)) {
                responseCallbackFn(response, formMessages, form);
            } else {
                // Default to displaying the form's success or error messages to
                // the provided container.
                chatApp.displayFormMessages(response, formMessages, form);
            }
        }).fail(function(data) {
            // Make sure the div has the 'error' CSS class on failure.
            $(formMessages).removeClass('success');
            $(formMessages).addClass('error');

            // Display an error message from the failed request data.
            if (data.responseText !== '') {
                $(formMessages).text(JSON.stringify(data.responseText));
            } else {
                $(formMessages).text('Oops! An error occured and your action '
                + 'could not be completed.');
            }
        });
    });
};