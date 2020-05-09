<?php
	/**
	 * Buienradar Connector
	 * 
	 * Author: JKCTech
	 * Date: 09-05-2020
	 * 
	 * https://www.buienradar.nl/overbuienradar/gratis-weerdata - Information about this API
	 * https://data.buienradar.nl/2.0/feed/json - All weatherstations and global data (JSON)
	 * https://gpsgadget.buienradar.nl/data/raintext?lat=51&lon=3 - GPS rainfall table (Formulas on information page)
	 * https://gadgets.buienradar.nl/gadget/weathersymbol - Weathersymbol URL (PNG)
	 * https://api.buienradar.nl/image/1.0/RadarMapNL?w=256&h=256 - GIF Country overview of the weather (120 x 120 - 700 x 765)
	 * 
	 * The Buienradar API may be used freely, provided that buienradar.nl is acknowledged, including a hyperlink to https://www.buienradar.nl.
	 * No rights can be derived from the feed by users or other persons.
	 *
	 * De Buienradar API mag vrij worden gebruikt onder voorwaarde van bronvermelding buienradar.nl inclusief een hyperlink naar https://www.buienradar.nl.
	 * Aan de feed kunnen door gebruikers of andere personen geen rechten worden ontleend.
	 * 
	 */

	namespace nl\JKCTech;

	Class Buienradar 
	{
		const API_JSON = "https://data.buienradar.nl/2.0/feed/json/";
		const API_GPSGADGET = "https://gpsgadget.buienradar.nl/data/";
		const API_GADGET = "https://gadgets.buienradar.nl/gadget/";
		const API_MAP = "https://api.buienradar.nl/image/1.0/RadarMap";

		/**
		 * Caching control.
		 * If caching is disabled, all settings will be ignored.
		 * If no path is defined, caching will be disabled.
		 * If an error is encountered while making folders or writing -
		 * to cache files, caching will be disabled.
		 */
		public $cache_enabled = false;
		public $cache_makefolders = true;
		public $cache_path;
		public $cache_expire = 240;

		/**
		 * @param string $url
		 * @param array $params
		 * 
		 * @return string
		 */
		private function Request(string $url, array $params = array())
		{
			$ch = curl_init();
			if (is_array($params) && count($params) > 0)
				$url .= "?" . http_build_query($params);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			$result = curl_exec($ch);
			curl_close($ch);
			return $result;
		}

		/**
		 * @return bool success
		 */
		private function CacheReady()
		{
			if (empty($this->cache_path))
				return false;
			if ($this->cache_expire < 0)
				return false;
			if (file_exists($this->cache_path))
				return is_writable($this->cache_path);
			else
			{
				if (!$this->cache_makefolders)
					return false;
				if(!mkdir($this->cache_path, 0755, true))
					return false;
				return is_writable($this->cache_path);
			}
		}

		/**
		 * @param string $filename
		 * @param string $data
		 * 
		 * @return bool success
		 */
		private function CacheSave(string $filename, string $data)
		{
			if (!$this->CacheReady())
				return false;
			return file_put_contents($filename, $data);
		}

		/**
		 * @param string $filename
		 * @param int $expire Override expire time
		 * 
		 * @return mixed (False on cache fail, True on cache success but expired, data elsewise)
		 */
		private function CacheLoad(string $filename, int $expire = null)
		{
			if (is_null($expire))
				$expire = $this->cache_expire;
			if (!$this->CacheReady())
				return false;
			if(!file_exists($filename))
				return true;
			if (time() - filemtime($filename) >= $expire)
				return true;
			return file_get_contents($filename);
		}

		/**
		 * Rainfaill predictions next 2 hours.
		 * By default located at "De Bilt"
		 * Will return array of times and rain in mm per hour.
		 * 
		 * @param float $lat
		 * @param float $lon
		 * @param int $expire Override expiretime for cache
		 * 
		 * @return mixed (False on failure, Array on success)
		 */
		public function Raintext(float $lat = 52.100, float $lon = 5.171, int $expire = null)
		{
			$data = "";
			$lat = round($lat, 3);
			$lon = round($lon, 3);

			// Attempt cache read
			if ($this->cache_enabled)
			{
				if (substr($this->cache_path, -1) != '/')
					$this->cache_path .= '/';
				$fn = $this->cache_path . "raintext_" . intval($lat * 1000) . "-" . intval($lon * 1000) . ".txt";

				if (is_null($expire))
					$expire = $this->cache_expire;

				$cd = $this->CacheLoad($fn, $expire);
				if ($cd == false)
					return false;
				if ($cd !== true)
					$data = $cd;
			}

			// Get from server if no cache available or expired
			if (empty($data))
			{
				$data = $this->Request(self::API_GPSGADGET . "raintext", array(
					"lat" => $lat, 
					"lon" => $lon
				));

				// Save to cache if enabled
				if ($this->cache_enabled)
				{
					if(!$this->CacheSave($fn, $data))
						return false;
				}
			}

			// Process
			$forecast = array();
			foreach(explode(PHP_EOL, $data) as $line) 
			{
				$details = explode("|", $line);
				if (count($details) != 2)
					continue;
				$mm = round(pow(10, ((intval($details[0]) - 109 ) / 32 )), 1);
				$forecast[trim($details[1])] = $mm;
			}
			return $forecast;
		}

		/**
		 * Return the current weathersymbol as an image URL
		 *
		 * @param int $expire Override expiretime for cache
		 *
		 * @return mixed (False on failure, String (URL) on success)
		 */
		public function Weathersymbol(int $expire = null)
		{
			$data = "";

			// Attempt cache read
			if ($this->cache_enabled)
			{
				if (substr($this->cache_path, -1) != '/')
					$this->cache_path .= '/';
				$fn = $this->cache_path . "weathersymbol.txt";

				if (is_null($expire))
					$expire = $this->cache_expire;

				$cd = $this->CacheLoad($fn, $expire);
				if ($cd == false)
					return false;
				if ($cd !== true)
					$data = $cd;
			}

			// Get from server if no cache available or expired
			if (empty($data))
			{
				$data = $this->Request(self::API_GADGET . "weathersymbol");
				preg_match("/\/images\/weathericons.+\.png/m", $data, $matches);
				$data = "https://gadgets.buienradar.nl" . $matches[0];

				// Save to cache if enabled
				if ($this->cache_enabled)
				{
					if(!$this->CacheSave($fn, $data))
						return false;
				}
			}
			
			return $data;
		}

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
		public function RadarMap(bool $asfile = false, int $expire = null, string $region = "NL", int $width = 256, int $height = 256)
		{
			$data = "";
			$region = strtoupper($region);

			// Size constraints
			if ($width < 120 || $width > 700 || $height < 120 || $height > 765)
				return false;

			// Allow specific regions only
			if (!in_array($region, array("NL", "BE", "EU")))
				return false;

			// Attempt cache read if enabled and filetype is GIF
			if ($this->cache_enabled && $asfile)
			{
				if (substr($this->cache_path, -1) != '/')
					$this->cache_path .= '/';
				$fn = $this->cache_path . sprintf("radarmap%s_%dx%d.gif", strtolower($region), $width, $height);

				if (is_null($expire))
					$expire = $this->cache_expire;

				$cd = $this->CacheLoad($fn);
				if ($cd == false)
					return false;
				if ($cd !== true)
					$data = $cd;
			}

			// Get from server if no cache available or expired
			if (empty($data))
			{
				$params = array(
					"w" => $width,
					"h" => $height
				);
				if ($asfile)
					$data = $this->Request(self::API_MAP . $region, $params);
				else
					$data = self::API_MAP . $region . "?" . http_build_query($params);

				// Save to cache if enabled & filetype is gif
				if ($this->cache_enabled && $asfile)
				{
					if(!$this->CacheSave($fn, $data))
						return false;
				}
			}
			
			return $data;
		}

		/**
		 * Get the global Buienradar feed.
		 * From here, we can filter other wanted items.
		 * 
		 * @param integer $expire Override expiretime for cache
		 * 
		 * @return mixed (False on failure, Object on success)
		 */
		public function Feed(int $expire = null)
		{
			$data = "";

			// Attempt cache read
			if ($this->cache_enabled)
			{
				if (substr($this->cache_path, -1) != '/')
					$this->cache_path .= '/';
				$fn = $this->cache_path . "feed.json";

				if (is_null($expire))
					$expire = $this->cache_expire;

				$cd = $this->CacheLoad($fn, $expire);
				if ($cd == false)
					return false;
				if ($cd !== true)
					$data = $cd;
			}

			// Get from server if no cache available or expired
			if (empty($data))
			{
				$data = $this->Request(self::API_JSON);

				// Save to cache if enabled
				if ($this->cache_enabled)
				{
					if(!$this->CacheSave($fn, $data))
						return false;
				}
			}

			return json_decode($data);
		}

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
		public function GetStationsByField(string $field, string $value, string $operator = "==", int $expire = null)
		{
			$feed = $this->Feed($expire);

			if (!is_object($feed))
				return false;

			$result = array();

			foreach($feed->actual->stationmeasurements as $station)
			{
				if (!isset($station->{$field}))
					continue;

				if ($operator == "==")
					$cmp = $station->{$field} == $value;
				else if ($operator == "!=")
					$cmp = $station->{$field} != $value;
				else if ($operator == "===")
					$cmp = $station->{$field} === $value;
				else if ($operator == "!==")
					$cmp = $station->{$field} !== $value;
				else if ($operator == "<>")
					$cmp = $station->{$field} <> $value;
				else if ($operator == "<")
					$cmp = $station->{$field} < intval($value);
				else if ($operator == ">")
					$cmp = $station->{$field} > intval($value);
				else if ($operator == "<=")
					$cmp = $station->{$field} <= intval($value);
				else if ($operator == ">=")
					$cmp = $station->{$field} >= intval($value);
				else if ($operator == "contains")
					$cmp = strpos($station->{$field}, strval($value)) !== false;
				else
					return false;
				
				if ($cmp)
					$result[] = $station;
			}

			return (object) $result;
		}
	}
?>