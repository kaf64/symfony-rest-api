# Simple Symfony REST Api
Simple application in REST architecture based on Symfony 4.4 
## Requirements
- PHP version: 7.1.3 or higher
- [Composer](https://getcomposer.org/download/) installed  
More about requirements and setting up at [Symfony documentation page](https://symfony.com/doc/4.4/setup.html).
## Endpoints/Functions
- (GET) api/user - get all users from database  
example response:
````
[
    {
        "id": 1,
        "username": "admin",
        "roles": [
            "ROLE_USER"
        ],
        "password": "$2y$13$Oj5MagX01DQrwbry2w6LMeMVpOCDE25H4p1IzaatYptWAOmAZxSTa"
    }
]
````
- (GET) api/user/id -  get user with specific id (if exists)  
example response:
````
{
    "id": 1,
    "username": "admin",
    "roles": [
        "ROLE_USER"
    ],
    "password": "$2y$13$Oj5MagX01DQrwbry2w6LMeMVpOCDE25H4p1IzaatYptWAOmAZxSTa"
}
````
- (POST) api/user - add one new user, keys: "username" and "password" (in plain tekst)  
example request content:
````
{
		"username":"user1",
		"password":"pass1"
}
````
example response (if success):
````
{
    "status": "User created"
}
````
example response (if fail):
````
{
    "status": "error",
    "error_message": "Can't create user - login incorrect, use different login and try again"
}
````
- (PUT) user/id -  edit user with specific id (if user exists, can edit keys "username" and/or "password")
example request content:
````
{
		"username":"user1",
		"password":"pass1"
}
````
example response (if success):
````
{
    "status": "User successfully edited"
}
````
example response (if fail):
````
{
    "status": "Cannot edit - user not found"
}
````

- (DELETE) user/id - delete user with specific id (if exists)  
example response (if success):
````
{
    "status": "User deleted"
}
````
example response (if fail):
````
{
    "status": "Cannot delete - user not found"
}
````
Fields from every user (GET):
- id
- username
- roles (array)
- password (hashed)  
You can login through bowser using default address.  
To work with api I'm using app called [Postman](https://www.postman.com/).
## Installation
All mentioned commands should be used in console. To install application, follow steps below.  
**First**, download all files. Use `git clone <link>` command or download directly as zip archive. Click button **clone or download** at github page at upper right and select method.  
**Next**, go to downloaded directory (if you download archive, unpack it first) and use `composer install` command to install dependencies.  
**Finally**, use `symfony server:start` command to start a server and enter `http://localhost:8000/` in browser to get to main page.  
More information on [Symfony documentation page](https://symfony.com/doc/4.4/setup.html#setting-up-an-existing-symfony-project).
### Setting up database & load fixtures
Project uses database to store information about registered users. To configure database, follow steps below (all mentioned commands should be used in console):  
**First**, create database and user with right permission. Check documentation of your hosting provider.  
**Next**, create file **.env.local** in root directory of project, from **.env** file copy line starting from `DATABASE_URL`, paste it to **.env.local** and change fields **db_user, db_password, db_name** to values corresponded to database created in previous step. Default database engine is mysql, if you use other engine, change **mysql** value in **.env.local** file.  
**Next**, use command `php bin/console make:migration` to create migration file used to create needed tables, fields, etc. If command returns information like "SUCCESS", go to next step.    
**Next**, use command `php bin/console doctrine:migrations:migrate` to execute migration created in previous step. If command returns in console error like "permission denided", check your database parameters in **.env.local** file.  
**Finally**, use command `php bin/console doctrine:fixtures:load` to load to database first user with login **admin** and password **admin**.  
Making separated **.env** file to each environment is a part of [Symfony best practices](https://symfony.com/doc/4.4/best_practices.html#use-environment-variables-for-infrastructure-configuration).  
More about configuring databases in [Symfony documentation page](https://symfony.com/doc/4.4/doctrine.html).
### Additional .htaccess to security and subdomain
To make it work at remote server, you have to make additional configuration.  
All steps are described on [Symfony documentation page](https://symfony.com/doc/4.4/setup/web_server_configuration.html).  
If you are not able to create virtual hosts (ie. shared hosting), you can use subdomain. To make subdomain, check documentation of your hosting provider.  
After adding subdomain, create .htaccess file in root directory of your project to make this work.  
Here is code for address **subdomain.example.com**:
```
RewriteEngine on
RewriteCond %{HTTP_HOST} ^subdomain.example.com$ [NC,OR]
RewriteCond %{HTTP_HOST} ^subdomain.example.com$
RewriteCond %{REQUEST_URI} !public/
RewriteRule (.*) /public/$1 [L]
```
If you want to secure access only to your computer (ie. when you working on site and you want temporary disable access to others) you can use .htaccess file, just add these lines at end to yout .htaccess file in project root directory:
```
order deny,allow
deny from all
allow from <ip_address>
```
Of course, replace **<ip_address>** with your real ip adress, to check actual address of your machine, use site like [who.is](https://who.is/).  
When you want to open site for everyone, just delete these lines.
### License
Project is under [MIT License](./LICENSE)

