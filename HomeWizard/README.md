# HomeWizard

This class is the result of reverse-engineering the communication between the HomeWizard Lite app and their servers. Because of this, not every possible action is documented here because I simply don't have access to example data on that.

### Initialization
```php
require_once(__DIR__ . "/homewizard.php");

use nl\JKCTech\HomeWizard as HomeWizard;

/**
 * @param string $username
 * @param string $sha1 Result of sha1(password)
 * @param bool $caching Enable / Disable session caching
 * 
 * @return bool 
 */
$hw = new HomeWizard("email@example.com", "cbfdac6008f9cab4083784cbd1874f76618d2a97", true);
```

### Session Settings
``` php
/**
 * HomeWizard generates session tokens for authentication.
 * These will be stored automatically in your $_SESSION variable if desired.
 * This dictates the amount of time after which we want a new session.
 * (In seconds)
 */
$hw->session_expire = 600;
```

### Get Plugs
``` php
/**
 * Return array of plug hubs on your account
 * including their connected devices.
 *
 * @return array $result
 */
$plugs = $hw->GetPlugs();
```

### Action
``` php
/**
 * Perform action on device.
 *
 * @param string $plug_id
 * @param string $device_id
 * @param string $action "On|Off"
 * 
 * @return array $result 
 */
$action = $hw->Action("4204696a-73ed-4679-b2b2-c8e7123666f44", "5665ccd4-1337-4f1b-9d6c-4d67feeded30", "On");
```