<?php
namespace Upscale\Cisco\Meraki;

/**
 * Client for the REST API of the Cisco Meraki dashboard
 */
class ApiClient
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $apiBaseUrl;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var float Maximum permitted number of requests per second
     */
    protected $callRateLimit = 0;

    /**
     * @var float Timestamp with microseconds of the last API call
     */
    protected $callTimeLast = 0;

    /**
     * Inject dependencies
     *
     * @param \GuzzleHttp\ClientInterface $httpClient
     * @param string $apiKey Secret access key
     * @param string $apiBaseUrl Base URL of all API endpoints
     * @param int $callRateLimit Rate limit, req/sec
     */
    public function __construct(
        \GuzzleHttp\ClientInterface $httpClient,
        $apiKey,
        $apiBaseUrl = 'https://api.meraki.com/api/v0/',
        $callRateLimit = 5
    ) {
        $this->httpClient = $httpClient;
        $this->apiBaseUrl = $apiBaseUrl;
        $this->apiKey = $apiKey;
        $this->callRateLimit = $callRateLimit;
    }

    /**
     * Delay execution to satisfy the request rate limit
     */
    protected function limitCallRate()
    {
        if (!$this->callRateLimit) {
            return;
        }
        $now = microtime(true);
        $callIntervalActual = $now - $this->callTimeLast;
        $callIntervalNeeded = 1 / $this->callRateLimit;
        $callDelayNeeded = $callIntervalNeeded - $callIntervalActual;
        if ($callDelayNeeded > 0) {
            usleep($callDelayNeeded * 1000000); // Seconds to microseconds
        }
        $this->callTimeLast = $now;
    }

    /**
     * Call API endpoint and return its response decoded from the JSON format
     *
     * @param string $endpoint REST API endpoint path
     * @param string $method HTTP method
     * @param array $options HTTP client options
     * @return array
     * @throws \UnexpectedValueException
     */
    public function callApi($endpoint, $method = 'GET', array $options = [])
    {
        $this->limitCallRate();
        $path = "$this->apiBaseUrl/$endpoint";
        $options['headers']['X-Cisco-Meraki-API-Key'] = $this->apiKey;
        $res = $this->httpClient->request($method, $path, $options);
        if ($res->getStatusCode() != 200 || strpos($res->getHeaderLine('content-type'), 'application/json') !== 0) {
            throw new \UnexpectedValueException('Response is not in JSON format');
        }
        $json = json_decode($res->getBody(), true);
        return $json;
    }

    /**
     * Fetch the list of networks belonging to an organization
     *
     * @param string $organizationId
     * @return array
     */
    public function fetchNetworks($organizationId)
    {
        return $this->callApi("organizations/$organizationId/networks");
    }

    /**
     * Fetch the list of devices in a network
     *
     * @param string $networkId
     * @return array
     */
    public function fetchDevices($networkId)
    {
        return $this->callApi("networks/$networkId/devices");
    }

    /**
     * Fetch the list of clients connected to a device within a given time span
     *
     * @param string $deviceId
     * @param int $timeSpan Time span, sec
     * @return array
     */
    public function fetchClients($deviceId, $timeSpan = 86400)
    {
        return $this->callApi("devices/$deviceId/clients?timespan=$timeSpan");
    }
}
