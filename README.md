WebRoute
========

This is the official reference implementation of the WebRoute Internet protocol.

The WebRoute protocol links two TCP/IP endpoints together via shared unique IDs and then transitions linked connections into a simple TCP/IP passthrough.  A WebRoute server is intended to be deployed as a reverse proxy behind a standard web server (e.g. Apache or Nginx).  The method by which clients initiate a connection is not dissimilar to WebSocket Upgrade but without all of the framing baggage that WebSocket brings to the table.

[![Donate](https://cubiclesoft.com/res/donate-shield.png)](https://cubiclesoft.com/donate/) [![Discord](https://img.shields.io/discord/777282089980526602?label=chat&logo=discord)](https://cubiclesoft.com/product-support/github/)

Features
--------

* Connects two clients together via unique ID regardless of how each client's firewall is setup.
* Uses ubiquitous web server technology to initiate a TCP/IP passthrough.
* The protocol is designed to be easily integrated into existing software products that already support WebSocket (e.g. web browsers) by following a similar pattern.
* The reference implementation has a liberal open source license.  MIT or LGPL, your choice.
* Designed for relatively painless integration into your environment.
* Sits on GitHub for all of that pull request and issue tracker goodness to easily submit changes and ideas respectively.

Test Suite
----------

Included with this repository are a test server and client, written in PHP.  To run the test suite, follow these simple steps using separate terminal/console/command line windows:

* Start the server by running `php tests/test_webroute_server.php`.  This starts a basic WebRoute server that is designed for protocol demonstration purposes only.
* Start the first client by running `php tests/test_webroute_client.php`.  This connects to the WebRoute server with a static ID and enters a waiting state.
* Start the second client by running `php tests/test_webroute_client.php`.  This connects to the WebRoute server with the same static ID as the first client.

When the second client connects, the WebRoute server sees that there is a waiting client with the same ID as the second client (the first client).  At this point, the server links the two clients together and data is sent, received, and echoed out to the display using a custom protocol (i.e. no longer HTTP).

The WebRoute Protocol
---------------------

The WebRoute protocol itself is licensed under Creative Commons Zero (CC0).  There's not much to it anyway.

The WebRoute protocol follows an Upgrade approach that is very similar to WebSocket.  Two clients connect into a WebRoute server and make a request that looks like:

```
GET /test?accesskey=123456789101112 HTTP/1.1
Host: 127.0.0.1:47735
Connection: keep-alive, Upgrade
WebRoute-Version: 1
WebRoute-ID: 6fa1a1490ee0fe617b36530054e984786897e022
WebRoute-Timeout: 60
Upgrade: webroute

```

Upon accepting the upgrade request, usually only after a "link" is established between both clients (e.g. updating pointers in internal structures), the server responds to each client with:

```
HTTP/1.1 101 Switching Protocols
Upgrade: webroute
Connection: Upgrade
Sec-WebRoute-Accept: FgtwdI1ph0NTOVQts+nD5u0AcXs=

```

The value of `Sec-WebRoute-Accept` is calculated as `base64-encode(sha1(concatenate(client-WebRoute-ID, "BE7204BD-47E6-49EE-9B0D-016E370644B2"))))`.

Upon upgrading to WebRoute, any data sent to the server is simply passed through to the other client that was associated by the same `WebRoute-ID`.

Some additional details about the various headers sent by each client:

* WebRoute-Version - An integer representing the version of the protocol.  Currently must be `1`.
* WebRoute-ID - A unique, hard to guess ID, preferably generated with a CSPRNG.  Required.  Additional security measures should be taken (e.g. options in the URL or a custom HTTP header).  Like WebSocket, relying solely on HTTP cookies is not recommended as they can used in cross-site request forgery (CSRF/XSRF) attacks.
* WebRoute-Timeout - An optional integer representing the number of seconds to wait before timing out the connection AFTER the WebRoute request has been accepted by the server.  This is a suggestion to the WebRoute server as to how long to keep the connection alive when no data has been sent or received from either side.  A WebRoute server should not accept timeout values less than its own minimum timeout but should otherwise honor the smallest timeout value sent by both clients.  For example, if the server timeout is 60 seconds and two clients send 45 and 65, the chosen timeout for both clients should be 60.  Another example is if the server timeout is 60 seconds and two clients send 80 and 120, the chosen timeout for both clients should be 80.

It is recommended that 'wr://' and 'wrs://' (SSL) be used to recognize WebRoute URIs.  In addition, it is possible to chain URIs together to build a chain of TCP/IP passthroughs via WebRoute.  This implementation uses the space ' ' character to separate URIs and will return the first URI that does not begin with 'wr://' or 'wrs://'.

Note that neither the protocol nor the recommended protocol schemes have been registered with the IETF.  If someone wants to contribute toward a Draft/Standard, you are certainly welcome to commit valuable time toward such a goal.  I looked at the lengthy, involved process and decided that it wasn't worth my time.

Security Notes
--------------

The WebRoute protocol is only as secure as the unique identifiers in use.  A PRNG or a weak CSPRNG could result in insecure, guessable IDs being generated and therefore linking two clients together that should not be linked.  Additional security measures should be taken when using upgrade protocols like WebRoute.

The WebRoute protocol can be utilized to completely bypass most common firewall setups in ways that are very unusual and difficult to detect.  See [Remoted API Server](https://github.com/cubiclesoft/remoted-api-server) for such an implementation.  Like most of the sage advice regarding network-enabled software that can be used on the Internet:  Caution is advised when deploying WebRoute to production systems.  Know what you are doing when it comes to using upgrade protocols like WebRoute or it'll come back to haunt you.
