<?php
    // filename: send_message.php
    // desc: handles sending a message for a signed-in user to a recipient.

    // Import project files.
    require_once 'classes/Helper.php';
    require_once 'classes/Message.php';
    require_once 'classes/User.php';
    
    // Declare namespace on objects, so it's shorter to refer to them. Ex:
    // can write Helper instead of ChatApp\Helper every time.
    use ChatApp\Helper as Helper;
    use ChatApp\Message as Message;
    use ChatApp\User as User;

    // Only attempt server-side processing if a POST request was received.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            // Sanitize input to prevent malicious form content.
            $content = Helper::sanitizeInput($_POST['message']);
            
            // If the user is on Safari (Mac/iOS), then use recipient-select
            // instead of receipient.
            if (Helper::isSafari($_SERVER['HTTP_USER_AGENT'])) {
                $recipient = Helper::sanitizeInput($_POST['recipient-select']);
            } else {
                $recipient = Helper::sanitizeInput($_POST['recipient']);
            }

            // Get username and token from cookie.
            $cookie = Helper::getUserAndTokenFromCookie();
            $sender = $cookie['username'];
            $token = $cookie['token'];

            // Check token validity.
            $senderUser = User::findByToken($token);
            if (!$senderUser || $sender !== $senderUser->username) {
                throw new Exception('Please verify you are signed in with a '
                    . 'registered account and try again.');
            }
            
            // Check that the recipient exists.
            $recipientUser = User::findByUsername($recipient);
            if (!$recipientUser) {
                throw new Exception('The username specified as the recipient '
                    . 'does not seem to exist. Please make sure the username '
                    . 'is correct.');
            }
            
            // Check that the message is 140 char or less.
            $messageLength = strlen($content);
            if ($messageLength > 140 || $messageLength <= 0) {
                throw new Exception('Message must be more than 0 characters '
                    . 'and no more than 140 characters.');
            }
            
            // Insert a message record into the DB.
            Message::create($sender, $recipient, $content);
            
            // Extend token validity of the active user.
            $senderUser->extendTokenValidity();
            
            // Return a response indicating the message was sent successfully.
            echo Helper::getJsonResponse('success',
                "Message sent successfully to $recipient.");
        } catch (Exception $e) {
            // If there's an exception of any kind, return an error response
            // with the exception message.
            echo Helper::getJsonResponse('fail', 'Error sending message. '
                . $e->getMessage());
        }
    }
?>
