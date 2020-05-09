<?php
	/**
	 * ADSB-X
	 * 
	 * Author: JKCTech
	 * Date: 14-03-2020
	 * 
	 * Connect with the ADSB-X REST API to get access to aircraft data.
	 * More info here: https://www.adsbexchange.com/data/
	 * 
	 */

	Class ADSBX
	{
		const API_URL = "https://adsbexchange.com/api/aircraft/";

		private $api_key;
		private $errors;

		/**
		 * @param string $key
		 * 
		 * @return void
		 */
		public function __construct (string $key)
		{
			$this->api_key = $key;
			$errors = array();
		}

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
		public function get_all()
		{
			return $this->get("json/");
		}

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
		public function get_military()
		{
			return $this->get("mil/");
		}

		/**
		 * Search for an aircraft by it's ICAO hex code.
		 * 
		 * https://adsbexchange.com/api/aircraft/icao/4844C2/
		 *
		 * @param string $icao
		 * 
		 * @return string
		 */
		public function get_icao(string $icao)
		{
			return $this->get(sprintf("icao/%s/", strval($icao)));
		}

		/**
		 * Search for aircraft(s) by it's squawk code.
		 * 
		 * https://adsbexchange.com/api/aircraft/sqk/6221/
		 *
		 * @param integer $sqk
		 * 
		 * @return string
		 */
		public function get_squawk(int $sqk)
		{
			return $this->get(sprintf("sqk/%s/", strval($sqk)));
		}

		/**
		 * Search for aircraft(s) by it's ADSB-X registration number.
		 * 
		 * https://adsbexchange.com/api/aircraft/registration/57-1469/
		 * 
		 * @param string $reg
		 * 
		 * @return string
		 */
		public function get_registration(string $reg)
		{
			return $this->get(sprintf("registration/%s/", $reg));
		}

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
		public function get_range(float $lat, float $lon, int $dist)
		{
			return $this->get(sprintf("json/lat/%s/lon/%s/dist/%s/", strval($lat), strval($lon), strval($dist)));
		}

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
		public function download_all(string $path)
		{
			if (empty($path))
				return false;
			
			$file = $path . date("YmdHis") . ".json";

			if(!file_exists(dirname($file)))
    			mkdir(dirname($file), 0775, true);

			$result = touch($file);

			if (!$result)
			{
				$this->error("Could not create file " . $file);
				return false;
			}
			
			$response = $this->get("json/");

			if (!$response)
				return false;

			$result = file_put_contents($file, $response);

			if (!$result)
				$this->error("Could not save file " . $file);

			return $file;
		}

		/**
		 * @param string $endpoint
		 * 
		 * @return string
		 */
		public function get(string $endpoint)
		{
			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => self::API_URL . $endpoint,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "GET",
				CURLOPT_HTTPHEADER => array("api-auth: " . $this->api_key),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err)
			{
				$this->error("Response error: " . $err);
				return false;
			}

			return $response;
		}

		/**
		 * Add error to list.
		 * 
		 * @param string $message
		 * 
		 * @return void
		 */
		private function error(string $message)
		{
			$this->errors[] = $message;
		}

		/**
		 * Return array of errors.
		 * 
		 * @param bool $clear Clear array after read.
		 * 
		 * @return array
		 */
		public function errors(bool $clear = true)
		{
			$list = $this->errors;
			if ($clear)
				$list = array();
			return $list;
		}
	}
?>