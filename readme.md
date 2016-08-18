## Steps to do after cloning the project

* composer install
* php artisan key:generate

## Configs

You should check "commissions.php" and "currencyRates.php" configs.


## Commissions calculations.

* First, you have to create an csv file, each operation should be in new line and contains
* operation date, format `Y-m-d`
* client id, number
* client type,  `natural` or `juridical`
* operation type, `cash_in` or `cash_out`
* operation amount, number (eg. `2.12` or `3`)
* operation currency (`EUR`, `USD`, `JPY`)
* In command line enter project directory
* Run command 'php artisan calculate:commissions "File_path"'. (You should replace text "File_path" with path to your created csv file)

## Tests

* To run some tests, you should navigate to project directory with console and run "phpunit" command.

## Program functionality

* Whole program is mostly in one file (app/Console/Commands/ConvertCurrency.php).
* It has different functions for cash in and cash out operations.
* Cash in commissions function is pretty straightforward.
* Cash out commissions function is much more complicated, it checks if user is natural or juridical,
* if user is juridical, then there is not much problem, since they always have the same commission rate.
* But cash out for natural users is more complicated,
* because of that, I call function to check if user has any commission free cash outs left.
* Mostly used function is money convertion function, it converts given amount of money from one currency to another.
* There is also function to round up number.




