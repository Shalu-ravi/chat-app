/**
 * chat-app.account.js
 * 
 * Description:
 *   Handles account actions of the user, like signin and signout.
 */

// These global comments appease JSLint and also serves as documentation that a
// global var or function was expected to be provided by another JS file prior
// to this one being run. See http://www.jslint.com/help.html#global for more. 

// $ is function from jQuery used to find and return jQuery objects.
/* global $*/

// Use the existing namespace object if it is available.
var chatApp = chatApp || {};

/**
 * Returns the username from local storage. Username is present if cookie data
 * was received during a successful sign-in.
 */ 
chatApp.getUsername = function() {
    return window.localStorage.username;
};

/**
 * Sets the messages link 'My Messages' to display a users name if signed in,
 * or a default value if not signed in.
 */ 
chatApp.updateUsername = function() {
    var username = chatApp.getUsername();
    if (username) {
        // Remove sign-in link.
        $('#header-signin-link').css('display', 'none');

        // Show sign-out link.
        $('#header-signout-link').css('display', 'inline');
        $('#header-username').text('(' + username + ')');
        $('#header-username').css('display', 'inline');

        // Enable Messages link.
        $('header-home-link').off('click');
    } else {
        // Remove sign-out link.
        $('#header-signout-link').css('display', 'none');
        $('#header-username').css('display', 'none');
        $('#header-username').text('');

        // Disable messages link.
        $('header-home-link').click(
            function(event) {
                event.preventDefault();
                return false;
            }
        );

        // Show sign-in link.
        $('#header-signin-link').css('display', 'inline');
    }
};

/**
 * Set the username in local browser storage.
 * @param username string The username of a verified/signed in user. Use an
 *   empty string to clear the saved username on local storage.
 */
chatApp.setUsername = function(username) {
    // If username is blank, clear the local storage.
    if (username === '') {
        window.localStorage.username = '';
        window.localStorage.clear();
    } else {
        window.localStorage.username = username;
    }
    chatApp.updateUsername();
};


/**
 * Log out a user as a success callback to a POST request to the server.
 * @param formMessagesSel string A CSS selector string that identifies the
 *   container for displaying success/error messages in.
 */
chatApp.signOut = function(formMessagesSel) {
    var formMessages;
    
    // If no selector was provided for the form messages container, then try
    // to guess which page we're on.
    if (typeof formMessagesSel === 'undefined') {
        // Get the container to display success/error messages on the messages
        // page (index.html)
        formMessages = $('#send-form-messages');

        // If the jQuery object doesn't exists, we must be on the sign-in page
        // instead (signin.html)
        if (!formMessages.length) {
            formMessages = $('#form-messages');
        }
    }
    
    // Send the POST request to the form handler via jQuery's ajax method.
    $.ajax({
        type: 'POST',
        url: 'lib/signout.php',
        // Data is empty here, since the username/token is sent from a browser
        // cookie automatically with the HTTP request.
        data: {}
    }).done(function(response) {
        // Update the messages link header.
        chatApp.setUsername('');

        // Redirect the user to signin page.
        window.location.replace('/signin.html');
    }).fail(function(data) {
        // Display an error message from the failed request data.
        if (data.responseText !== '') {
            $(formMessages).text(JSON.stringify(data.responseText));
        } else {
            $(formMessages).text('Oops! An error occured and your action '
            + 'could not be completed.');
        }
    });
};
