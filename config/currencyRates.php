<?php
/**
 * Created by PhpStorm.
 * User: mindaugas
 * Date: 16.8.17
 * Time: 12.08
 */

$eurToUsd = 1.1497;
$eurToJpy = 129.53;


return [
    "EUR" => [
        "USD" => $eurToUsd,
        "JPY" => $eurToJpy,
    ],
    "USD" => [
        "EUR" => 1/$eurToUsd,
        "JPY" => 1/$eurToUsd/$eurToJpy,
    ],
    "JPY" => [
        "EUR" => 1/$eurToJpy,
        "USD" => 1/$eurToUsd
    ]
];