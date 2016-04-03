<?php
    // filename: get_recipients.php
    // desc: Script for returning possible recipients for a signed-in user.

    // Import project files.
    require_once 'classes/Helper.php';
    require_once 'classes/User.php';
    
    // Declare namespace on objects, so it's shorter to refer to them. Ex:
    // can write Helper instead of ChatApp\Helper every time.
    use ChatApp\Helper as Helper;
    use ChatApp\User as User;

    // Only attempt server-side processing if a POST request was received.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            // Get username and token from cookie.
            $cookie = Helper::getUserAndTokenFromCookie();
            $username = $cookie['username'];
            $token = $cookie['token'];

            // Check that the user exists and the token matches the account.
            $user = User::findByToken($token);
            if (!$user) {
                throw new Exception('Session expired or user does not exist. '
                . 'Please re-signin and try again.');
            }
            if ($user->username !== $username) {
                throw new Exception('Session does not match user. Sign-in and '
                    . 'try again.');
            }
            
            // Fetch all users.
            $recipients = User::getAllUsernames();
            if (!$recipients) {
                throw new Exception('Could not retrieve recipients list from '
                    . 'the database.');
            }
            
            // Remove yourself from the list before returning it.
            $recipientsLen = count($recipients);
            for ($i = 0; $i < $recipientsLen; $i++) {
                if ($recipients[$i] === $username) {
                    array_splice($recipients, $i, 1);
                }
            }

            // Return a successful response containing the list of valid
            // recipients.
            echo Helper::getJsonResponse('success', 'Eligible recipients '
                . 'retrieved.', array('recipients' => $recipients));
        } catch (Exception $e) {
            // If there's an exception of any kind, return an error response
            // with the exception message.
            echo Helper::getJsonResponse('fail', 'Error retrieving messages. '
                . $e->getMessage());
        }
    }
?>