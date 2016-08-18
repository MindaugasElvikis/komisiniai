<?php
/**
 * Created by PhpStorm.
 * User: mindaugas
 * Date: 16.8.17
 * Time: 21.04
 */

return [
    "default_currency" => "EUR",
    "cash_in" => [
        "commission" => 0.0003,
        "max" => 5,
    ],
    "cash_out" => [
        "juridical" => [
            "commission" => 0.003,
            "min" => 0.5,
        ],
        "natural" => [
            "commission" => 0.003,
            "commission_free" => 1000,
            "commission_free_count" => 3,

            /**
             * Commission free time, this is strict option.
             *
             * Available options: Day, Week, Month, Year
             */
            "commission_free_days" => "Week",
        ]
    ]
];