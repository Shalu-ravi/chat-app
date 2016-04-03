<?php
    // Script for viewing a message for a signed-in user.
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
            $messageId = Helper::sanitizeInput($_POST['message-id']);

            // Get username and token from cookie.
            $cookie = Helper::getUserAndTokenFromCookie();
            $token = $cookie['token'];
            
            // Check that the message exists.
            $message = Message::findById($messageId);
            if (!$message) {
                throw new Exception('Message did not exist.');
            }
            
            // Check if the message has already been viewed beforew.
            if ($message->viewed) {
                throw new Exception('Message has already been viewed.');
            }
            
            // Check to see if the recipient of the message is the user that is
            // requesting to view the message.
            $recipientUser = User::findByToken($token);
            if ($message->recipient_id !== $recipientUser->id) {
                throw new Exception('The user specified as the recipient '
                    . 'does not match the user requesting the message. If you '
                    . 'believe this to be in error, please contact the system '
                    . 'administrator');
            }
            
            // Mark message as viewed, which renders it ineligible for display
            // on subsequent requests.
            $message->viewed = 1;
            $message->viewed_timestamp = Helper::getTimestamp();
            $message->save();
            
            // Extend token validity.
            $recipientUser->extendTokenValidity();
            
            // Return a successful response along with the message content.
            echo Helper::getJsonResponse('success', $message->content);
        } catch (Exception $e) {
            // If there's an exception of any kind, return an error response
            // with the exception message.
            echo Helper::getJsonResponse('fail', 'Error fetching message. '
                . $e->getMessage());
        }
    }
?>
