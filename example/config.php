<?php

return array(

    "cacheDirectory" => "/tmp/public-transport-info",

    "displayTimeSpan" => array(
        "start" => "07:00",
        "modeSwitch" => "11:00", // When to switch from arrivals to departures
        "end" => "18:00"
    ),

    // Time span for arrivals
    "arrivalTimeSpan" => array(
        "past" => "1:00",
        "future" => "0:10",
        "min" => "06:00", // Min planned time
        "max" => "11:10" // Max planned time
    ),

    // Time span for departures
    "departureTimeSpan" => array(
        "past" => "0:10",
        "future" => "1:00",
        "min" => "10:50", // Min planned time
        "max" => "19:00" // Max planned time
    ),


    // The data sources
    "dataSources" => array(
        "Rmv" => array(

            "factoryConfig" => array(
                "apiToken" => "<YOUR API KEY HERE>",

                /*
                 * Data fetch interval in seconds
                 *
                 * Note:
                 * You can make up to 600 requests per hour and up to 5000 requests per day.
                 * Each station id will cause its own request per data refresh.
                 * This must be taken into account when setting this value.
                 *
                 * For example with this config you would fetch data from 07:00 to 18:00 o'clock.
                 * This means 9 hours = 540 minutes => * 2 stations (configured below) = 1080 requests per day
                 */
                "dataFetchInterval" => 60
            ),

            "stationInfoConfig" => array(
                "stations" => array(

                    // Dillenburg Bahnhof (= trains)
                    "train" => array("003011005"),

                    // Dillenburg ZOB (= busses)
                    "bus" => array("003021243")
                ),
                "ignoreLines" => array(
                    "100" // Bus line 100 = Dillenburg Stadtverkehr
                )
            )
        )
    )
);
