# SMC CS 80 Chat App Project

## Overview
The goal of this project is to provide a basic chat system using a standard 
implementation stack to serve as an example of a full-stack web application
developed with modern code principles.

Features of the application have been intentionally left out in this version of
the project, and left as exercises to the student.

See the design doc [here](http://www.cs80-2016.com/alt-project-design-doc).

### MySQL import file
There is a mysql import file called "chatapp.sql" which contains the schema of
3 tables: "login_attempts", "messages", and "users". It's recommended to import
this file via PHPMyAdmin to a database called "chatapp", with a collation of
"utf8\_general\_ci".

You'll then need to go to "lib/classes/Helper.php", and replace the $user
variable in the openDbConnection() function with your own. The application
should run as-is without any other modifications.

More details instructions (with screenshots) for this can be found at
[this link](http://www.cs80-2016.com/alt-project-setup#heading=h.70r4rfn63trz).