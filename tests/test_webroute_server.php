<?php
	// This is strictly a test of the WebRouteServer class which only implements WebRoute.
	// For a more complete end-user experience, use the approach found in 'test_web_server.php' in the Ultimate Web Scraper Toolkit.

	// Temporary root.
	$rootpath = str_replace("\\", "/", dirname(__FILE__));
	require_once $rootpath . "/../support/webroute_server.php";
	require_once $rootpath . "/../support/http.php";

	$wrserver = new WebRouteServer();

	echo "Starting server...\n";
	$result = $wrserver->Start("127.0.0.1", "47735");
	if (!$result["success"])
	{
		var_dump($result);
		exit();
	}

	echo "Ready.\n";

	$tracker = array();

	do
	{
		$result = $wrserver->Wait();

		// Do something with active clients.
		foreach ($result["clients"] as $id => $client)
		{
			if (!isset($tracker[$id]))
			{
				echo "Client ID " . $id . " connected.\n";

				// Example of checking for an access key.
				$url = HTTP::ExtractURL($client->url);
				if (!isset($url["queryvars"]["accesskey"]) || $url["queryvars"]["accesskey"][0] !== "123456789101112")
				{
					$wrserver->RemoveClient($id);

					continue;
				}

				echo "Valid access key used.\n";

				$tracker[$id] = array();
			}
		}

		// Do something with removed clients.
		foreach ($result["removed"] as $id => $result2)
		{
			if (isset($tracker[$id]))
			{
				echo "Client ID " . $id . " disconnected.\n";

//				echo "Client ID " . $id . " disconnected.  Reason:\n";
//				var_dump($result2["result"]);
//				echo "\n";

				unset($tracker[$id]);
			}
		}
	} while (1);
?>