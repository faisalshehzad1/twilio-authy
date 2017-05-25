# twilio-authy

Twilio with Authy:

Framework: Codeignitor 3.0
Authy API Library


Installation:

1) Copy the zip file inside xamp -> htdocs
2) Extract the zip files inside a folder.
3) Access your localhost/phpmyadmin 
4) Under "sql" folder you will find the dump sql file of the project
5) Create a database inside phpmyadmin i.e "simple_db" and import the dump
6) Open application -> config folder.
7) You find "database.php" and "config.php" files with other files.
8) First open "database.php" file and change the:
		'hostname' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'simple_db'
	as required and save.
9) Open "config.php" file inside the same folder.
10) Change the "authy_key" as per your "Authy API Key" and save.


How This App Works:

Registration Process:

In this app users register themselves by using the “register form”. After the registration application, asks for a valid phone number with a country code. An SMS code is being sent to the provided number for confirmation of the details. After the user submits the SMS code (Token) a call is being sent to Authy by using Authy API to verify the “SMS Code” against the email address. On confirmation user is been saved into to “users” table against his authy_id and other information and redirected to the login area.

Login Area:

This section has two type of authentications.

1)	Authentication via SoftToken from Authy App
2)	Authentication using Authy OneTouch


Authentication via SoftToken from Authy App:
1)	User type his email address and password.
2)	If correct login credentials are being provided by the user he is redirect to another form for Authy authentication.
3)	Here we ask user for his “Authy Token”. Authy token can be found on the user installed “Authy App” on his smart device.
4)	If he doesn’t have an App installed, he can request for an SMS Code alert that will be sent on the provided number while registration.
5)	On providing and submitting the Token we check the provided Token with his email address and verify the transaction.
6)	Once valid he is redirected to the Dashboard area.


Authentication using Authy OneTouch:

This is the newest feature provided by the Authy.
1)	User provides his login information
2)	After validating him from the database he is being redirect to the Authentication Page where he has option to login via OneTouch button.
3)	After clicking on the “OneTouch” button an API call is been sent to Authy with the user “authy_id” for approval request.
4)	A notification is being sent on the user app against this login.
5)	Apps keep checking the response of the user decision.
6)	If Approved the user is logged in to the application and redirected to the dashboard area.


Please visit the online demo version to check the funcationality:
Online Demo: https://jadopado-twilio.herokuapp.com/auth/login