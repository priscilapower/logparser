## About API

To start to use, you need create a database named games in MySQL and put your credentials in the .env file.

After that, you must run the migrate in your terminal, this will create your table:

`$ php artisan migrate`

Then start your server like:

`$ php -S localhost:8000 -t public/`

Now, there're two endpoints you can use:

`/` get all the games from the database.
`/load` read the log **games.log**, located at the public path, and save the data on the database.

That's all falks !