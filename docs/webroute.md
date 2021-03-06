WebRoute Class:  'support/webroute.php'
=======================================

This class provides client-side routines to initiate a connection with another WebRoute enabled client via a WebRoute server (RFC ????).

Example usage:

```php
<?php
	require_once "support/webroute.php";

	$wr = new WebRoute();

	// The ID sent by two clients should be a string generated by a CSPRNG.
	// This is static so that the test will succeed.
	$id = "6fa1a1490ee0fe617b36530054e984786897e022";

	// Connect to the WebRoute server with the static ID.
	$result = $wr->Connect("wr://localhost:47735/test?accesskey=123456789101112", $id);
	if (!$result["success"])
	{
		var_dump($result);
		exit();
	}

	// At this point, the socket is simply a TCP/IP passthrough.
	$fp = $result["fp"];

	fwrite($fp, "CUSTOM PROTOCOL HERE\n");
	echo fgets($fp);
	sleep(1);

	fwrite($fp, "QUIT\n");
	echo fgets($fp);
	sleep(1);

	fclose($fp);
?>
```

WebRoute::ProcessState($state)
------------------------------

Access:  public

Parameters:

* $state - A valid WebRoute state object.

Returns:  A standard array of information.

This internal-ish function runs the core state engine behind the scenes against the input state.  This is the primary workhorse of the WebRoute class.

WebRoute::Connect($url, $id = false, $options = array(), $web = false)
----------------------------------------------------------------------

Access:  public

Parameters:

* $url - A string containing a WebRoute URL (starts with wr:// or wrs://).
* $id - A boolean of false to generate an ID via the CubicleSoft CSPRNG class or a string containing an ID generated with a CSPRNG (Default is false).
* $options - An array of valid WebBrowser class options (Default is array()).
* $web - A valid WebBrowser class instance (Default is false, which means one will be created).

Returns:  An array containing the results of the call.

This function initiates a connection to a WebRoute server via the WebBrowser class.  If you set up your own WebBrowser class (e.g. to handle cookies), pass it in as the $web parameter to use your class instance for the connection.

WebRoute::ConnectAsync($helper, $key, $callback, $url, $id = false, $options = array(), $web = false)
-----------------------------------------------------------------------------------------------------

Access:  public

Parameters:

* $helper - A MultiAsyncHelper instance.
* $key - A string containing a key to uniquely identify this WebBrowser instance.
* $callback - An optional callback function to receive regular status updates on the request (specify NULL if not needed).  The callback function must accept three parameters - callback($key, $url, $result).
* $url - A string containing a WebRoute URL (starts with wr:// or wrs://).
* $id - A boolean of false to generate an ID via the CubicleSoft CSPRNG class or a string containing an ID generated with a CSPRNG (Default is false).
* $options - An array of valid WebBrowser class options (Default is array()).
* $web - A valid WebBrowser class instance (Default is false, which means one will be created).

Returns:  A standard array of information.

This function queues the request with the MultiAsyncHandler instance ($helper) for later async/non-blocking processing of the request.  Note that this function always succeeds since request failure can't be detected until after processing begins.

See MultiAsyncHelper for example usage.

WebRoute::ConnectAsync__Handler($mode, &$data, $key, &$info)
------------------------------------------------------------

Access:  _internal_ public

Parameters:

* $mode - A string representing the mode/state to process.
* $data - Mixed content the depends entirely on the $mode.
* $key - A string representing the key associated with an object.
* $info - The information associated with the key.

Returns:  Nothing.

This internal static callback function is the internal handler for MultiAsyncHandler for processing WebRoute class instances.

WebRoute::WRTranslate($format, ...)
-----------------------------------

Access:  _internal_ static

Parameters:

* $format - A string containing valid sprintf() format specifiers.

Returns:  A string containing a translation.

This internal static function takes input strings and translates them from English to some other language if CS_TRANSLATE_FUNC is defined to be a valid PHP function name.
