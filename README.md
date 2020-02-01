PublicTransportInfo
===================

Backend for the PublicTransportInfo web app.


Usage
-----

Create a `config.php` file that returns an array with config values for the PublicTransportInfo instance. <br/>
Then you can use the PublicTransportInfo class as follows:

```php
// Create instance
$publicTransportInfo = new PublicTransportInfo\PublicTransportInfo(<path to config.php>);

// Fetch infos
$publicTransportInfo->getInfos()
```

See example/index.php for a complete usage example.


Configuration options
---------------------

| Config Key        | Description                                          | Default Value                                 |
|-------------------|------------------------------------------------------|-----------------------------------------------|
| cacheDirectory    | Directory in which cache files will be stored        | sys_get_temp_dir() . "/public-transport-info" |
| displayTimeSpan   | The time span for which any data will be displayed   | See displayTimeSpan                           |
| arrivalTimeSpan   | The time span for which arrivals will be displayed   | See arrivalTimeSpan                           |
| departureTimeSpan | The time span for which departures will be displayed | See departureTimeSpan                         |
| dataSources       | The data sources to fetch data from                  | See dataSources                               |


### displayTimeSpan ###

| Config Key | Description                                                            | Format | Default Value |
|------------|------------------------------------------------------------------------|--------|---------------|
| start      | Start of the time span in which any data will be displayed             | HH:mm  |         00:00 |
| modeSwitch | Time at which the display mode changes from "arrivals" to "departures" | HH:mm  |         12:00 |
| end        | End of the time span in which any data will be displayed               | HH:mm  |         24:00 |


### arrivalTimeSpan ###

| Config Key | Description                                                                       | Format | Default Value |
|------------|-----------------------------------------------------------------------------------|--------|---------------|
| past       | Time by which the arrival times may be in the past relative from the current time | HH:mm  | 0:0           |
| future     | Time by which the arrival times may be in the future from the current time        | HH:mm  | 0:0           |
| min        | The lowest displayable arrival time                                               | HH:mm  | 00:00         |
| max        | The highest displayable arrival time                                              | HH:mm  | 23:59         |

### departureTimeSpan ###

| Config Key | Description                                                                         | Format | Default Value |
|------------|-------------------------------------------------------------------------------------|--------|---------------|
| past       | Time by which the departure times may be in the past relative from the current time | HH:mm  |           0:0 |
| future     | Time by which the departure times may be in the past relative from the current time | HH:mm  |           0:0 |
| min        | The lowest displayable departure time                                               | HH:mm  |         00:00 |
| max        | The highest displayable departure time                                              | HH:mm  |         23:59 |
|            |                                                                                     |        |               |

### dataSources ###

This list is in the format "\<dataSourceType>" => "\<dataSourceConfig>".
The available data source types are: "Rmv"

These config values are available per data source:

| Config Key        | Description                                            | Default Value |
|-------------------|--------------------------------------------------------|---------------|
| factoryConfig     | The configuration for all instances of the data source | []            |
| stationInfoConfig | The stations for which data shall be fetched           | []            |

#### factoryConfig ####

These config values are available for all data source types:

| Config Key | Description | Default Value |
|------------|-------------|---------------|
| dataFetchInterval    | Duration in seconds for which a cached result will be valid |  300          |


These config values are only available for the "Rmv" data source:

| Config Key | Description                   | Default Value |
|------------|-------------------------------|---------------|
| apiToken   | The API token for the RMV API | -             |


#### stationInfoConfig ####

Available vehicleType's are "train" (trains) and "bus" (busses)

| Config Key  | Description                                               | Default Value |
|-------------|-----------------------------------------------------------|---------------|
| stations    | The station ids in the format "vehicleType" => stationIds | []            |
| ignoreLines | The line ids to exclude from the results                  | []            |
