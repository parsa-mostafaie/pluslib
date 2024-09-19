<?php

namespace pluslib\HTTP;

use JsonSerializable;
use Stringable;

class Response
{

  /**
   * Resets all headers (including status code) and body.
   *
   * @return self
   */

  public function reset(): self
  {

    $this->status_code = 200;
    $this->headers = [];
    $this->body = '';

    return $this;

  }

  /**
   * Status Code
   * @var int
   */
  protected int $status_code = 200;

  /*
   * See: https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
   *
   * 1xx: Informational - Request received, continuing process
   * 2xx: Success - The action was successfully received, understood, and accepted
   * 3xx: Redirection - Further action must be taken in order to complete the request
   * 4xx: Client Error - The request contains bad syntax or cannot be fulfilled
   * 5xx: Server Error - The server failed to fulfill an apparently valid request
   */

  const valid_status_codes = [
    100 => 'Continue',
    101 => 'Switching Protocols',
    102 => 'Processing',
    103 => 'Early Hints',
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Information',
    204 => 'No Content',
    205 => 'Partial Content',
    207 => 'Multi-Status',
    208 => 'Already Reported',
    218 => 'This is fine',
    226 => 'IM Used',
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    305 => 'Use Proxy',
    306 => 'Switch Proxy',
    307 => 'Temporary Redirect',
    308 => 'Permanent Redirect',
    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Timeout',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Payload Too Large',
    414 => 'URI Too Long',
    415 => 'Unsupported Media Type',
    416 => 'Range Not Satisfiable',
    417 => 'Expectation Failed',
    418 => 'I\'m a teapot',
    419 => 'Page Expired',
    420 => 'Enhance Your Calm',
    421 => 'Misdirected Request',
    422 => 'Unprocessable Entity',
    423 => 'Locked',
    424 => 'Failed Dependency',
    425 => 'Too Early',
    426 => 'Upgrade Required',
    428 => 'Precondition Required',
    429 => 'Too Many Requests',
    430 => 'Request Header Fields Too Large',
    431 => 'Request Header Fields Too Large',
    440 => 'Login Time-out',
    444 => 'No Response',
    449 => 'Retry With',
    450 => 'Blocked by Windows Parental Controls',
    451 => 'Unavailable For Legal Reasons',
    460 => 'Client Closed the Connection', // Verbiage mine
    463 => 'Too Many X-Forwarded-For Addresses', // Verbiage mine
    494 => 'Request header too large',
    495 => 'SSL Certificate Error',
    496 => 'SSL Certificate Required',
    497 => 'HTTP Request Sent to HTTPS Port',
    498 => 'Invalid Token',
    499 => 'Client Closed Request',
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'HTTP Version Not Supported',
    506 => 'Variant Also Negotiates',
    507 => 'Insufficient Storage',
    508 => 'Loop Detected',
    509 => 'Bandwidth Limit Exceeded',
    510 => 'Not Extended',
    511 => 'Network Authentication Required',
    520 => 'Web Server Returned an Unknown Error',
    521 => 'Web Server Is Down',
    522 => 'Connection Timed Out',
    523 => 'Origin Is Unreachable',
    524 => 'A Timeout Occurred',
    525 => 'SSL Handshake Failed',
    526 => 'Invalid SSL Certificate',
    527 => 'Railgun Error',
    529 => 'Site is overloaded',
    530 => 'Site is frozen',
    561 => 'Unauthorized',
    598 => 'Network read timeout error'
  ];

  /**
   * Checks if a given status code is valid.
   *
   * @param $status int
   *
   * @return bool
   */

  protected function _isValidStatusCode(int $status): bool
  {
    return isset(static::valid_status_codes[$status]);
  }

  /**
   * Sets status code to be sent with response.
   *
   * @param int $code
   *
   * @return self
   *
   * @throws InvalidStatusCodeException
   */

  public function setStatusCode(int $code): self
  {
    if ($this->_isValidStatusCode($code)) {
      $this->status_code = $code;

      return $this;
    }

    throw new InvalidStatusCodeException('Unable to set status code: invalid status code');
  }

  /**
   * Returns the status code and associated phrase to be sent with response.
   *
   * @return array
   */

