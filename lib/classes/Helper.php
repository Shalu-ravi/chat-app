<?php
    // Declare project namespace.
    namespace ChatApp;
    
    // Import thirdparty libraries.
    require_once __DIR__.'/../../thirdparty/lib/idiorm.php';
    
    // Declare global namespace on global objects, otherwise will default to
    // using ChatApp\ORM instead of \ORM, and ChatApp\Exception instead of
    // \Exception.
    use \ORM as ORM;
    use \Exception as Exception;

    // Class with static helper methods that are used throughout the project.
    class Helper {
        // Boolean for determining if the database has been connected to.
        private static $isDbConnected = false;
        
        /**
         * Returns true if the database is connected, and false otherwise
         * @return boolean
         */
        public static function isDbConnected() {
            return Helper::$isDbConnected;
        }
        
        /**
         * Connects to the database and establishes a connection via Idiorm's
         * ORM object.
         */
        public static function openDbConnection() {
            $host = '127.0.0.1'; // local instance of db, so use localhost ip
            $port = '3306'; // default port is 3306
            $db = 'chatapp'; // database name you want to connect to
            $user = 'jmasukawa'; // mysql username [CHANGE THIS TO YOURS]
            $pass = ''; // No password by default on dev mysql instance
            
            // Define the data source name for connecting to the database.
            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8";
            ORM::configure(array(
                'connection_string' => $dsn,
                'return_result_sets' => true,
                'username' => $user,
                'password' => $pass
            ));
            Helper::$isDbConnected = true;
        }
        
        /**
         * Sanitizes data coming from a user input field by trimming whitespace,
         * removing backslashes and converting special chars to HTML encodings.
         * @param string $data A string from a parameter of a post request.
         * @return string
         */
        public static function sanitizeInput($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        /**
         * Helper function for sending an email.
         * @param string $from An email address of the sender.
         * @param string $to An email address of the recipient.
         * @param string $subject The subject of the email.
         * @param string $message The contents of the email.
         * @return boolean
         */
        public static function send_email($from, $to, $subject, $message) {
            // Add standard headers for an email.
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/plain; charset=utf-8' . "\r\n";
            $headers .= 'From: '.$from . "\r\n";
        
            // mail function returns true if sent successfully, false otherwise.
            return mail($to, $subject, $message, $headers);
        }
        
        /**
         * Creates a timestamp string, offset by the provided number of seconds.
         * @param int $offsetInSeconds An offset from "now".
         * @return string
         */
         public static function getTimestamp($offsetInSeconds = 0) {
            return date('Y-m-d H:i:s', time() + $offsetInSeconds);
        }
        
        /**
         * Convenience method that returns a JSON encoded string response.
         * @param string $status A string for the status, 'fail' or 'success'.
         * @param string $message A string response message to show the user.
         * @param array $data An associative array with data to include.
         * @return string
         */
        public static function getJsonResponse($status, $message = null,
            $data = null) {
            if (!$data) {
                $data = array();
            }
            if ($message) {
                $data['message'] = $message;
            }
            header('Content-Type: application/json');
            
            // Encode the entire reponse into a JSON string.
            return json_encode(
                array('status' => $status, 'data' => $data));
        }
        
        /**
         * Returns an associative array with cookie data. Two properties exist:
         * 'token' and 'username'. Throws an exception if cookie is not found.
         * @return array
         */
        public static function getUserAndTokenFromCookie() {
            $sessionInfo = json_decode($_COOKIE['ChatApp'], true);
            $username = Helper::sanitizeInput($sessionInfo['username']);
            $token = Helper::sanitizeInput($sessionInfo['token']);
            
            // Return an associative array with two keys, 'username' & 'token'.
            return array('username' => $username, 'token' => $token);
        }
        
        /**
         * Returns true if the useragent string provided is Safari.
         * @param string $useragent A device's useragent as a string.
         * @return boolean
         */
        public static function isSafari($useragent) {
            // Chrome and Safari user agent strings both contain 'Safari',
            // because Chrome's webkit version is based on Safari's webkit.
            if (strpos($useragent, 'Safari') !== false) {
                if (strpos($useragent, 'Chrome') === false) {
                    return true;
                }
            }
            return false;
        }
    }
?>
