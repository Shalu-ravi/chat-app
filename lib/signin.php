<?php
    // filename: signin.php
    // desc: Script for signing in a user given a valid username/password pair.

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
            // Check if the ip has exceeded the number of failed login attempts.
            if(!User::didExceedRateLimit($_SERVER['REMOTE_ADDR'])) {
                throw new Exception(
                    'Too many login attempts, try again at a later time.');
            }
            
            // Sanitize input to prevent malicious form content.
            $username = Helper::sanitizeInput($_POST['username']);
            $pass = Helper::sanitizeInput($_POST['password']);
    
            // Check if the username follows the app's naming conventions of
            // only lower and uppercase letters or numbers in usernames.
            if (!preg_match("/^[a-zA-Z0-9]*$/", $username)) {
                throw new Exception('Invalid username. Usernames must not have'
                    . 'spaces and can only contain letters and numbers.');
            }
            
            // Check that the user exists.
            $user = User::findByUsername($username);
            if (!$user) {
                throw new Exception('Username or password is incorrect. Please '
                    . 'check and try again.');
            } else {
                // Boolean indicating the password is correct for the username.
                $passVerified = password_verify($pass, $user->hashed_pw);
                
                // Insert login_attempts record.
                User::addLoginAttempt($_SERVER['REMOTE_ADDR'], $username,
                    $passVerified);

                // Check if the password correctly matched the given username.
                if (!$passVerified) {
                    throw new Exception('Username or password is incorrect. '
                        . 'Please check and try again.');
                }
                
                // Generate a token and update the user record with the token.
                $token = $user->generateToken();
                if (!$token) {
                    throw new Exception('Server error post-authentication. '
                        . 'Please contact the system administrator for help.');
                }
                
                // Update the user record for last logged in.
                $user->last_login = Helper::getTimestamp();
                
                // Save modifications to the user object back to the database.
                $user->save();
                
                // If cookie is already set, unset it and set new cookie info.
                $responseData =
                    array('token' => $token, 'username' => $user->username);
                if (isset($_COOKIE['ChatApp'])) {
                    $_COOKIE['ChatApp'] = json_encode($responseData);
                }
                
                // Set HTTP headers to tell the client to set the cookie info.
                setcookie(
                    'ChatApp', // name
                    json_encode($responseData), // value
                    time() + 24 * 60 * 60, // expiration (1 day)
                    '/', // path
                    '', // domain
                    false, // secure only
                    true // http only
                );
                
                // Send a response containing a success message and data.
                echo Helper::getJsonResponse('success',
                    'Successfully logged in.', $responseData);
            }
        } catch (Exception $e) {
            // If there's an exception of any kind, return an error response
            // with the exception message.
            echo Helper::getJsonResponse('fail', 'Error signing in. '
                . $e->getMessage());
        }
    }
?>