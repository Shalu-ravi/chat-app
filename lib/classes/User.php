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
     * Class for abstraction properties and interactions for the 'users' table
     * in the database. Available properties (database columns) for the 'users'
     * table: id, username, email, hashed_pw, registered, last_login, token,
     * token_validity.
     */ 
    class User extends DataObject {
        /**
         * Create a new user object (email is optional, requires a password).
         * Uses existing data if the User is already in the database.
         * @param string $username A sanitized and validated username.
         * @param string $hashedPw A password hash, required for a new user.
         * @param string $email A validated email, optional for a new user.
         * @return User
         */
        public static function create($username, $hashedPw, $email = null) {
            // Check if the database connection is established already.
            if (!Helper::isDbConnected()) {
                Helper::openDbConnection();
            }
            
            $newUser = new User();
            $existingUser = User::findByUsername($username);
            // If user exists already, return a User object with that info.
            if ($existingUser) {
                return $existingUser;
            }
            
            // If user doesn't exist, then create a new user.
            $newUser->orm = ORM::forTable('users')->create();
            $newUser->username = $username;
            $newUser->hashed_pw = $hashedPw;
            $newUser->registered = Helper::getTimestamp();

            // Add an email address if it is specified.
            if ($email) {
                $newUser->email = $email;
            }

            // Save the new user to the database.
            $newUser->save();
            return $newUser;
        }
    
        /**
         * Create a User instance with a provided $orm instance.
         * @param ORM $orm Instance of Idiorm ORM class.
         * @return User
         */
        private static function createFromOrm($orm) {
            $user = new User();
            $user->orm = $orm;
            return $user;
        }

        /**
         * Find a user given its id.
         * @param integer $id A user id.
         * @return User
         */
        public static function findById($id) {
            // Check if the database connection is established already.
            if (!Helper::isDbConnected()) {
                Helper::openDbConnection();
            }
            
            // Get the message by its id.
            $result = ORM::forTable('users')
                        ->where('id', $id)
                        ->findOne();

            if (!$result) {
                return null;
            }
            return User::createFromOrm($result);
        }
        
        /**
         * Find a user by a username, and returns an instance of the User object
         * for that user.
         * @return User
         */
        public static function findByUsername($username) {
            // Check if the database connection is established already.
            if (!Helper::isDbConnected()) {
                Helper::openDbConnection();
            }

            // Check if a user with the provided username is in the database.
            $result = ORM::forTable('users')
                        ->where('username', $username)
                        ->findOne();

            if (!$result) {
                return null;
            }
            return User::createFromOrm($result);
        }

        /**
         * Find a user by a token string. Only valid tokens are taken into
         * consideration. A token is valid for 60 minutes after it is created.
         * @param string $token The token to search for
         * @return User
         */
        public static function findByToken($token) {
            // Check if the database connection is established already.
            if (!Helper::isDbConnected()) {
                Helper::openDbConnection();
            }
            
            // Find it in the database and make sure the timestamp is correct.
            $result = ORM::forTable('users')
                            ->where('token', $token)
                            ->where_raw('token_validity > NOW()')
                            ->findOne();

            if (!$result) {
                return null;
            }
            return User::createFromOrm($result);
        }

        /**
         * Checks the login attempts across a single ip address to ensure it is
         * not too high and potentially malicious.
         * @param string $ip The ip address to check for
         * @param int $limitHour The number of logins within an hour
         * @param int $limit10Min The number of logins within 10 minutes.
         * @return User
         */
         public static function didExceedRateLimit($ip, $limitHour = 20,
            $limit10Min = 10) {
            // Check if the database connection is established already.
            if (!Helper::isDbConnected()) {
                Helper::openDbConnection();
            }

            // The number of login attempts for the last hour by this IP.
            $countHour = ORM::forTable('login_attempts')
                            ->where('ip', ip2long($ip))
                            ->where_raw("timestamp > SUBTIME(NOW(),'1:00')")
                            ->count();

            // The number of login attempts for the last 10 minutes by this IP.
            $count10Min =  ORM::forTable('login_attempts')
                            ->where('ip', ip2long($ip))
                            ->where_raw("timestamp > SUBTIME(NOW(),'0:10')")
                            ->count();

            if ($countHour > $limitHour || $count10Min > $limit10Min) {
                return false;
            }
            return true;
        }

        /**
         * Adds a login attempt for a particular username. Assumes the username
         * provided has already been verified to exist.
         * @param string $ip The ip address to add an attempt for
         * @param string $username The username used to login
         * @param boolean $isPassVerified True if password verified for username
         * @return array
         */
         public static function addLoginAttempt($ip, $username, $isPassVerified) {
            // Check if the database connection is established already.
            if (!Helper::isDbConnected()) {
                Helper::openDbConnection();
            }

            // Get $userId from a lookup on the $username
            $user = User::findByUsername($username);

            // Create a new record in the login attempt table.
            $loginAttempt = ORM::forTable('login_attempts')->create();
            $loginAttempt->user_id = $user->id;
            $loginAttempt->ip = ip2long($ip);
            $loginAttempt->success = $isPassVerified;
            $loginAttempt->save();
        }
        
        /**
         * Return all usernames (possible recipients of messages) in
         * alphabetical order.
         * @return array
         */
        public static function getAllUsernames() {
            // Check if the database connection is established already.
            if (!Helper::isDbConnected()) {
                Helper::openDbConnection();
            }
            
            // Find it in the database and make sure the timestamp is correct.
            $result_set = ORM::forTable('users')
                         ->findMany();
            if (!$result_set) {
                return null;
            }
            
            // Extract just the username strings from the result set.
            $usernames = array();
            foreach ($result_set as $result) {
                array_push($usernames, $result->username);
            }
            
            // Sort the usernames
            sort($usernames);
            return $usernames;
        }
        
        
        /**
         * Generates a new SHA1 login token, writes it to the database and
         * returns it.
         * @return string
         */
        public function generateToken() {
            $this->token = sha1($this->username . time()
                . rand(0, 1000000));

            // Set token validity to 60 min from now (60min * 60s/min).
            $this->token_validity = Helper::getTimestamp(60 * 60);

            // Assign the token to the user in the users database.
            if ($this->save()) {
                return $this->token;
            }
            return null;
        }
        
        /**
         * Extends token validity of an existing token from user activity.
         * @param int $extensionInMin Number of minutes to extend validity by
         */
        public function extendTokenValidity($extensionInMin = 60) {
            // Set token validity to 60 min from now (60min * 60s/min).
            $this->token_validity = Helper::getTimestamp($extensionInMin * 60);

            // Assign the token to the user in the users database.
            $this->save();
        }
    
        /**
         * Private constructor defined to prevent instantiation of this class
         * directly. Use above static methods 'create', 'findByUsername', and
         * 'findByToken' to get instances of this class.
         */
         private function __construct() {}
    }
?>
