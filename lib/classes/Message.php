<?php
    // Declare project namespace.
    namespace ChatApp;
    
    // Import thirdparty libraries.
    require_once __DIR__.'/../../thirdparty/lib/idiorm.php';
    
    // Declare global namespace on global objects, otherwise will default to
    // using ChatApp\ORM instead of \ORM.
    use \ORM as ORM;

    // Import project files.
    require_once 'DataObject.php';
    require_once 'Helper.php';
    require_once 'User.php';

    /**
     * Class for abstraction properties and interactions for the 'messages'
     * table in the database. Available properties (database columns) for the
     * 'messages' table: id, sender_id, recipient_id, content, created, viewed.
     */
    class Message extends DataObject {
        /**
         * Create a new message object.
         * @param string $senderUsername A sanitized and validated username.
         * @param string $recipientUsername A sanitized and validated username.
         * @param string $messageContent A string of 140 characters or less.
         * @return User
         */
        public static function create($senderUsername, $recipientUsername,
            $messageContent) {
            // Check if the database connection is established already.
            if (!Helper::isDbConnected()) {
                Helper::openDbConnection();
            }
            
            // Get the ids for the sender and recipient.
            $sender = User::findByUsername($senderUsername);
            $recipient = User::findByUsername($recipientUsername);
            
            // Create a new message.
            $newMessage = new Message();
            $newMessage->orm = ORM::forTable('messages')->create();
            $newMessage->sender_id = $sender->id;
            $newMessage->recipient_id = $recipient->id;
            $newMessage->content = $messageContent;
            $newMessage->created = Helper::getTimestamp();

            // Save the new message to the database.
            $newMessage->save();
            return $newMessage;
        }

        /**
         * Find a message given its id.
         * @param integer $id A message id.
         * @return Message
         */
        public static function findById($id) {
            // Check if the database connection is established already.
            if (!Helper::isDbConnected()) {
                Helper::openDbConnection();
            }
            
            // Get the message by its id.
            $result = ORM::forTable('messages')
                        ->where('id', $id)
                        ->findOne();

            if (!$result) {
                return null;
            }
            return Message::createFromOrm($result);
        }
    
        /**
         * Find all messages for a user. Assumes the user has been checked to
         * verify that it exists.
         * @param string $username A username to search for
         *   unread messages where the username is the recipient of the message.
         * @return array() Array of Message objects.
         */
        public static function findAllForUsername($username) {
            // Check if the database connection is established already.
            if (!Helper::isDbConnected()) {
                Helper::openDbConnection();
            }
            
            // Get the id for the recipient.
            $recipient = User::findByUsername($username);
            
            // Get all unread messages for the recipient.
            $resultSet = ORM::forTable('messages')
                        ->where('recipient_id', $recipient->id)
                        ->where('viewed', 0)
                        ->findMany();

            if (count($resultSet) === 0) {
                return null;
            }
            
            // Build an array of Message instances and return them.
            $messageArray = array();
            foreach ($resultSet as $result) {
                array_push($messageArray, Message::createFromOrm($result));
            }
            return $messageArray;
        }
        
        /**
         * Create a Message instance with a provided $orm instance.
         * @param ORM $orm Instance of Idiorm ORM class.
         * @return Message
         */
        private static function createFromOrm($orm) {
            $message = new Message();
            $message->orm = $orm;
            return $message;
        }

        /**
         * Private constructor defined to prevent instantiation of this class
         * directly. Use above static methods 'create' and 'findAllForUsername'
         * to get instances of this class.
         */
        private function __construct() {}
    }
?>
