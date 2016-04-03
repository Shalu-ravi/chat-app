<?php
    // Sign-out script for signing out a user.

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

            // Check that the user exists and the token is valid.
            $user = User::findByToken($token);
            if ($user) {
                // Update the user record for token_validity to expire.
                if ($user->username !== $username) {
                    throw new Exception('Username did not match session user,'
                        . ' could not perform operation.');
                }
                // Set token_validity to expire in -1 hour. 60 min * 60 sec/min.
                $user->token_validity = Helper::getTimestamp(-60 * 60);
                $user->save();
            }
            
            // Remove cookies for current session.
            if (isset($_COOKIE['ChatApp'])) {
                unset($_COOKIE['ChatApp']);
            }
            
            // Write a blank cookie that will expire immediately.
            setcookie(
                'ChatApp', // name
                null, // value
                time() - (60 * 60), // expiration, 60 min * 60 sec/min.
                '/', // path
                '', // domain
                false, // secure only
                true // http only
            );

            // Send a response back that the user was successfully logged out.
            echo Helper::getJsonResponse('success',
                'Successfully logged out.');
        } catch (Exception $e) {
            // If there's an exception of any kind, return an error response
            // with the exception message.
            echo Helper::getJsonResponse('fail', 'Error logging out. '
                . $e->getMessage());
        }
    }

?>