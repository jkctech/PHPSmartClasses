<?php
	/**
	 * UptimeRobot Connector
	 * 
	 * Author: JKCTech
	 * Date: 06-05-2020
	 * 
	 * Connect with UptimeRobot and display your monitors
	 * 
	 */

	namespace nl\JKCTech;

	Class UptimeRobot 
	{
		const API = "https://api.uptimerobot.com/v2/";

		private $token;
		
		/**
		 * @param string $token
		 * 
		 * @return void
		 */
		public function __construct (string $token)
		{
			$errors = array();

			$this->token = $token;
		}

		/**
		 * Perform a HTTP POST request to UptimeRobot
		 *
		 * @param string $method
		 * @param array $params
		 * 
		 * @return object
		 */
		private function Request(string $method, array $params = array())
		{
			$ch = curl_init();

			$params['format'] = "json";
			$params['api_key'] = $this->token;

			curl_setopt($ch, CURLOPT_URL, self::API . $method);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
			
			$result = json_decode(curl_exec($ch));

			curl_close($ch);

			return $result;
		}
		
		/**
		 * @return object
		 */
		public function GetAllMonitors()
		{
			$data = $this->Request("getMonitors");
			return ($data);
		}
	}
?>