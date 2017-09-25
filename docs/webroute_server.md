WebRouteServer Class:  'support/webroute_server.php'
====================================================

This class is a working WebRoute server implementation in PHP.  It won't win any performance awards.  However, this class avoids the need to set up a formal web server or compile anything and then figure out how to proxy server Upgrade requests via that server.

Pairs nicely with the WebServer class for handing off Upgrade requests to the WebRouteServer class via the WebRouteServer::ProcessWebServerClientUpgrade() function.

For example basic usage, see:

https://github.com/cubiclesoft/webroute/blob/master/tests/test_webroute_server.php

WebRouteServer::Reset()
-----------------------

Access:  public

Parameters:  None.

Returns:  Nothing.

This function resets a class instance to the default state.  Note that WebRouteServer::Stop() should be called first as this simply resets all internal class variables.

WebRouteServer::SetMaxChunkSize($maxsize)
-----------------------------------------

Access:  public

Parameters:

* $maxsize - An integer representing the maximum chunk size, in bytes, to queue per client.

Returns:  Nothing.

This function sets the maximum chunk size for data passthrough to manage server RAM usage.  The default setting is 65,536 bytes.

WebRouteServer::SetDefaultTimeout($defaulttimeout)
--------------------------------------------------

Access:  public

Parameters:

* $defaulttimeout - An integer representing the default socket timeout, in seconds, per client.

Returns:  Nothing.

This function sets the default period of inactivity before closing the socket.  The default setting is 60 seconds.

WebRouteServer::Start($host, $port)
-----------------------------------

Access:  public

Parameters:

* $host - A string containing the host IP to bind to.
* $port - A port number to bind to.  On some systems, ports under 1024 are restricted to root/admin level access only.

Returns:  An array containing the results of the call.

This function starts a WebRoute server on the specified host and port.  The socket is also set to asynchronous (non-blocking) mode.  Some useful values for the host:

* `127.0.0.1` to bind to the localhost IPv4 interface.
* `0.0.0.0` to bind to all IPv4 interfaces.
* `[::1]` to bind to the localhost IPv6 interface.
* `[::0]` to bind to all IPv6 interfaces.

To select a new port number for a server, use the following link:

https://www.random.org/integers/?num=1&min=5001&max=49151&col=5&base=10&format=html&rnd=new

If it shows port 8080, just reload to get a different port number.

WebRouteServer::Stop()
----------------------

Access:  public

Parameters:  None.

Returns:  Nothing.

This function stops a WebRoute server after disconnecting all clients and resets some internal variables in case the class instance is reused.

WebRouteServer::GetStream()
---------------------------

Access:  public

Parameters:  None.

Returns:  The underlying socket stream (PHP resource) or a boolean of false if the server is not started.

This function is considered "dangerous" as it allows direct access to the underlying stream.  However, as long as it is only used with functions like PHP stream_select() and Wait() is used to do actual management, it should be safe enough.  This function is intended to be used where there are multiple handles being waited on (e.g. handling multiple connections to multiple WebRoute servers).

WebRouteServer::UpdateStreamsAndTimeout($prefix, &$timeout, &$readfps, &$writefps)
----------------------------------------------------------------------------------

Access:  public

Parameters:

* $prefix - A unique prefix to identify the various streams (server and client handles).
* $timeout - An integer reference containing the maximum number of seconds or a boolean of false.
* $readfps - An array reference to add streams wanting data to arrive.
* $writefps - An array reference to add streams wanting to send data.

Returns:  Nothing.

This function updates the timeout and read/write arrays with prefixed names so that a single stream_select() call can manage all sockets.

WebRouteServer::FixedStreamSelect(&$readfps, &$writefps, &$exceptfps, $timeout)
-------------------------------------------------------------------------------

Access:  public static

Parameters:  Same as stream_select() minus the microsecond parameter.

Returns:  A boolean of true on success, false on failure.

This function allows key-value pairs to work properly for the usual read, write, and except arrays.  PHP's stream_select() function is buggy and sometimes will return correct keys and other times not.  This function is called by Wait().  Directly calling this function is useful if multiple servers are running at a time (e.g. one public SSL server, one localhost non-SSL server).

WebRouteServer::Wait($timeout = false)
--------------------------------------

Access:  public

Parameters:

* $timeout - A boolean of false or an integer containing the number of seconds to wait for an event to trigger such as a write operation to complete (Default is false).

Returns:  An array containing the results of the call.

This function is the core of the WebRouteServer class and should be called frequently (e.g. a while loop).  It handles new connections, the initial conversation, linking clients together, passing data between clients, and timeouts.  The extra optional arrays to the call allow the function to wait on more than just sockets, which is useful when waiting on other asynchronous resources.

