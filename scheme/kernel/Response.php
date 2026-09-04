<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @since Version 4
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/**
 * ------------------------------------------------------
 *  Class Response
 * ------------------------------------------------------
 *
 * Handles HTTP responses, status codes, headers, content types,
 * and provides convenient methods for common response formats.
 */
class Response
{
    /**
     * HTTP Status Code
     *
     * @var int
     */
    private $status_code = 200;

    /**
     * Response Headers
     *
     * @var array
     */
    private $headers = array();

    /**
     * Response Content
     *
     * @var mixed
     */
    private $content = NULL;

    /**
     * Cookies to set
     *
     * @var array
     */
    private $cookies = array();

    /**
     * Whether headers have been sent
     *
     * @var boolean
     */
    private $headers_sent = FALSE;

    /**
     * HTTP status code text map
     *
     * @var array
     */
    private static $status_texts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        422 => 'Unprocessable Entity',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
    );

    /**
     * Class constructor
     *
     * @param mixed $content
     * @param int   $status_code
     */
    public function __construct($content = NULL, $status_code = 200)
    {
        $this->content     = $content;
        $this->status_code = $status_code;
    }

    // ---------------------------------------------------------------
    // STATUS CODE
    // ---------------------------------------------------------------

    /**
     * Set HTTP Status Code
     *
     * @param  int $status_code
     * @return $this
     */
    public function set_status_code($status_code)
    {
        $this->status_code = (int) $status_code;
        return $this;
    }

    /**
     * Get current HTTP status code
     *
     * @return int
     */
    public function get_status_code()
    {
        return $this->status_code;
    }

    /**
     * Get the status text for the current (or given) status code
     *
     * @param  int|null $code  Defaults to the current status code
     * @return string
     */
    public function get_status_text($code = NULL)
    {
        $code = $code ?: $this->status_code;
        return isset(self::$status_texts[$code]) ? self::$status_texts[$code] : 'Unknown';
    }

    /**
     * Check if the response status code is informational (1xx)
     *
     * @return boolean
     */
    public function is_informational()
    {
        return $this->status_code >= 100 && $this->status_code < 200;
    }

    /**
     * Check if the response is successful (2xx)
     *
     * @return boolean
     */
    public function is_successful()
    {
        return $this->status_code >= 200 && $this->status_code < 300;
    }

    /**
     * Check if the response is a redirect (3xx)
     *
     * @return boolean
     */
    public function is_redirect()
    {
        return $this->status_code >= 300 && $this->status_code < 400;
    }

    /**
     * Check if the response is a client error (4xx)
     *
     * @return boolean
     */
    public function is_client_error()
    {
        return $this->status_code >= 400 && $this->status_code < 500;
    }

    /**
     * Check if the response is a server error (5xx)
     *
     * @return boolean
     */
    public function is_server_error()
    {
        return $this->status_code >= 500 && $this->status_code < 600;
    }

    /**
     * Check if the response is any kind of error (4xx or 5xx)
     *
     * @return boolean
     */
    public function is_error()
    {
        return $this->status_code >= 400;
    }

    /**
     * Check if the response has a specific status code
     *
     * @param  int $code
     * @return boolean
     */
    public function is_status($code)
    {
        return $this->status_code === (int) $code;
    }

    // ---------------------------------------------------------------
    // HEADERS
    // ---------------------------------------------------------------

    /**
     * Add Response Header(s)
     * Accepts a single name/value pair or an associative array of headers.
     *
     * @param  string|array $name
     * @param  string       $value
     * @return $this
     */
    public function add_header($name, $value = '')
    {
        if (is_array($name))
        {
            foreach ($name as $key => $val)
            {
                $this->headers[$key] = $val;
            }
        }
        else
        {
            $this->headers[$name] = $value;
        }

        return $this;
    }

    /**
     * Remove a response header by name
     *
     * @param  string $name
     * @return $this
     */
    public function remove_header($name)
    {
        if (isset($this->headers[$name]))
        {
            unset($this->headers[$name]);
        }

        return $this;
    }

    /**
     * Check if a response header has been set
     *
     * @param  string $name
     * @return boolean
     */
    public function has_header($name)
    {
        return isset($this->headers[$name]);
    }

    /**
     * Get a single response header value
     *
     * @param  string $name
     * @return string|null
     */
    public function get_header($name)
    {
        return isset($this->headers[$name]) ? $this->headers[$name] : NULL;
    }

    /**
     * Get all response headers
     *
     * @return array
     */
    public function get_headers()
    {
        return $this->headers;
    }

    /**
     * Set the Content-Type header
     *
     * @param  string $mime     MIME type (e.g. 'application/json')
     * @param  string $charset  Character set (default: utf-8)
     * @return $this
     */
    public function content_type($mime, $charset = 'utf-8')
    {
        $value = $charset ? $mime . '; charset=' . $charset : $mime;
        return $this->add_header('Content-Type', $value);
    }

    /**
     * Set cache control headers
     * Pass 0 or a negative number to disable caching entirely.
     *
     * @param  int    $seconds  Max age in seconds. 0 = no-cache.
     * @param  string $visibility  'public' or 'private' (default: 'public')
     * @return $this
     */
    public function cache($seconds = 3600, $visibility = 'public')
    {
        if ($seconds <= 0)
        {
            $this->add_header('Cache-Control', 'no-store, no-cache, must-revalidate');
            $this->add_header('Pragma', 'no-cache');
            $this->add_header('Expires', '0');
        }
        else
        {
            $this->add_header('Cache-Control', $visibility . ', max-age=' . $seconds);
            $this->add_header('Expires', gmdate('D, d M Y H:i:s', time() + $seconds) . ' GMT');
        }

        return $this;
    }

    /**
     * Disable all caching on this response
     *
     * @return $this
     */
    public function no_cache()
    {
        return $this->cache(0);
    }

    /**
     * Add common security headers (HSTS, X-Frame-Options, etc.)
     *
     * @param  array $overrides  Override specific headers
     * @return $this
     */
    public function with_security_headers($overrides = array())
    {
        $defaults = array(
            'X-Content-Type-Options'    => 'nosniff',
            'X-Frame-Options'           => 'SAMEORIGIN',
            'X-XSS-Protection'          => '1; mode=block',
            'Referrer-Policy'           => 'strict-origin-when-cross-origin',
            'Permissions-Policy'        => 'geolocation=(), microphone=()',
        );

        return $this->add_header(array_merge($defaults, $overrides));
    }

    /**
     * Add CORS headers to the response
     *
     * @param  string $origin   Allowed origin (default: *)
     * @param  string $methods  Allowed methods
     * @param  string $headers  Allowed headers
     * @param  bool   $credentials  Allow credentials
     * @return $this
     */
    public function with_cors($origin = '*', $methods = 'GET, POST, PUT, PATCH, DELETE, OPTIONS', $headers = 'Content-Type, Authorization', $credentials = FALSE)
    {
        $this->add_header('Access-Control-Allow-Origin', $origin);
        $this->add_header('Access-Control-Allow-Methods', $methods);
        $this->add_header('Access-Control-Allow-Headers', $headers);

        if ($credentials)
        {
            $this->add_header('Access-Control-Allow-Credentials', 'true');
        }

        return $this;
    }

    // ---------------------------------------------------------------
    // CONTENT
    // ---------------------------------------------------------------

    /**
     * Set Response Content
     *
     * @param  mixed $content
     * @return $this
     */
    public function set_content($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get response content
     *
     * @return mixed
     */
    public function get_content()
    {
        return $this->content;
    }

    /**
     * Append content to the existing response body
     *
     * @param  mixed $content
     * @return $this
     */
    public function append_content($content)
    {
        $this->content .= $content;
        return $this;
    }

    /**
     * Set HTML Response Content
     * Automatically adds Content-Type: text/html header.
     *
     * @param  mixed $content
     * @return $this
     */
    public function set_html_content($content)
    {
        $this->content_type('text/html');
        $this->set_content($content);
        return $this;
    }

    /**
     * Set JSON Response Content
     * Automatically adds Content-Type: application/json header.
     *
     * @param  mixed $data
     * @param  int   $options  JSON encoding options (e.g. JSON_PRETTY_PRINT)
     * @return $this
     */
    public function set_json_content($data, $options = 0)
    {
        $this->content_type('application/json');
        $this->content = json_encode($data, $options);
        return $this;
    }

    /**
     * Set plain text Response Content
     * Automatically adds Content-Type: text/plain header.
     *
     * @param  string $content
     * @return $this
     */
    public function set_text_content($content)
    {
        $this->content_type('text/plain');
        $this->set_content($content);
        return $this;
    }

    /**
     * Set XML Response Content
     * Automatically adds Content-Type: application/xml header.
     *
     * @param  string $xml
     * @return $this
     */
    public function set_xml_content($xml)
    {
        $this->content_type('application/xml');
        $this->set_content($xml);
        return $this;
    }

    /**
     * Get the length of the current content body in bytes
     *
     * @return int
     */
    public function get_content_length()
    {
        return strlen((string) $this->content);
    }

    /**
     * Check if the response body is empty
     *
     * @return boolean
     */
    public function is_empty()
    {
        return $this->content === NULL || $this->content === '';
    }

    // ---------------------------------------------------------------
    // SEND
    // ---------------------------------------------------------------

    /**
     * Send headers only (no body)
     * Safe to call multiple times — headers are only sent once.
     *
     * @return void
     */
    public function send_headers()
    {
        if ($this->headers_sent)
        {
            return;
        }

        if ( ! headers_sent())
        {
            http_response_code($this->status_code);

            foreach ($this->headers as $name => $value)
            {
                header("$name: $value");
            }

            $this->send_cookies();
        }

        $this->headers_sent = TRUE;
    }

    /**
     * Send cookies
     *
     * @return void
     */
    private function send_cookies()
    {
        foreach ($this->cookies as $cookie)
        {
            setcookie(
                $cookie['name'],
                $cookie['value'],
                array(
                    'expires'  => $cookie['expiration'] > 0 ? time() + $cookie['expiration'] : 0,
                    'path'     => $cookie['path'],
                    'domain'   => $cookie['domain'],
                    'secure'   => $cookie['secure'],
                    'httponly' => $cookie['httponly'],
                    'samesite' => $cookie['samesite'],
                )
            );
        }

        $this->cookies = array();
    }

    /**
     * Send the response — flushes status code, all queued headers, and the content body
     *
     * @return void
     */
    public function send()
    {
        $this->send_headers();

        if ($this->content !== NULL)
        {
            echo $this->content;
        }
    }

    /**
     * Send JSON response immediately
     *
     * @param  mixed $data
     * @param  int   $status_code
     * @param  int   $options  JSON encoding options
     * @return void
     */
    public function send_json($data, $status_code = 200, $options = 0)
    {
        $this->set_status_code($status_code);
        $this->set_json_content($data, $options);
        $this->send();
    }

    /**
     * Send a structured JSON success response
     *
     * @param  mixed  $data
     * @param  string $message
     * @param  int    $status_code
     * @return void
     */
    public function send_json_success($data = NULL, $message = 'Success', $status_code = 200)
    {
        $response = array(
            'success' => TRUE,
            'message' => $message,
        );

        if ($data !== NULL)
        {
            $response['data'] = $data;
        }

        $this->send_json($response, $status_code);
    }

    /**
     * Send a structured JSON error response
     *
     * @param  string $message
     * @param  int    $status_code
     * @param  array  $additional_data  Extra fields merged into the payload
     * @return void
     */
    public function send_json_error($message, $status_code = 400, $additional_data = array())
    {
        $response = array_merge(array(
            'success' => FALSE,
            'error'   => $message,
        ), $additional_data);

        $this->send_json($response, $status_code);
    }

    /**
     * Send a structured JSON validation error response (422)
     *
     * @param  array  $errors   Associative array of field => message pairs
     * @param  string $message
     * @return void
     */
    public function send_json_validation($errors, $message = 'Validation failed')
    {
        $this->send_json_error($message, 422, array('errors' => $errors));
    }

    /**
     * Send HTML response
     *
     * @param  mixed $content
     * @param  int   $status_code
     * @return void
     */
    public function send_html($content, $status_code = 200)
    {
        $this->set_status_code($status_code);
        $this->set_html_content($content);
        $this->send();
    }

    /**
     * Send plain text response
     *
     * @param  string $content
     * @param  int    $status_code
     * @return void
     */
    public function send_text($content, $status_code = 200)
    {
        $this->set_status_code($status_code);
        $this->set_text_content($content);
        $this->send();
    }

    /**
     * Send XML response
     *
     * @param  string $xml
     * @param  int    $status_code
     * @return void
     */
    public function send_xml($xml, $status_code = 200)
    {
        $this->set_status_code($status_code);
        $this->set_xml_content($xml);
        $this->send();
    }

    /**
     * Send HTTP 204 No Content response
     *
     * @return void
     */
    public function send_no_content()
    {
        $this->set_status_code(204);
        $this->set_content(NULL);
        $this->send();
    }

    /**
     * Send HTTP 201 Created response with optional Location header
     *
     * @param  mixed       $data
     * @param  string|null $location  URI of the newly created resource
     * @return void
     */
    public function send_created($data = NULL, $location = NULL)
    {
        if ($location !== NULL)
        {
            $this->add_header('Location', $location);
        }

        $this->send_json_success($data, 'Created', 201);
    }

    /**
     * Send HTTP 401 Unauthorized response
     *
     * @param  string $message
     * @return void
     */
    public function send_unauthorized($message = 'Unauthorized')
    {
        $this->send_json_error($message, 401);
    }

    /**
     * Send HTTP 403 Forbidden response
     *
     * @param  string $message
     * @return void
     */
    public function send_forbidden($message = 'Forbidden')
    {
        $this->send_json_error($message, 403);
    }

    /**
     * Send HTTP 404 Not Found response
     *
     * @param  string $message
     * @return void
     */
    public function send_not_found($message = 'Not found')
    {
        $this->send_json_error($message, 404);
    }

    /**
     * Send HTTP 405 Method Not Allowed response
     *
     * @param  array  $allowed  List of allowed HTTP methods
     * @param  string $message
     * @return void
     */
    public function send_method_not_allowed($allowed = array(), $message = 'Method not allowed')
    {
        if ( ! empty($allowed))
        {
            $this->add_header('Allow', implode(', ', array_map('strtoupper', $allowed)));
        }

        $this->send_json_error($message, 405);
    }

    /**
     * Send HTTP 429 Too Many Requests response
     *
     * @param  string   $message
     * @param  int|null $retry_after  Seconds before the client may retry
     * @return void
     */
    public function send_too_many_requests($message = 'Too many requests', $retry_after = NULL)
    {
        if ($retry_after !== NULL)
        {
            $this->add_header('Retry-After', (int) $retry_after);
        }

        $this->send_json_error($message, 429);
    }

    /**
     * Send HTTP 500 Internal Server Error response
     *
     * @param  string $message
     * @return void
     */
    public function send_server_error($message = 'Internal server error')
    {
        $this->send_json_error($message, 500);
    }

    // ---------------------------------------------------------------
    // FILE & STREAMING
    // ---------------------------------------------------------------

    /**
     * Serve a file as a downloadable attachment
     *
     * @param  string      $filepath  Absolute path to the file
     * @param  string|null $filename  Custom download filename
     * @param  array       $headers   Additional headers
     * @return void
     */
    public function download($filepath, $filename = NULL, $headers = array())
    {
        if ( ! file_exists($filepath))
        {
            $this->set_status_code(404)->set_content('File not found')->send();
            return;
        }

        $filename = $filename ?: basename($filepath);
        $filesize = filesize($filepath);

        $this->set_status_code(200);
        $this->add_header('Content-Description', 'File Transfer');
        $this->add_header('Content-Type', mime_content_type($filepath));
        $this->add_header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $this->add_header('Content-Transfer-Encoding', 'binary');
        $this->add_header('Content-Length', $filesize);
        $this->add_header('Cache-Control', 'private');
        $this->add_header('Pragma', 'public');
        $this->add_header('Expires', '0');

        foreach ($headers as $name => $value)
        {
            $this->add_header($name, $value);
        }

        $this->send_headers();

        $handle = fopen($filepath, 'rb');
        while ( ! feof($handle))
        {
            echo fread($handle, 8192);
            flush();
        }
        fclose($handle);
    }

    /**
     * Serve a file inline in the browser (e.g. PDF, image) instead of forcing a download
     *
     * @param  string      $filepath
     * @param  string|null $filename
     * @return void
     */
    public function inline($filepath, $filename = NULL)
    {
        if ( ! file_exists($filepath))
        {
            $this->set_status_code(404)->set_content('File not found')->send();
            return;
        }

        $filename = $filename ?: basename($filepath);

        $this->set_status_code(200);
        $this->add_header('Content-Type', mime_content_type($filepath));
        $this->add_header('Content-Disposition', 'inline; filename="' . $filename . '"');
        $this->add_header('Content-Length', filesize($filepath));
        $this->add_header('Cache-Control', 'public, max-age=86400');

        $this->send_headers();

        $handle = fopen($filepath, 'rb');
        while ( ! feof($handle))
        {
            echo fread($handle, 8192);
            flush();
        }
        fclose($handle);
    }

    /**
     * Stream content to the browser via a callback
     * Useful for large datasets, NDJSON, or server-sent events.
     *
     * @param  callable $callback  Function that outputs content
     * @param  array    $headers   Optional additional headers
     * @return void
     */
    public function stream($callback, $headers = array())
    {
        if ( ! is_callable($callback))
        {
            $this->set_status_code(500)->send();
            return;
        }

        $this->add_header('Cache-Control', 'no-cache');

        foreach ($headers as $name => $value)
        {
            $this->add_header($name, $value);
        }

        $this->send_headers();

        call_user_func($callback);
    }

    /**
     * Send a Server-Sent Events (SSE) stream
     * Sets the correct Content-Type and keeps the connection open.
     *
     * @param  callable $callback  Function that emits SSE-formatted events
     * @return void
     */
    public function send_event_stream($callback)
    {
        if (ob_get_level())
        {
            ob_end_clean();
        }

        $this->stream($callback, array(
            'Content-Type'  => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ));
    }

    // ---------------------------------------------------------------
    // REDIRECTS
    // ---------------------------------------------------------------

    /**
     * Redirect to a URL
     *
     * @param  string $url
     * @param  int    $status_code  Must be one of: 301, 302, 303, 307, 308
     * @return void
     */
    public function redirect($url, $status_code = 302)
    {
        $valid_codes = array(301, 302, 303, 307, 308);

        if ( ! in_array($status_code, $valid_codes))
        {
            $status_code = 302;
        }

        $this->set_status_code($status_code);
        $this->add_header('Location', $url);
        $this->send();
        exit;
    }

    /**
     * Permanent redirect (301)
     *
     * @param  string $url
     * @return void
     */
    public function redirect_permanent($url)
    {
        $this->redirect($url, 301);
    }

    /**
     * Post/Redirect/Get redirect (303 See Other)
     * Use after a successful form POST to prevent re-submission on refresh.
     *
     * @param  string $url
     * @return void
     */
    public function redirect_after_post($url)
    {
        $this->redirect($url, 303);
    }

    /**
     * Redirect back to the referring page
     *
     * @param  string $fallback  Fallback URL if referrer is unavailable
     * @return void
     */
    public function back($fallback = '/')
    {
        $lava     = lava_instance();
        $referrer = isset($lava->request) ? $lava->request->referrer() : NULL;

        $this->redirect($referrer ?: $fallback);
    }

    // ---------------------------------------------------------------
    // COOKIES
    // ---------------------------------------------------------------

    /**
     * Queue a cookie to be sent with the response
     *
     * @param  string  $name
     * @param  string  $value
     * @param  int     $expiration  Seconds from now. 0 = session cookie.
     * @param  string  $path
     * @param  string  $domain
     * @param  boolean $secure
     * @param  boolean $httponly
     * @param  string  $samesite    'Lax', 'Strict', or 'None'
     * @return $this
     */
    public function set_cookie($name, $value = '', $expiration = 0, $path = '', $domain = '', $secure = FALSE, $httponly = FALSE, $samesite = 'Lax')
    {
        $this->cookies[] = array(
            'name'       => $name,
            'value'      => $value,
            'expiration' => $expiration,
            'path'       => $path,
            'domain'     => $domain,
            'secure'     => $secure,
            'httponly'   => $httponly,
            'samesite'   => $samesite,
        );

        return $this;
    }

    /**
     * Expire a cookie immediately
     *
     * @param  string $name
     * @param  string $path
     * @param  string $domain
     * @return $this
     */
    public function delete_cookie($name, $path = '', $domain = '')
    {
        return $this->set_cookie($name, '', -3600, $path, $domain);
    }

    // ---------------------------------------------------------------
    // UTILITY
    // ---------------------------------------------------------------

    /**
     * Reset the response to its default state
     *
     * @return $this
     */
    public function clear()
    {
        $this->status_code  = 200;
        $this->headers      = array();
        $this->content      = NULL;
        $this->cookies      = array();
        $this->headers_sent = FALSE;

        return $this;
    }

    /**
     * Return a snapshot of the current response state as an array
     * Useful for testing or logging.
     *
     * @return array
     */
    public function to_array()
    {
        return array(
            'status_code' => $this->status_code,
            'status_text' => $this->get_status_text(),
            'headers'     => $this->headers,
            'content'     => $this->content,
        );
    }

    /**
     * Magic method — returns the response body as a string
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->content;
    }
}