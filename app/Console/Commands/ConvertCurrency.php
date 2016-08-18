<?php
/**
 * Created by PhpStorm.
 *
 * @author Mindaugas Elvikis <mindaugas.elvikis@gmail.com>
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;
use Carbon;

/**
 * Class ConvertCurrency
 * @package App\Console\Commands
 */
class ConvertCurrency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:commissions {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate commissions from gived data in file.';

    /**
     * Money transactions data.
     *
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $currencyRates;

    /**
     * @var string
     */
    private $defaultCurrency;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->currencyRates = config('currencyRates');
        $this->defaultCurrency = config('commissions.default_currency');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if ($this->readData($filePath)) {

            foreach ($this->data as $item) {
                if ($item["operation"] == "cash_out") {
                    $result = $this->cashOutCommissions($item);
                } else {
                    $result = $this->cashInCommissions($item["amount"], $item["currency"]);
                }
                $this->info(number_format($result, 2));
            }
        } else {
            $this->error("The provided file was not found!");
        }
    }

    /**
     * Convert money from one currency to another.
     *
     * @param float     $amount
     * @param string    $from
     * @param string    $to
     *
     * @return float
     */
    private function convertMoney($amount, $from, $to)
    {
        return $amount * $this->currencyRates[$from][$to];
    }

    /**
     * Calculate cash in commissions
     *
     * @param float     $amount
     * @param string    $currency
     *
     * @return float
     */
    private function cashInCommissions($amount, $currency)
    {
        $maxCommission = config('commissions.cash_in.max');
        $commissionRate = config('commissions.cash_in.commission');
        $commissions = $amount * $commissionRate;

        if ($currency != $this->defaultCurrency) {
            $maxCommission = $this->convertMoney($maxCommission, $this->defaultCurrency, $currency);
        }

        return $this->round_up($commissions > $maxCommission ? $maxCommission : $commissions, 3);
    }

    /**
     * Calculate cash out commissions.
     *
     * @param array $operation
     *
     * @return float
     */
    private function cashOutCommissions($operation)
    {
        if ($operation["user_type"] == "juridical") {
            $commissionRate = config('commissions.cash_out.juridical.commission');
            $minCommission = config('commissions.cash_out.juridical.min');
            $commissions = $operation["amount"] * $commissionRate;

            if ($operation["currency"] != $this->defaultCurrency) {
                $minCommission = $this->convertMoney($minCommission, $this->defaultCurrency, $operation["currency"]);
            }

            return $this->round_up($commissions < $minCommission ? $minCommission : $commissions, 3);
        }

        $commissionRate = config('commissions.cash_out.natural.commission');

        $amountToCalculateCommissions = $this->commissionFree($operation);

        $commissions = $amountToCalculateCommissions * $commissionRate;

        return $this->round_up($commissions, 3);
    }

    /**
     * Calculate if natural client still have some commission free cash outs left.
     *
     * @param array $operation
     *
     * @return float
     */
    private function commissionFree($operation)
    {
        $commissionFree = config('commissions.cash_out.natural.commission_free');
        if ($operation["currency"] != $this->defaultCurrency) {
            $commissionFree = $this->convertMoney($commissionFree, $this->defaultCurrency, $operation["currency"]);
        }
        $commissionFreeCount = config('commissions.cash_out.natural.commission_free_count');
        $commissionFreeDays = config('commissions.cash_out.natural.commission_free_days');

        $start = Carbon\Carbon::createFromFormat('Y-m-d', $operation["date"])->{'startOf' . $commissionFreeDays}();
        $end = Carbon\Carbon::createFromFormat('Y-m-d', $operation["date"])->{'endOf' . $commissionFreeDays}();

        $count = $commissionFreeCount;
        $amount = $commissionFree;

        foreach ($this->data as $item) {
            if ($item["id"] == $operation["id"]) {
                break;
            } else if ($item["user"] == $operation["user"] && $item["operation"] == "cash_out") {
                $operationDate = Carbon\Carbon::createFromFormat('Y-m-d', $item["date"]);
                if ($operationDate->getTimestamp() > $start->getTimestamp()
                    && $operationDate->getTimestamp() < $end->getTimestamp()
                ) {
                    $count--;

                    if ($item["currency"] == $operation["currency"]) {
                        $cashOutAmount = $item["amount"];
                    } else {
                        $cashOutAmount = $this->convertMoney($item["amount"], $item["currency"], $operation["currency"]);
                    }
                    $amount = $amount - $cashOutAmount;

                    if ($count == 0 || $amount <= 0) {
                        return $operation["amount"];
                    }
                }
            }
        }

        if ($amount >= $operation["amount"]) {
            return 0;
        }

        return $operation["amount"] - $amount;
    }

    /**
     * Read data from csv file.
     *
     * @param string $filePath
     *
     * @return boolean
     */
    private function readData($filePath)
    {
        if (File::exists($filePath)) {
            $file = fopen($filePath, 'r');

            try {
                $i = 1;
                while (!feof($file)) {
                    $row = fgetcsv($file, 0, ',');

                    list($operation["date"], $operation["user"], $operation["user_type"], $operation["operation"], $operation["amount"], $operation["currency"]) = $row;
                    $operation["id"] = $i;
                    $this->data[] = $operation;
                    $i++;
                }
            } finally {
                fclose($file);
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Round number up.
     *
     * @param float $number
     * @param int   $precision
     *
     * @return float
     */
    private function round_up($number, $precision = 2)
    {
        $fig = (int)str_pad('1', $precision, '0');
        return (ceil($number * $fig) / $fig);
    }
}
