### How to run
To start copy the .env.example to a new .env file, fill in the db connection, fill in the app's url and fill in the gateway id and encryption key for paystar.

You can also change the Paystar credentials in the config/paystar.php file directly.

then run:
```shell
composer update
php artisan key:generate
php artisan migrate
```

to add the test user and test product run:
```shell
php artisan custom-seeder {cardNumber}
```
I case you want to change the cardNumber you can do so by re running the command with your new card number.

You can run this on a server or through ngrok on your local system.


