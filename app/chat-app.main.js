/**
 * chat-app.main.js
 * 
 * Description:
 *   Handles dynamic content on index.html, including form handlers for
 *   sending a message and polling for new messages.
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
    // TODO(jmasukawa): ensure that the "recipient" field can only accept one
    // of the names on the datalist.
    
    /**
     * Adds a message to the document within the message container.
     * @param sender string The sender's username.
     * @param messageId string The unique message id of the message.
     */
    var addMessage = function(sender, messageId) {
        // Build the message div element.
        var messageDiv = $('<div></div>').addClass('message');
        // Build the span that will contain the sender's username.
        var fromSpan = $('<span></span>')
            .text('From ' + sender + ':')
            .addClass('prefix');
        // Build the details element that will hold the preview for the message.
        var detailsElement = $('<details></details>').append(
                $('<summary>View message</summary>')
        );
        messageDiv.append(fromSpan);
        messageDiv.append(detailsElement);
        
        // Add data for the message id in the message div element.
        messageDiv.data('messageId', messageId);
        
        // Append the div to the message container.
        $('#msg-container').append(messageDiv);
    };
    
    /**
     * Adds all messages returned from a server response to the page. Response
     * is in the JSend JSON format, which has the following structure:
     * {
     *     'status' : 'success',
     *     'data' : {
     *         'messages': [{
     *             'messageId' : 2;
     *             'sender' : 'senderUsername',
     *         },{
     *             'messageId' : 3;
     *             'sender' : 'senderUsername',
     *         }],
     *     },
     * }
     * @param response obj A JSON object that contains a 'status' and 'data'.
     *   The status can either be 'success' or 'fail'.
     * @param formMessages jQueryObj A jQuery object representing the div where
     *   a form's success or error messages will be displayed.
     */
    var addMessagesSuccessCallback = function(response, formMessages) {
        // If the response was a failure, then write the error messages
        // and return early.
        if (response['status'] === 'fail') {
            // There was a problem retrieving messages, set error class.
            $(formMessages).removeClass('success');
            $(formMessages).addClass('error');
            
            // Show error messages.
            $(formMessages).text(response['data']['message']);
            
            // Stop executing statements in this menu by returning early.
            return;
        }
        
        if (response['status'] === 'success') {
            var messages = response['data']['messages'];
            // If there are no messages to add from the response, just show the
            // success message and return from the method.
            if (messages.length === 0) {
                // Make sure the div has the 'success' CSS class on success.
                $(formMessages).removeClass('error');
                $(formMessages).addClass('success');

                // Show success message.
                $(formMessages).text(response['data']['message']);
                
                // Stop executing statements in this menu by returning early.
                return;
            }
            
            // If there are messages in the response, add them accordingly.
            if (messages.length > 0) {
                // Remove all non-fading messages, since currently fading
                // messages should continue to fade and allow the user to read.
                $.each($('#msg-container details'),
                    function(index, detailElement) {
                        var parentDiv = detailElement.parentElement;
                        if (!$(parentDiv).hasClass('fade')) {
                            $(detailElement).off('click');
                            $(parentDiv).remove();
                        }
                    }
                );
                
                // Add all messages from the response to the message container.
                $.each(messages, function(index, message){
                    addMessage(message['sender'], message['messageId']);
                });
                
                // Set an event on each details element in msg-container to
                // fetch the corresponding message on click.
                $.each($('#msg-container details'),
                    function(index, detailElement) {
                        $(detailElement).click(viewMessageHandler);
                    }
                );
                
                // Clear out prior form success/error messages.
                $(formMessages).text('');
                
                // Make sure the div has the 'success' CSS class on success.
                $(formMessages).removeClass('error');
                $(formMessages).addClass('success');
                
                // Show success message.
                $(formMessages).text(response['data']['message']);
            }
        }
    };
    
    /**
     * Details elements should slowly remove themself after the message has
     * been displayed.
     * @param parentDiv jQueryObj An object represnting the parent div of the
     *   message that needs to be faded.
     * @param fadeTimeInMs number An integer that represents the time for the
     *   message to fade out and be removed from the document.
     */
    var fadeMessage = function(parentDiv, fadeTimeInMs) {
        // We want to remove the div containing the detail clicked.
        var fadeOpacity = 0;
        $(parentDiv).addClass('fade');
        // Use jQuery's fadeTo method to fade the element out, removing it once
        // the fade animation completes.
        $(parentDiv).fadeTo(fadeTimeInMs, fadeOpacity, function() {
            $(parentDiv).remove();
        });
    };
    
    
    /**
     * Populates the valid recipients datalist/select elements from the db.
     * @param formMessagesSel string A CSS selector string that identifies the
     *   form messages div that will display the success/error messages.
     * @param recipientOptionsSel string A CSS selector string that identifies
     *   the parent element to the recipient options elements.
     */
    var setValidRecipients = function(formMessagesSel, recipientOptionsSel) {
        var formMessages = $(formMessagesSel);
        
        // Send the POST request to the form handler via jQuery's ajax method.
        $.ajax({
            type: 'POST',
            url: 'lib/get_recipients.php',
            data: {} // Username and token is sent to the server via cookie.
        }).done(function(response) {
            if (response['status'] === 'success') {
                // Make sure the div has the 'success' CSS class on success.
                $(formMessages).removeClass('error');
                $(formMessages).addClass('success');
                
                // Add the recipients into options objs within datalist/select.
                var optionElements = '';
                $.each(response['data']['recipients'],
                    function(index, username) {
                        optionElements += '<option value="' + username + '">'
                            + username + '</option>\n';
                    }
                );
                // Clear out existing options elements.
                $(recipientOptionsSel).text('');
                // Set all options elements.                
                $(recipientOptionsSel).append(optionElements);
            } else {
                // There was a problem retrieving messages, set error class.
                $(formMessages).removeClass('success');
                $(formMessages).addClass('error');

                // Display an error message from the failed request data.
                $(formMessages).append(response['data']['message']);
            }
        }).fail(function(data) {
            // There was a problem retrieving messages, set error class.
            $(formMessages).removeClass('success');
            $(formMessages).addClass('error');

            // Display an error message from the failed request data.
            $(formMessages).append(data.responseText);
        });
        
    }
    
    /**
     * Fetch a message from the server by message id.
     * @param messageId string The message id of the message to view.
     * @param detailsElement jQueryObj An object that represents the details
     *   HTML element that has been clicked by the user to view.
     */ 
    var viewMessage = function(messageId, detailsElement) {
        var requestData = 'message-id=' + messageId;
        var formMessages = $('#form-messages');
        
        // Send the POST request to the form handler via jQuery's ajax method.
        $.ajax({
            type: 'POST',
            url: 'lib/view_message.php',
            data: requestData
        }).done(function(response) {
            if (response['status'] === 'success') {
                // There was a problem retrieving messages, set error class.
                $(formMessages).removeClass('success');
                $(formMessages).addClass('error');
                
                // Add the message contents into the corresponding details obj.
                $(detailsElement).append(response['data']['message']);
                
                // Once the message is added, fade the message from the DOM.
                var fadeTimeInMs = 10000;
                fadeMessage(detailsElement.parentElement, fadeTimeInMs);
            } else {
                // There was a problem retrieving messages, set error class.
                $(formMessages).removeClass('success');
                $(formMessages).addClass('error');
    
                // Display an error message from the failed request data.
                $(formMessages).append(response['data']['message']);
            }
        }).fail(function(data) {
            // There was a problem retrieving messages, set error class.
            $(formMessages).removeClass('success');
            $(formMessages).addClass('error');

            // Display an error message from the failed request data.
            $(formMessages).append(data.responseText);
        });
    };

    /**
     * Send a request to the database to view a message.
     * @param event obj The event object that was captured when the user clicked
     *   on the details element that the handler was attached to.
     */ 
    var viewMessageHandler = function(event) {
        // The detail element is event.delegateTarget, which received the event.
        var detailsElement = event.delegateTarget;
        // The div containing the detailsElement is the parent element.
        var parentDiv = detailsElement.parentElement;
        // If the message has been viewed and is being faded out, don't let the
        // user close the message by aborting the click action.
        if ($(parentDiv).hasClass('fade')) {
            event.preventDefault;
            return false;
        }
        // Get the data stored on the message div element. This was set from 
        // the addMessage method.
        var messageId = $(parentDiv).data('messageId');

        // Fetch the message from the server and and add it to the details
        // element for viewing.
        viewMessage(messageId, detailsElement);
    };    
    
    // Set up the ajax requests for the send message and check messages form.
    chatApp.setFormHandler('#send-msg-form', '#send-form-messages');
    chatApp.setFormHandler('#check-messages-form', '#check-form-messages',
        addMessagesSuccessCallback);
    
    // Set up the sign out action when the user clicks the sign out link.
    $('#header-signout-link').click(chatApp.signOut);
    
    if (typeof chatApp.getUsername() === 'undefined') {
        // Redirect to signin page if no username is specified.
        window.location.replace('/signin.html');
    }
    
    // Sets the list of possible recipients for the user from the server.
    setValidRecipients('#send-form-messages', '#recipient-select');
    
    // TODO: Every so often, update the recipient list.
    
    // TODO: Automatically check messages on page load and every so often.
    
    // Sets the messages link 'My Messages' to display a username if signed-in.
    chatApp.updateUsername();
});
