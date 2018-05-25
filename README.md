Client for Cisco Meraki Dashboard API
=====================================

This library is a simplistic PHP client for [Cisco Meraki Dashboard API](https://dashboard.meraki.com/api_docs).

**Features:**
- Authorization via API Key
- Base URL of API endpoints
- Parsing JSON responses
- Following HTTP redirects
- Rate limiting (default: 5 req/sec)

## Installation

The library is to be installed via [Composer](https://getcomposer.org/) as a project dependency in `composer.json`:
```yaml
{
    "require": {
        "upscale/cisco-meraki-client": "*"
    }
}
```

## Basic Usage

The library implements a handful of shortcut API methods:
```php
$meraki = new \Upscale\Cisco\Meraki\ApiClient(
    new \GuzzleHttp\Client(),
    '0011223344556677889900aaabbbcccdddeeefff'
);

$organizationId = 123456;
$networks = $meraki->fetchNetworks($organizationId);

$networkId = $networks[0]['id'];
$devices = $meraki->fetchDevices($networkId);

$deviceId = $devices[0]['serial'];
$clients = $meraki->fetchClients($deviceId);;

var_export($networks);
var_export($devices);
var_export($clients);
```

## Advanced Usage

Arbitrary [REST API endpoints](https://dashboard.meraki.com/api_docs) can be called via the generic interface:
```php
$organizations = $meraki->callApi('organizations', 'GET');
```

The arguments are passed through to [Guzzle HTTP Client](http://guzzlephp.org/) providing full control over API requests.

## Contributing

Pull Requests are welcome to introduce the missing shortcut methods!

## License

Licensed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0).
