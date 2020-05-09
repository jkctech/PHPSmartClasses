# UptimeRobot (Readonly)

### Initialization
```php
require_once(__DIR__ . "/uptimerobot.php");

use nl\JKCTech\UptimeRobot as UptimeRobot;

$ur = new UptimeRobot("your_api_token");
```

### Get all monitors
``` php
/**
 * @return object
 */
$monitors = $ur->GetAllMonitors();
```