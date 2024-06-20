# importTest
The above task of parsing CSV file and store value to database is done using laravel

1. First pull the branch development
2. Create a database in MySql and update .env file with proper user and password
3. Run "php artisan migrate:fresh" to create database table
4. Next run "php artisan import:products /path/to/csv/file --test" to dry run the command
5. And "php artisan import:products /path/to/csv/file" to parse csv and insert the data in database 