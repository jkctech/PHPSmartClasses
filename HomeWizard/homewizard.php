<?php
	/**
	 * HomeWizard Connector
	 * 
	 * Author: JKCTech
	 * Date: 01-05-2020
	 * 
	 * Connect with the HomeWizard Lite service and control your devices.
	 * 
	 */

	namespace nl\JKCTech;

	Class HomeWizard 
	{
		const API_AUTH = "https://cloud.homewizard.com/";
		const API_PLUG = "https://plug.homewizard.com/";

		private $authstring;
		private $session;
		public $session_expire = 600;
		
		/**
		 * @param string $username
		 * @param string $sha1 Result of sha1(password)
		 * @param bool $caching Enable / Disable session caching
		 * 
		 * @return bool 
		 */
		public function __construct (string $username, string $sha1, bool $caching = false)
		{
			$errors = array();

			$this->authstring = base64_encode(sprintf("%s:%s", $username, $sha1));

			// Caching
			if ($caching)
			{
				if (session_status() == PHP_SESSION_NONE)
					session_start();
				if (isset($_SESSION['cache']['homewizard'][$this->authstring]))
				{
					if (time() - $_SESSION['cache']['homewizard'][$this->authstring]['time'] > $this->session_expire)
					{
						$this->CreateSession();

						$_SESSION['cache']['homewizard'][$this->authstring]['session'] = $this->session;
						$_SESSION['cache']['homewizard'][$this->authstring]['time'] = time();
					}
					else
					{
						$this->session = $_SESSION['cache']['homewizard'][$this->authstring]['session'];
					}
				}
				else
				{
					$this->CreateSession();

					$_SESSION['cache']['homewizard'][$this->authstring]['session'] = $this->session;
					$_SESSION['cache']['homewizard'][$this->authstring]['time'] = time();
				}
			}

			// Create new session always if we don't use caching
			else
			{
				$this->CreateSession();
			}
		}

		/**
		 * Create session on the HomeWizard servers
		 *
		 * @return bool $success
		 */
		private function CreateSession()
		{
			try
			{
				$session = $this->Request(
					self::API_AUTH . "account/login",
					"GET",
					array('header'  => "Authorization: Basic " . $this->authstring . "\r\n")
				);

				if (isset($session->errorMessage))
				{
					die("HW Authentication error (1): " . $session->errorMessage);
					return false;
				}

				$this->session = $session->session;
			}
			catch (Exception $e)
			{
				return false;
			}
		}

		/**
		 * Perform a HTTP request to HomeWizard
		 *
		 * @param string $url
		 * @param string $method
		 * @param array $options
		 * @param array $body
		 * 
		 * @return array $result
		 */
		private function Request(string $url, string $method, array $options = array(), array $body = array())
		{
			$options['method'] = strtoupper($method);

			if ($options['method'] == "POST")
				$options['header'] .= "Content-Type: application/json; charset=utf-8\r\n";

			if(count($body) > 0)
			{
				$options['content'] = json_encode($body);
				$options['timeout'] = 15;
			}

			$options = array('http' => $options);
			$context = stream_context_create($options);
			$result = json_decode(file_get_contents($url, false, $context));

			return $result;
		}

		/**
		 * Return array of plug hubs on your account
		 * including their connected devices.
		 *
		 * @return array $result
		 */
		public function GetPlugs()
		{
			try
			{
				return $this->Request(
					self::API_PLUG . "plugs",
					"GET",
					array('header'  => "X-Session-Token: " . $this->session . "\r\n")
				);
			}
			catch (Exception $e)
			{
				return false;
			}
		}

		/**
		 * Perform action on device.
		 *
		 * @param string $plug_id
		 * @param string $device_id
		 * @param string $action "On|Off"
		 * 
		 * @return array $result 
		 */
		public function Action(string $plug_id, string $device_id, string $action)
		{
			try
			{
				return $this->Request(
					self::API_PLUG . sprintf("plugs/%s/devices/%s/action", $plug_id, $device_id),
					"POST",
					array("header"  => "X-Session-Token: " . $this->session . "\r\n"),
					array("action" => $action)
				);
			}
			catch (Exception $e)
			{
				return false;
			}
		}
	}
?>