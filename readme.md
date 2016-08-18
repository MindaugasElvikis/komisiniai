## Steps to do after cloning the project

* composer install
* php artisan key:generate

## Configs

You should check "commissions.php" and "currencyRates.php" configs.


## Commissions calculations.

* First, you have to create an csv file, each operation should be in new line and contains
- operation date, format `Y-m-d`
- client id, number
- client type,  `natural` or `juridical`
- operation type, `cash_in` or `cash_out`
- operation amount, number (eg. `2.12` or `3`)
- operation currency (`EUR`, `USD`, `JPY`)
* In command line enter project directory
* Run command 'php artisan calculate:commissions "File_path"'. (You should replace text "File_path" with path to your created csv file)

## Tests

* To run some tests, you should navigate to project directory with console and run "phpunit" command.




