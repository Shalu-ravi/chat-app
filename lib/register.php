<?php
    // filename: register.php
    // desc: Registration script for signing up a user.

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
            // Sanitize input to prevent malicious form content.
            $username = Helper::sanitizeInput($_POST['username']);
            $email = Helper::sanitizeInput($_POST['email']);
            $pass = Helper::sanitizeInput($_POST['password']);
            $passConf = Helper::sanitizeInput($_POST['confirm-password']);
            
            // Check if the username follows the app's naming conventions of
            // only lower and uppercase letters or numbers in usernames.
            if (!preg_match("/^[a-zA-Z0-9]*$/", $username)) {
                throw new Exception('Invalid username. Usernames must not have '
                    . 'spaces and can only contain letters and numbers.');
            }
            
            // Check email validity.
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception(
                    'E-mail address was not in the form me@example.com');
            }
    
            // Check that the password and confirmation password matches.
            if ($pass !== $passConf) {
                throw new Exception('Password fields did not match.');
            }
    
            // Check that a username isn't already taken.
            if (User::findByUsername($username)) {
                throw new Exception('Username is already registered, please '
                    . 'choose another username.');
            } else {
                // Username is not already registered, create a user.
                $user = User::create($username,
                    password_hash($pass, PASSWORD_BCRYPT), $email);
                if (!$user) {
                    throw new Exception('Server encountered an error creating '
                        . 'your account. Please contact the administrator(s) '
                        . 'of the server/database for assistance.');
                }
                // Return a successful response that the user was created.
                echo Helper::getJsonResponse('success',
                    'Successfully created account.');
            }        
        } catch (Exception $e) {
            // If there's an exception of any kind, return an error response
            // with the exception message.
            echo Helper::getJsonResponse('fail', 'Error creating an account. '
                . $e->getMessage());
        }
    }
?>
