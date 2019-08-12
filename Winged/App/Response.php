<?php

namespace Winged\App;

use \Winged\Http\HttpResponseHandler;

/**
 * Class Response
 *
 * @package Winged\App
 */
class Response extends HttpResponseHandler
{

    public $headers = [];

    protected $statusCodes = [
        200 => "Ok",
        201 => "Created",
        202 => "Accepted",
        203 => "Non-Authoritative Information",
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content",
        207 => "Multi-Status",
        226 => "IM Used",
        300 => "Multiple Choice",
        301 => "Moved Permanently",
        302 => "Found",
        303 => "See Other",
        304 => "Not Modified",
        305 => "Use Proxy",
        306 => "Unused",
        307 => "Temporary Redirect",
        308 => "Permanent Redirect",
        400 => "Bad Request",
        401 => "Unauthorized",
        402 => "Payment Required",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        407 => "Proxy Authentication Required",
        408 => "Request Timeout",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        412 => "Precondition Failed",
        413 => "Payload Too Large",
        414 => "URI Too Long",
        415 => "Unsupported Media Type",
        416 => "Requested Range Not Satisfiable",
        417 => "Expectation Failed",
        418 => "I'm a teapot",
        421 => "Misdirected Request",
        422 => "Unprocessable Entity",
        423 => "Locked",
        424 => "Failed Dependency",
        426 => "Upgrade Required",
        428 => "Precondition Required",
        429 => "Too Many Requests",
        431 => "Request Header Fields Too Large",
        451 => "Unavailable For Legal Reasons",
        500 => "Internal Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Service Unavailable",
        504 => "Gateway Timeout",
        505 => "HTTP Version Not Supported",
        506 => "Variant Also Negotiates",
        507 => "Insufficient Storage",
        508 => "Loop Detected",
        510 => "Not Extended",
        511 => "Network Authentication Required",
    ];

    protected $finalStatusCode = 200;

    /**
     * @var null | Request
     */
    public $request = null;

    /**
     * Response constructor.
     *
     * @param null  $request
     * @param array $headers
     */
    public function __construct(&$request = null, $headers = [])
    {
        $this->headers = $headers;
        if (is_object($request) && get_class($request) === 'Winged\App\Resquest') {
            $this->request = $request;
        } else {
            $this->request = App::getRequest();
        }
    }

    /**
     * @param string $headerKey
     * @param string $headerValue
     *
     * @return $this
     */
    public function setHeader($headerKey = '', $headerValue = '')
    {
        if (is_string($headerKey) && is_string($headerValue)) {
            $this->headers[$headerKey] = $headerValue;
        }
        return $this;
    }

    /**
     * @param $response
     * @param $error
     *
     * @return $this
     */
    public function dispatch($response = '', $error = false)
    {
        if ($this->request) {
            $this->setHeaderStatusCode();
            if ($this->request->getAcceptablePriority() === 'text/html' && !$error) {
                if (!is_scalar($response)) {
                    header_remove();
                    $this->setStatusCode(502);
                    $this->setHeaderStatusCode();
                    $this->dispatchJson([
                        'response' => 502,
                        'message' => 'NOTICE! The server tried to respond with valid HTML, but an internal error prevented it from doing so. As a fallback, we send this warning and content passed within {data}',
                        'content' => [
                            'data' => $response
                        ]
                    ]);
                } else {
                    $this->dispatchHtml($response);
                }
            } else {
                if ($this->request->isAcceptableType(['application/json', 'application/yaml', 'application/xml']) && !is_array($response)) {
                    $response = ['response' => $response];
                }
                switch ($this->request->getAcceptablePriority()) {
                    case 'application/json':
                        $this->dispatchJson($response);
                        break;
                    case 'application/xml':
                        $this->dispatchXml($response);
                        break;
                    case 'application/yaml':
                        $this->dispatchYaml($response);
                        break;
                    default:
                        $this->dispatchJson($response);
                        break;
                }
            }
            App::_exit();
        }
        return $this;
    }

    protected function setHeaderStatusCode()
    {
        $sapi_type = php_sapi_name();
        if (substr($sapi_type, 0, 3) == 'cgi') {
            header("Status: " . $this->getStatusCode() . " " . $this->statusCodes[$this->getStatusCode()]);
        } else {
            header("HTTP/1.1 " . $this->getStatusCode() . " " . $this->statusCodes[$this->getStatusCode()]);
        }
    }

    /**
     * @return $this
     */
    public function forceJson()
    {
        $this->request->accept = ['application/json' => 'application/json'];
        return $this;
    }

    /**
     * @return $this
     */
    public function forceHtml()
    {
        $this->request->accept = ['text/html' => 'application/json'];
        return $this;
    }

    /**
     * @return $this
     */
    public function forceXml()
    {
        $this->request->accept = ['application/json' => 'application/json'];
        return $this;
    }

    /**
     * @return $this
     */
    public function forceYaml()
    {
        $this->request->accept = ['application/json' => 'application/json'];
        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->finalStatusCode;
    }

    /**
     * @param $statusCode
     *
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        if (is_int($statusCode)) {
            if (array_key_exists($statusCode, $this->statusCodes)) {
                $this->finalStatusCode = $statusCode;
            }
        }
        return $this;
    }

    /**
     * @return Response
     */
    public function headerRemoveAll()
    {
        $this->headers = [];
        return $this->setStatusCode(200);
    }

    /**
     * @param string $headerKey
     *
     * @return $this
     */
    public function headerRemove($headerKey = '')
    {
        if ($headerKey != '') {
            if (array_key_exists($headerKey, $this->headers)) {
                unset($this->headers[$headerKey]);
            }
        }
        return $this;
    }

    /**
     * @param $statusCode
     * @param $url
     *
     * @return $this
     */
    public function respondWithRedirect($statusCode, $url)
    {
        if (is_string($url)) {
            $this->headerRemoveAll();
            $this->setStatusCode($statusCode);
            $this->setHeader('Location', $url);
        }
        return $this;
    }

}