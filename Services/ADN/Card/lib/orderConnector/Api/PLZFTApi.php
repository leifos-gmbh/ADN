<?php
/**
 * PLZFTApi
 * PHP version 7.4
 *
 * @category Class
 * @package  Plasticard\PLZFT
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * ADN/leifos - OpenAPI 3.0
 *
 * Definition of the REST API for ADN/leifos
 *
 * The version of the OpenAPI document: 0.1.0
 * Contact: developer@plasticard.de
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 6.3.0
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Plasticard\PLZFT\Api;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Plasticard\PLZFT\ApiException;
use Plasticard\PLZFT\Configuration;
use Plasticard\PLZFT\HeaderSelector;
use Plasticard\PLZFT\ObjectSerializer;

/**
 * PLZFTApi Class Doc Comment
 *
 * @category Class
 * @package  Plasticard\PLZFT
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class PLZFTApi
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var HeaderSelector
     */
    protected $headerSelector;

    /**
     * @var int Host index
     */
    protected $hostIndex;

    /** @var string[] $contentTypes **/
    public const contentTypes = [
        'addOrder' => [
            'application/xml',
        ],
    ];

/**
     * @param ClientInterface $client
     * @param Configuration   $config
     * @param HeaderSelector  $selector
     * @param int             $hostIndex (Optional) host index to select the list of hosts if defined in the OpenAPI spec
     */
    public function __construct(
        ClientInterface $client = null,
        Configuration $config = null,
        HeaderSelector $selector = null,
        $hostIndex = 0
    ) {
        $this->client = $client ?: new Client();
        $this->config = $config ?: new Configuration();
        $this->headerSelector = $selector ?: new HeaderSelector();
        $this->hostIndex = $hostIndex;
    }

    /**
     * Set the host index
     *
     * @param int $hostIndex Host index (required)
     */
    public function setHostIndex($hostIndex): void
    {
        $this->hostIndex = $hostIndex;
    }

    /**
     * Get the host index
     *
     * @return int Host index
     */
    public function getHostIndex()
    {
        return $this->hostIndex;
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Operation addOrder
     *
     * Send a new order
     *
     * @param  \Plasticard\PLZFT\Model\Order $order Create a new order (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['addOrder'] to see the possible values for this operation
     *
     * @throws \Plasticard\PLZFT\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return string|string|\Plasticard\PLZFT\Model\ApiResponse404|\Plasticard\PLZFT\Model\ApiResponse480|\Plasticard\PLZFT\Model\ApiResponse481|string
     */
    public function addOrder($order, string $contentType = self::contentTypes['addOrder'][0])
    {
        list($response) = $this->addOrderWithHttpInfo($order, $contentType);
        return $response;
    }

    /**
     * Operation addOrderWithHttpInfo
     *
     * Send a new order
     *
     * @param  \Plasticard\PLZFT\Model\Order $order Create a new order (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['addOrder'] to see the possible values for this operation
     *
     * @throws \Plasticard\PLZFT\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of string|string|\Plasticard\PLZFT\Model\ApiResponse404|\Plasticard\PLZFT\Model\ApiResponse480|\Plasticard\PLZFT\Model\ApiResponse481|string, HTTP status code, HTTP response headers (array of strings)
     */
    public function addOrderWithHttpInfo($order, string $contentType = self::contentTypes['addOrder'][0])
    {
        $request = $this->addOrderRequest($order, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            switch($statusCode) {
                case 200:
                    if ('string' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ('string' !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, 'string', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 401:
                    if ('string' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ('string' !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, 'string', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 404:
                    if ('\Plasticard\PLZFT\Model\ApiResponse404' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ('\Plasticard\PLZFT\Model\ApiResponse404' !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\Plasticard\PLZFT\Model\ApiResponse404', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 480:
                    if ('\Plasticard\PLZFT\Model\ApiResponse480' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ('\Plasticard\PLZFT\Model\ApiResponse480' !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\Plasticard\PLZFT\Model\ApiResponse480', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 481:
                    if ('\Plasticard\PLZFT\Model\ApiResponse481' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ('\Plasticard\PLZFT\Model\ApiResponse481' !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\Plasticard\PLZFT\Model\ApiResponse481', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 500:
                    if ('string' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ('string' !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, 'string', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
            }

            $returnType = 'string';
            if ($returnType === '\SplFileObject') {
                $content = $response->getBody(); //stream goes to serializer
            } else {
                $content = (string) $response->getBody();
                if ($returnType !== 'string') {
                    $content = json_decode($content);
                }
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        'string',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        'string',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Plasticard\PLZFT\Model\ApiResponse404',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 480:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Plasticard\PLZFT\Model\ApiResponse480',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 481:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Plasticard\PLZFT\Model\ApiResponse481',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        'string',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation addOrderAsync
     *
     * Send a new order
     *
     * @param  \Plasticard\PLZFT\Model\Order $order Create a new order (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['addOrder'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function addOrderAsync($order, string $contentType = self::contentTypes['addOrder'][0])
    {
        return $this->addOrderAsyncWithHttpInfo($order, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation addOrderAsyncWithHttpInfo
     *
     * Send a new order
     *
     * @param  \Plasticard\PLZFT\Model\Order $order Create a new order (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['addOrder'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function addOrderAsyncWithHttpInfo($order, string $contentType = self::contentTypes['addOrder'][0])
    {
        $returnType = 'string';
        $request = $this->addOrderRequest($order, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'addOrder'
     *
     * @param  \Plasticard\PLZFT\Model\Order $order Create a new order (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['addOrder'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function addOrderRequest($order, string $contentType = self::contentTypes['addOrder'][0])
    {

        // verify the required parameter 'order' is set
        if ($order === null || (is_array($order) && count($order) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $order when calling addOrder'
            );
        }


        $resourcePath = '/services/65/prod/v1';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;





        $headers = $this->headerSelector->selectHeaders(
            ['text/plain', 'application/xml', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($order)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                $httpBody = \GuzzleHttp\Utils::jsonEncode(ObjectSerializer::sanitizeForSerialization($order));
            } else {
                $httpBody = $order;
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                $httpBody = \GuzzleHttp\Utils::jsonEncode($formParams);
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires HTTP basic authentication
        if (!empty($this->config->getUsername()) || !(empty($this->config->getPassword()))) {
            $headers['Authorization'] = 'Basic ' . base64_encode($this->config->getUsername() . ":" . $this->config->getPassword());
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'POST',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Create http client option
     *
     * @throws \RuntimeException on file opening failure
     * @return array of http client options
     */
    protected function createHttpClientOption()
    {
        $options = [];
        if ($this->config->getDebug()) {
            $options[RequestOptions::DEBUG] = fopen($this->config->getDebugFile(), 'a');
            if (!$options[RequestOptions::DEBUG]) {
                throw new \RuntimeException('Failed to open the debug file: ' . $this->config->getDebugFile());
            }
        }

        return $options;
    }
}
