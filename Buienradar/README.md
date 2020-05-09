# Buienradar

### Initialization
```php
require_once(__DIR__ . "/buienradar.php");

use nl\JKCTech\Buienradar as Buienradar;

$br = new Buienradar();
```

### Caching Settings
``` php
/**
 * Enable / Disable caching.
 * If caching is disabled, all other settings are ignored.
 * If caching is enabled but an error occurs in caching,
 * If an error occurs in caching while enabled,
 * functions will fail and return false.
 */
$br->cache_enabled = true;

// Make folders if they don't exist yet.
$br->cache_makefolders = true;

// Folder to store cachedata in.
$br->cache_path = __DIR__ . "/cache/buienradar/";

// Time before we renew data. (In seconds)
$br->cache_expire = 300;
```

### Rain Forecast (2 Hours)
``` php
/**
 * Rainfaill predictions next 2 hours.
 * By default located at "De Bilt"
 * Will return array of times and rain in mm per hour.
 * 
 * @param integer $lat
 * @param integer $lon
 * @param int $expire Override expiretime for cache
 * 
 * @return mixed (False on failure, Array on success)
 */
$data = $br->Raintext(52.100, 5.171);
$data = $br->Raintext(52.100, 5.171, 240); // With cache expire override
```

### Current Weather Symbol
``` php
/**
 * Return the current weathersymbol as an image URL
 *
 * @param int $expire Override expiretime for cache
 *
 * @return mixed (False on failure, String (URL) on success)
 */
$data = $br->Weathersymbol();
$data = $br->Weathersymbol(600); // With cache expire override
```

### RadarMap URL or GIF
``` php
/**
 * URL Mode: Return a URL to a GIF of the current weather in a specific region.
 * File Mode: Return raw data of the above mentioned GIF.
 * (Caching only available on File Mode.)
 * 
 * @param bool $asfile Return raw file instead of url
 * @param int $expire Override expiretime for cache
 * @param string $region [NL|BE|EU]
 * @param integer $width [120-700]
 * @param integer $height [120-765]
 * 
 * @return mixed (False on failure, String on URL type, Bytes on Asfile type)
 */
$data = $br->RadarMap();
$data = $br->RadarMap(true, 300, "BE", 180, 180); // All parameters are optional
$data = $br->RadarMap(false, null, "EU", 120, 120); // Another example
```

### Global Feed
``` php
/**
 * Get the global Buienradar feed.
 * From here, we can filter other wanted items.
 * 
 * @param integer $expire Override expiretime for cache
 * 
 * @return mixed (False on failure, Object on success)
 */
$data = $br->Feed();
$data = $br->Feed(600); // With cache expire override
```

### Get Stationdata by field
``` php
/**
 * Filter data from current data provided by all weatherstations.
 *
 * @param string $field Field to check
 * @param string $value Value to compare to
 * @param string $operator [==|!=|===|!===|<>|<|>|<=|>=|contains]
 * @param integer $expire Override expiretime for cache
 * 
 * @return mixed (False on failure, Object on success)
 */
$data = $br->GetStationsByField("stationid", 6235, "==");
$data = $br->GetStationsByField("stationname", "Den Helder", "contains");
```