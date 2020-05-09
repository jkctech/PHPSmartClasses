# ADSB-X (ADS-B Exchange)

### Initialization
```php
require_once(__DIR__ . "/adsbx.php");

use nl\JKCTech\ADSBX as ADSBX;

/**
 * @param string $key
 * 
 * @return void
 */
$adsbx = new ADSBX("your_api_token");
```

### Get global worldwide dataset
``` php
/**
 * Get ALL aircrafts.
 * 
 * https://adsbexchange.com/api/aircraft/json/
 * 
 * Warning!
 * This file can become quite big and can take several seconds to finish.
 * 
 * @return string
 */
$all = json_decode($adsbx->get_all());
```

### Download global worldwide dataset to file
``` php
/**
 * Download ALL aircraft data from ADSB-X
 * 
 * https://adsbexchange.com/api/aircraft/json/
 * 
 * Attempt to create target file, if succesfull, runs get_all().
 * Save this to a file in the given path.
 * 
 * Given path in paramter needs trailing slash!
 * 
 * @param string $path
 * 
 * @return string filepath
 */
$file = $adsbx->download_all(__DIR__ . "/cache/adsbx/");
```

### Get all military aircrafts
``` php
/**
 * Get all aircrafts tagged as "Military".
 * 
 * https://adsbexchange.com/api/aircraft/mil/
 * 
 * Warning!
 * This file can become quite big and can take several seconds to finish.
 * 
 * @return string
 */
$military = json_decode($adsbx->get_military());
```

### Search aircrafts by ICAO
``` php
/**
 * Search for an aircraft by it's ICAO hex code.
 * 
 * https://adsbexchange.com/api/aircraft/icao/484B58/
 *
 * @param string $icao
 * 
 * @return string
 */
$aircrafts = json_decode($adsbx->get_icao("484B58"));
```

### Search aircrafts by squawk (transponder) code
``` php
/**
 * Search for aircraft(s) by it's squawk code.
 * 
 * https://adsbexchange.com/api/aircraft/sqk/6221/
 *
 * @param integer $sqk
 * 
 * @return string
 */
$aircrafts = json_decode($adsbx->get_squawk(6221));
```

### Search aircrafts by ADS-B Exchange registration code
``` php
/**
 * Search for aircraft(s) by it's ADSB-X registration number.
 * 
 * https://adsbexchange.com/api/aircraft/registration/57-1469/
 * 
 * @param string $reg
 * 
 * @return string
 */
$aircrafts = json_decode($adsbx->get_registration("57-1469"));
```

### Search aircrafts by GPS coördinate and range
``` php
/**
 * Return aircrafts in X nautical miles from a given Lat - Lon point
 * 
 * https://adsbexchange.com/api/aircraft/json/lat/52.956280/lon/4.760797/dist/10/
 * 
 * @param float $lat
 * @param float $lon
 * @param integer $dist
 * 
 * @return string
 */
$aircrafts = json_decode($adsbx->get_range(52.956280, 4.760797, 10));
```