<?php
    // filename: get_messages.php
    // desc: Script for retrieving messages for a signed-in user.

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
            
            // Fetch all messages for a user.
            $messages = Message::findAllForUsername($user->username);
            if (!$messages) {
                // If no unread messages, then return that the process succeeded
                // but that there were no messages to be read.
                echo Helper::getJsonResponse('success', 'No unread messages.',
                    array('messages' => array()));
            } else {
                // Use an associative array to avoid duplicate db requests.
                $idUserMap = array();
                // Retrieve the sender name and message id for each message and
                // store it into the $messagesInfo array.
                $messagesInfo = array();
                foreach ($messages as $message) {
                    $senderId = $message->sender_id;
                    
                    // If we've already figured out the username for this id,
                    // just set the username from $idUserMap.
                    if (array_key_exists($senderId, $idUserMap)) {
                        $senderUsername = $idUserMap[$senderId];
                    } else {
                        // Fetch the username from the database for the user id.
                        $sender = User::findById($senderId);
                        $senderUsername = $sender->username;
                        // Store the username by the user id in case we have
                        // more messages from the same user in $messages.
                        $userIdMap[$senderId] = $senderUsername;
                    }
                    
                    // Put the sender name and message id in the message array.
                    array_push($messagesInfo, array(
                        'sender' => $senderUsername,
                        'messageId' => $message->id
                    ));
                }
                // Return a response that contains all senders and message ids
                // of unread messages for the given user.
                echo Helper::getJsonResponse('success', 'Messages retrieved.',
                    array('messages' => $messagesInfo));
            }
            // Extend token validity for the active user.
            $user->extendTokenValidity();
        } catch (Exception $e) {
            // If there's an exception of any kind, return an error response
            // with the exception message.
            echo Helper::getJsonResponse('fail', 'Error retrieving messages. '
                . $e->getMessage());
        }
    }
?>