This function returns an array of clients that were responsive during the call.  It will also return clients that are no longer connected so that the application can have a chance to clean up resources associated with the client.

WebRouteServer::GetClients()
----------------------------

Access:  public

Parameters: None.

Returns:  An array of all of the active clients.

This function makes it easy to retrieve the entire list of clients currently connected to the server.  Note that this may include clients that are in the process of connecting and upgrading to the WebRoute protocol.

WebRouteServer::GetClient($id)
------------------------------

Access:  public

Parameters:

* $id - An integer containing the ID of the client to retrieve.

Returns:  An array containing client information associated with the ID if it exists, a boolean of false otherwise.

This function retrieves a single client array by its ID.

WebRouteServer::RemoveClient($id)
---------------------------------

Access:  public

Parameters:

* $id - An integer containing the ID of the client to retrieve.

Returns:  Nothing.

This function terminates a specified client by ID.  This is the correct way to disconnect a client.  Do not use fclose() directly on the socket handle.

WebRouteServer::ProcessWebServerClientUpgrade($webserver, $client, $linkexists = false)
---------------------------------------------------------------------------------------

Access:  public

Parameters:

* $webserver - An instance of the WebServer class.
* $client - An instance of WebServer_Client directly associated with the WebServer class.
* $linkexists - A boolean indicating that the upgrade should only happen if a waiting client exists (Default is false).

Returns:  An integer representing the new WebRouteServer client ID on success, false otherwise.

This function determines if the client is attempting to Upgrade to WebRoute.  If so, it detaches the client from the WebServer instance and associates a new client with the WebRouteServer instance.  Can optionally only perform the upgrade if another client exists that can be linked to.  Note that the WebRouteServer instance does not require WebRouteServer::Start() to have been called.

WebRouteServer::ProcessNewConnection($method, $path, $client)
-------------------------------------------------------------

Access:  protected

Parameters:

* $method - A string containing the HTTP method (supposed to be "GET").
* $path - A string containing the path portion of the request.
* $client - An object containing introductory information about the new client (parsed headers, etc).

Returns:  A string containing the HTTP response, if any, an empty string otherwise.

This function handles basic requirements of the WebRoute protocol and will reject obviously bad connections with the appropriate HTTP response string.  However, the function can be overridden in a derived class.

WebRouteServer::ProcessAcceptedConnection($method, $path, $client)
------------------------------------------------------------------

Access:  protected

Parameters:

* $method - A string containing the HTTP method (supposed to be "GET").
* $path - A string containing the path portion of the request.
* $client - An object containing nearly complete information about the new client (parsed headers, etc).

Returns:  A string containing additional HTTP headers to add to the response, if any, otherwise an empty string.

This function is called if the connection is being accepted.  That is, ProcessNewConnection() returned an empty string.  The default function does nothing but it can be overridden in a derived class to handle things such as custom protocols and extensions.

WebRouteServer::InitNewClient($fp)
----------------------------------

Access:  protected

Parameters:

* $fp - A stream resource or a boolean of false.

Returns:  The new stdClass instance.

This function creates a new client object.  Since there isn't anything terribly complex about the object, stdClass is used instead of something formal.

WebRouteServer::AcceptClient($client)
-------------------------------------

Access:  private

Parameters:

* $client - An object containing nearly complete information about the new client (parsed headers, etc).

Returns:  Nothing.

This function queues up the Upgrade response to send to each client upon linking two clients together.

WebRouteServer::ProcessInitialResponse($method, $path, $client)
---------------------------------------------------------------

Access:  private

Parameters:

* $method - A string containing the HTTP method (supposed to be "GET").
* $path - A string containing the path portion of the request.
* $client - An object containing nearly complete information about the new client (parsed headers, etc).

Returns:  Nothing.

This function performs a standard initial response to the client as to whether or not their request to Upgrade to the WebRoute protocol was successful and the client was linked to a matching client.

WebRouteServer::HeaderNameCleanup($name)
----------------------------------------

Access:  _internal_ static

Parameters:

* $name - A string containing a HTTP header name.

Returns:  A string containing a purified HTTP header name.

This internal static function cleans up a HTTP header name.

WebRouteServer::WRTranslate($format, ...)
-----------------------------------------

Access:  _internal_ static

Parameters:

* $format - A string containing valid sprintf() format specifiers.

Returns:  A string containing a translation.

This internal static function takes input strings and translates them from English to some other language if CS_TRANSLATE_FUNC is defined to be a valid PHP function name.