  public function getStatusCode(): array
  {
    return [
      'code' => $this->status_code,
      'phrase' => static::valid_status_codes[$this->status_code]
    ];
  }

  /**
   * Headers
   */

  protected array $remove_headers = [];

  /**
   * Sets header values to be removed with the response.
   *
   * @param array $headers
   *
   * @return self
   */

  public function removeHeaders(array $headers): self
  {
    foreach ($headers as $header) {
      $this->remove_headers[] = $header;
    }

    return $this;
  }

  /**
   * Sets header values to be removed with the response.
   *
   * @param $header
   *
   * @return self
   */

  public function removeHeader($header): self
  {
    $this->remove_headers[] = $header;

    return $this;
  }

  protected array $headers = [];

  /**
   * Sets header values to be sent with the response.
   *
   * @param $headers array
   *
   * @return self
   */

  public function setHeaders(array $headers): self
  {
    foreach ($headers as $header => $value) {
      $this->headers[$header] = $value;
    }

    return $this;

  }

  /**
   * Sets header values to be sent with the response.
   *
   * @param $header
   * @param $value
   *
   * @return self
   */

  public function setHeader($header, $value): self
  {
    $this->headers[$header] = $value;

    return $this;

  }

  /**
   * Returns array of headers to be sent with the response.
   *
   * @return array
   */

  public function getHeaders(): array
  {
    return $this->headers;
  }

  /*
   * ############################################################
   * Body
   * ############################################################
   */

  protected string $body = '';

  /**
   * Sets body to be sent with the response.
   *
   * @param $body string
   *
   * @return self
   */

  public function setBody(string $body): self
  {
    $this->body = $body;

    return $this;
  }

  /**
   * Returns body to be sent with the response, or null if body has not been set.
   *
   * @return string
   */

  public function getBody(): string
  {
    return $this->body;
  }

  /*
   * Response
   */

  /**
   * Sends response.
   *
   * @return void
   */

  public function send(): void
  {
    // -------------------- Status code --------------------
    $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';

    $status = $this->getStatusCode();

    header(trim($protocol . ' ' . $status['code'] . ' ' . $status['phrase']));

    // -------------------- Headers --------------------
    // Remove
    foreach ($this->remove_headers as $remove) {
      header_remove($remove);
    }
    // Set
    foreach ($this->getHeaders() as $k => $v) {
      header($k . ': ' . $v);
    }

    // -------------------- Body --------------------
    if ($this->getBody() != '') {
      echo $this->getBody();
    }

    // -------------------- Reset --------------------
    $this->reset();
  }

  /**
   * Sets Content-Type as application/json, and converts the given array to the JSON encoded body.
   *
   * @param JsonSerializable $jsonable
   *
   * @return static
   */
  public function json($jsonable)
  {
    return $this->setHeaders([
      'Content-Type' => 'application/json'
    ])->setBody(json_encode($jsonable));
  }

  /**
   * Sets Content-Type as application/json, and converts the given array to the JSON encoded body.
   *
   * @param JsonSerializable $jsonable
   *
   * @return void
   */

  public function sendJson($jsonable): void
  {
    $this->json($jsonable)->send();
  }

  /**
   * Is given status code valid a valid redirect.
   *
   * @param $status int
   *
   * @return bool
   */

  protected function _isValidRedirect(int $status): bool
  {
    return $status >= 300 && $status < 400;
  }

  /**
   * Redirects to a given URL using a given status code.
   *
   * @param string $url
   * @param int $status (HTTP status code to return)
   *
   * @return static
   *
   * @throws InvalidStatusCodeException
   */

  public function redirect(string $url, int $status = 302): static
  {
    if (!$this->_isValidRedirect($status)) {
      throw new InvalidStatusCodeException('Unable to redirect: invalid status code');
    }

    $this->reset(); // Reset anything that may be set

    return $this->setHeaders([
      'Location' => $url
    ])->status($status);
  }

  public static function from($value)
  {
    if ($value instanceof static) {
      return $value;
    } else if (is_string($value) || $value instanceof Stringable) {
      return response()->setBody((string) $value);
    } else if (isJsonConvertible($value) && (!is_object($value) || $value instanceof JsonSerializable)) {
      return response()->json($value);
    }

    return response();
  }

  public function status($status_code)
  {
    return $this->setStatusCode($status_code);
  }
}