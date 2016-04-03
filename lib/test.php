<?php
    // DEBUG(jmasukawa): This entire file is just a test file and should NOT be
    // deployed to a production service. This file is, however, useful for
    // testing purposes, such as populating dummy accounts to a database.

    // Import project files.
    require_once 'classes/User.php';
    
    // Declare namespace on objects, so it's shorter to refer to them. Ex:
    // can write User instead of ChatApp\User every time.
    use Chatapp\User as User;

    /**
     * Test function for printing a user's properties to an HTML string. This is
     * incorrect usage of <br> elements, but as a test function, it works fine.
     * @param User $user User object to print.
     */ 
    function debugPrintUser($user) {
        echo '<p>';
        echo 'id:' . $user->id . '<br>';
        echo 'username:' . $user->username . '<br>';
        echo 'email:' . $user->email . '<br>';
        echo 'pw_hash:' . $user->hashed_pw . '<br>';
        echo 'registered:' . $user->registered . '<br>';
        echo 'last login:' . $user->last_login . '<br>';
        echo 'token:' . $user->token . '<br>';
        echo 'token validity:' . $user->token_validity . '<br>';
        echo '</p>';
    }
    
    /**
     * Function that creates test accounts. Will create accounts from 1 - 4:
     * username: TestUser1
     * password: TestUserPass1
     * email: testuser1@example.com
     */ 
    function createTestAccounts($username, $pass, $emailPrefix, $emailSuffix) {
        echo "<h2>Creating test users and adding to the database</h2>";
    
        for ($i = 1; $i < 4; $i++) {
            $testUsername = $username . $i;
            $testPass = $pass . $i;
            $testEmail = $emailPrefix . $i . $emailSuffix;
            echo "Creating user: $testUsername with password: $testPass and "
                . " email: $testEmail...";
            
            $newUser = User::create(
                $testUsername,
                password_hash($testPass, PASSWORD_BCRYPT),
                $testEmail
            );
            debugPrintUser($newUser);
        }
    }

    /**
     * Function that retrieves test accounts. Will retrieve accounts from 1 - 3:
     * username: TestUser1
     * password: TestUserPass1
     * email: testuser1@example.com
     */ 
    function retrieveTestAccounts($username) {
        echo "<h2>Retrieving stored test users from the database</h2>";
        
        for ($i = 1; $i < 4; $i++) {
            $testUsername = $username . $i;
            echo "Searching for existing user $testUsername...";

            $existingUser = User::findByUsername($testUsername);
            debugPrintUser($existingUser);
        }
    }
    
    // Test if a username and password verifies.
    function testPasswordStorage($username, $password) {
        echo "<h2>Getting user: $username and checking validity of password: "
            . "$password from the database</h2>";
        
        $existingUser = User::findByUsername($username);
        $didPasswordVerify = password_verify($password,
            $existingUser->hashed_pw);
            
        if ($didPasswordVerify) {
            echo "<strong>Password verified successfully.</strong>";
        } else {
            echo "<strong>Password did not verify successfully, something is "
                . "wrong with the database or test script.</strong>";
        }
    }
    
    // Define base username, password, and email to be used with test functions.
    $baseUsername = 'TestUser';
    $basePass = 'TestUserPass';
    $baseEmailPrefix = 'testuser';
    $baseEmailSuffix = '@example.com';
    
    // These function calls below will execute them when you visit this
    // file in a browser: https://<server_address>/lib/test.php
    // To turn them off, just comment them out.
    createTestAccounts($baseUsername, $basePass, $baseEmailPrefix,
        $baseEmailSuffix);
    retrieveTestAccounts($baseUsername);
    testPasswordStorage('TestUser1', 'TestUserPass1');
?>
