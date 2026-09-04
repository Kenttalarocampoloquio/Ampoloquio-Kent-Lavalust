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
 *  Class Request
 * ------------------------------------------------------
 */
class Request
{
    /**
     * If CSRF Protection is enabled, csrf_verify() will run
     *
     * @var boolean
     */
    private $_enable_csrf = FALSE;

    /**
     * Security instance
     *
     * @var object
     */
    private $security;

    /**
     * Raw input data (for PUT, PATCH, DELETE requests)
     *
     * @var array|null
     */
    private $raw_input = NULL;

    /**
     * Class constructor
     */
    public function __construct()
    {
        /**
         * Load Security Instance
         */
        $this->security = load_class('Security', 'kernel');

        /**
         * Check CSRF Protection if enabled
         */
        $this->_enable_csrf = (config_item('csrf_protection') === TRUE);

        /**
         * Run CSRF validation if enabled
         */
        if ($this->_enable_csrf === TRUE)
        {
            $this->security->csrf_validate();
        }
    }

    // ---------------------------------------------------------------
    // RAW INPUT
    // ---------------------------------------------------------------

    /**
     * Get raw input data safely (for PUT, PATCH, DELETE methods)
     *
     * @return array
     */
    private function get_raw_input()
    {
        if ($this->raw_input === NULL)
        {
            $input = file_get_contents('php://input');

            $content_type = $this->server('CONTENT_TYPE');
            if ($content_type && strpos($content_type, 'application/json') !== FALSE)
            {
                $decoded = json_decode($input, TRUE);
                $this->raw_input = is_array($decoded) ? $decoded : array();
            }
            else
            {
                parse_str($input, $parsed);
                $this->raw_input = $parsed;
            }
        }

        return $this->raw_input;
    }

    /**
     * Get the raw request body as a string
     *
     * @return string
     */
    public function raw_body()
    {
        return (string) file_get_contents('php://input');
    }

    /**
     * Get the request body decoded as JSON
     * Returns NULL if the body is not valid JSON
     *
     * @param  boolean $assoc  Return as associative array (TRUE) or stdClass (FALSE)
     * @return mixed
     */
    public function json($assoc = TRUE)
    {
        $body = $this->raw_body();
        if (empty($body))
        {
            return $assoc ? array() : new stdClass();
        }
        $decoded = json_decode($body, $assoc);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : NULL;
    }

    // ---------------------------------------------------------------
    // INPUT RETRIEVAL
    // ---------------------------------------------------------------

    /**
     * POST Variable
     *
     * @param  string|null $index
     * @param  mixed       $default  Value to return when key is not found
     * @return mixed
     */
    public function post($index = NULL, $default = NULL)
    {
        if ($index === NULL)
        {
            return !empty($_POST) ? $_POST : array();
        }

        return isset($_POST[$index]) ? $_POST[$index] : $default;
    }

    /**
     * GET Variable
     *
     * @param  string|null $index
     * @param  mixed       $default  Value to return when key is not found
     * @return mixed
     */
    public function get($index = NULL, $default = NULL)
    {
        if ($index === NULL)
        {
            return !empty($_GET) ? $_GET : array();
        }

        return isset($_GET[$index]) ? $_GET[$index] : $default;
    }

    /**
     * PUT Variable (from raw input)
     *
     * @param  string|null $index
     * @param  mixed       $default
     * @return mixed
     */
    public function put($index = NULL, $default = NULL)
    {
        $data = $this->get_raw_input();

        if ($index === NULL)
        {
            return $data;
        }

        return isset($data[$index]) ? $data[$index] : $default;
    }

    /**
     * PATCH Variable (from raw input)
     *
     * @param  string|null $index
     * @param  mixed       $default
     * @return mixed
     */
    public function patch($index = NULL, $default = NULL)
    {
        return $this->put($index, $default);
    }

    /**
     * DELETE Variable (from raw input)
     *
     * @param  string|null $index
     * @param  mixed       $default
     * @return mixed
     */
    public function delete($index = NULL, $default = NULL)
    {
        return $this->put($index, $default);
    }

    /**
     * POST then GET
     * Checks POST first, falls back to GET
     *
     * @param  string|null $index
     * @return mixed
     */
    public function post_get($index = NULL)
    {
        $output = $this->post($index);
        return isset($output) ? $output : $this->get($index);
    }

    /**
     * GET then POST
     * Checks GET first, falls back to POST
     *
     * @param  string|null $index
     * @return mixed
     */
    public function get_post($index = NULL)
    {
        $output = $this->get($index);
        return isset($output) ? $output : $this->post($index);
    }

    /**
     * Get all input data (GET + POST + raw input merged)
     *
     * @return array
     */
    public function all()
    {
        return array_merge($this->get(), $this->post(), $this->put());
    }

    /**
     * Get a single input value from all sources (GET, POST, raw input)
     * Optionally supply a default value if the key is missing
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function input($key, $default = NULL)
    {
        $data = $this->all();
        return isset($data[$key]) ? $data[$key] : $default;
    }

    /**
     * Check if input has a specific key
     *
     * @param  string|array $key  Single key or array of keys (all must exist)
     * @return boolean
     */
    public function has($key)
    {
        $data = $this->all();

        foreach ((array) $key as $k)
        {
            if ( ! isset($data[$k]))
            {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Check if input has any of the given keys
     *
     * @param  array $keys
     * @return boolean
     */
    public function has_any($keys)
    {
        $data = $this->all();

        foreach ((array) $keys as $key)
        {
            if (isset($data[$key]))
            {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Check if a key is present and its value is not empty
     *
     * @param  string|array $key
     * @return boolean
     */
    public function filled($key)
    {
        $data = $this->all();

        foreach ((array) $key as $k)
        {
            if (empty($data[$k]))
            {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Check if a key is missing from the input
     *
     * @param  string $key
     * @return boolean
     */
    public function missing($key)
    {
        return ! $this->has($key);
    }

    /**
     * Get only specific input fields
     *
     * @param  array $keys
     * @return array
     */
    public function only($keys)
    {
        $data   = $this->all();
        $result = array();

        foreach ($keys as $key)
        {
            if (isset($data[$key]))
            {
                $result[$key] = $data[$key];
            }
        }

        return $result;
    }

    /**
     * Get all input except specific fields
     *
     * @param  array $keys
     * @return array
     */
    public function except($keys)
    {
        $data = $this->all();

        foreach ($keys as $key)
        {
            unset($data[$key]);
        }

        return $data;
    }

    /**
     * Return a typed integer value from input, or $default if not present / not numeric
     *
     * @param  string $key
     * @param  int    $default
     * @return int
     */
    public function integer($key, $default = 0)
    {
        $value = $this->input($key);
        return ($value !== NULL && is_numeric($value)) ? (int) $value : (int) $default;
    }

    /**
     * Return a typed float value from input, or $default if not present / not numeric
     *
     * @param  string $key
     * @param  float  $default
     * @return float
     */
    public function float($key, $default = 0.0)
    {
        $value = $this->input($key);
        return ($value !== NULL && is_numeric($value)) ? (float) $value : (float) $default;
    }

    /**
     * Return a boolean interpretation of an input value
     * Treats '1', 'true', 'on', 'yes' as TRUE; everything else as FALSE
     *
     * @param  string  $key
     * @param  boolean $default
     * @return boolean
     */
    public function boolean($key, $default = FALSE)
    {
        $value = $this->input($key);

        if ($value === NULL)
        {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Return a string value from input trimmed of whitespace, or $default
     *
     * @param  string $key
     * @param  string $default
     * @return string
     */
    public function string($key, $default = '')
    {
        $value = $this->input($key);
        return ($value !== NULL) ? trim((string) $value) : (string) $default;
    }

    // ---------------------------------------------------------------
    // FILE UPLOADS
    // ---------------------------------------------------------------

    /**
     * Get a single uploaded file from $_FILES
     *
     * @param  string $key
     * @return array|null
     */
    public function file($key)
    {
        return isset($_FILES[$key]) ? $_FILES[$key] : NULL;
    }

    /**
     * Get all uploaded files
     *
     * @return array
     */
    public function files()
    {
        return $_FILES;
    }

    /**
     * Check if a file was uploaded for the given key
     *
     * @param  string $key
     * @return boolean
     */
    public function has_file($key)
    {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    // ---------------------------------------------------------------
    // COOKIES
    // ---------------------------------------------------------------

    /**
     * Cookie Variable
     *
     * @param  string|null $index
     * @param  mixed       $default
     * @return mixed
     */
    public function cookie($index = NULL, $default = NULL)
    {
        if ($index === NULL)
        {
            return !empty($_COOKIE) ? $_COOKIE : array();
        }

        return isset($_COOKIE[$index]) ? $_COOKIE[$index] : $default;
    }

    /**
     * Set a cookie with security options
     *
     * @param  string $name
     * @param  string $value
     * @param  int    $expiration  Lifetime in seconds from now. 0 = session cookie.
     * @param  array  $options     Override: prefix, path, domain, secure, httponly, samesite
     * @return void
     */
    public function set_cookie($name, $value = '', $expiration = 0, $options = array())
    {
        if (preg_match('/[=,; \t\r\n\013\014]/', $name))
        {
            return;
        }

        $lists = array('prefix', 'path', 'domain', 'secure', 'httponly', 'samesite');
        $arr   = array();

        if (is_array($options) && count($options) > 0)
        {
            foreach ($options as $key => $val)
            {
                $arr[$key] = $val;
                $pos = array_search($key, $lists);
                if ($pos !== FALSE)
                {
                    unset($lists[$pos]);
                }
            }
        }

        if ( ! is_numeric($expiration) || $expiration < 0)
        {
            $expires = 1;
        }
        else
        {
            $expires = ($expiration > 0) ? time() + $expiration : 0;
        }

        foreach ($lists as $key)
        {
            $arr[$key] = config_item('cookie_' . $key);
        }

        if ($this->is_secure() && ! isset($arr['secure']))
        {
            $arr['secure'] = TRUE;
        }

        setcookie(
            $arr['prefix'] . $name,
            $value,
            array(
                'expires'  => $expires,
                'path'     => $arr['path'],
                'domain'   => $arr['domain'],
                'secure'   => (bool) $arr['secure'],
                'httponly' => (bool) $arr['httponly'],
                'samesite' => isset($arr['samesite']) ? $arr['samesite'] : 'Lax',
            )
        );
    }

    /**
     * Delete a cookie
     *
     * @param  string $name
     * @param  array  $options
     * @return void
     */
    public function delete_cookie($name, $options = array())
    {
        $this->set_cookie($name, '', -3600, $options);
    }

    // ---------------------------------------------------------------
    // SERVER & HEADERS
    // ---------------------------------------------------------------

    /**
     * Server Variable
     *
     * @param  string|null $index
     * @return mixed
     */
    public function server($index = NULL)
    {
        if ($index === NULL)
        {
            return !empty($_SERVER) ? $_SERVER : array();
        }

        return isset($_SERVER[$index]) ? $_SERVER[$index] : NULL;
    }

    /**
     * Get a single header value by name
     * Accepts standard header names (e.g. 'Content-Type', 'Authorization')
     *
     * @param  string $name
     * @return string|null
     */
    public function header($name)
    {
        // Special cases not prefixed with HTTP_
        $special = array(
            'CONTENT-TYPE'   => 'CONTENT_TYPE',
            'CONTENT-LENGTH' => 'CONTENT_LENGTH',
            'CONTENT-MD5'    => 'CONTENT_MD5',
        );

        $upper = strtoupper(str_replace('-', '_', $name));

        if (isset($special[$upper]))
        {
            return $this->server($special[$upper]);
        }

        return $this->server('HTTP_' . $upper);
    }

    /**
     * Get all request headers as an associative array
     *
     * @return array
     */
    public function headers()
    {
        $headers = array();

        $special = array(
            'CONTENT_TYPE',
            'CONTENT_LENGTH',
            'CONTENT_MD5',
        );

        foreach ($_SERVER as $key => $value)
        {
            if (strpos($key, 'HTTP_') === 0)
            {
                $name           = str_replace('_', '-', substr($key, 5));
                $headers[$name] = $value;
            }
            elseif (in_array($key, $special))
            {
                $name           = str_replace('_', '-', $key);
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    /**
     * Check if a specific header is present
     *
     * @param  string $name
     * @return boolean
     */
    public function has_header($name)
    {
        return $this->header($name) !== NULL;
    }

    /**
     * Get the Bearer token from the Authorization header
     * Returns NULL if the header is missing or not a Bearer token
     *
     * @return string|null
     */
    public function bearer_token()
    {
        $auth = $this->header('Authorization');

        if ($auth && strpos($auth, 'Bearer ') === 0)
        {
            return substr($auth, 7);
        }

        return NULL;
    }

    // ---------------------------------------------------------------
    // HTTP METHOD
    // ---------------------------------------------------------------

    /**
     * HTTP Request Method
     *
     * @param  boolean $upper  Return uppercase if TRUE (default: FALSE)
     * @return string
     */
    public function method($upper = FALSE)
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

        if ($method === 'POST')
        {
            $override = $this->header('X-HTTP-Method-Override');
            if ($override && in_array($override, array('PUT', 'PATCH', 'DELETE')))
            {
                $method = $override;
            }
        }

        return ($upper) ? strtoupper($method) : strtolower($method);
    }

    /**
     * Check if request method matches one or more methods
     *
     * @param  string|array $methods
     * @return boolean
     */
    public function is_method($methods)
    {
        $current = $this->method(TRUE);

        if (is_string($methods))
        {
            return $current === strtoupper($methods);
        }

        return in_array($current, array_map('strtoupper', (array) $methods));
    }

    /**
     * Check if request is GET
     *
     * @return boolean
     */
    public function is_get()
    {
        return $this->method(TRUE) === 'GET';
    }

    /**
     * Check if request is POST
     *
     * @return boolean
     */
    public function is_post()
    {
        return $this->method(TRUE) === 'POST';
    }

    /**
     * Check if request is PUT
     *
     * @return boolean
     */
    public function is_put()
    {
        return $this->method(TRUE) === 'PUT';
    }

    /**
     * Check if request is PATCH
     *
     * @return boolean
     */
    public function is_patch()
    {
        return $this->method(TRUE) === 'PATCH';
    }

    /**
     * Check if request is DELETE
     *
     * @return boolean
     */
    public function is_delete()
    {
        return $this->method(TRUE) === 'DELETE';
    }

    // ---------------------------------------------------------------
    // REQUEST TYPE DETECTION
    // ---------------------------------------------------------------

    /**
     * Is AJAX Request
     *
     * @return boolean
     */
    public function is_ajax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * Check if request expects a JSON response (Accept: application/json)
     *
     * @return boolean
     */
    public function wants_json()
    {
        $accept = $this->header('Accept');
        return $accept !== NULL && strpos($accept, 'application/json') !== FALSE;
    }

    /**
     * Check if the request is sending JSON (Content-Type: application/json)
     *
     * @return boolean
     */
    public function is_json()
    {
        $content_type = $this->header('Content-Type');
        return $content_type !== NULL && strpos($content_type, 'application/json') !== FALSE;
    }

    /**
     * Check if request is from a mobile device (basic UA sniff)
     *
     * @return boolean
     */
    public function is_mobile()
    {
        $ua = $this->user_agent();

        if (empty($ua))
        {
            return FALSE;
        }

        return (bool) preg_match(
            '/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec'
            . '|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront'
            . '|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian'
            . '|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',
            $ua
        );
    }

    /**
     * Check if request is secure (HTTPS)
     *
     * @return boolean
     */
    public function is_secure()
    {
        return (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        );
    }

    // ---------------------------------------------------------------
    // URL & URI
    // ---------------------------------------------------------------

    /**
     * Get the request URI (path + query string)
     *
     * @return string
     */
    public function uri()
    {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    }

    /**
     * Get only the path portion of the URI (no query string)
     *
     * @return string
     */
    public function path()
    {
        return strtok($this->uri(), '?');
    }

    /**
     * Get the full request URL
     *
     * @param  boolean $with_query  Include query string (default: TRUE)
     * @return string
     */
    public function url($with_query = TRUE)
    {
        $protocol = $this->is_secure() ? 'https' : 'http';
        $host     = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $uri      = $this->uri();

        $url = $protocol . '://' . $host . $uri;

        if ( ! $with_query)
        {
            $url = strtok($url, '?');
        }

        return $url;
    }

    /**
     * Get the full URL including scheme and host but without query string
     *
     * @return string
     */
    public function full_url_without_query()
    {
        return $this->url(FALSE);
    }

    /**
     * Get the query string (without the leading ?)
     *
     * @return string
     */
    public function query_string()
    {
        return isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
    }

    /**
     * Get a specific query string parameter
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function query($key, $default = NULL)
    {
        return $this->get($key, $default);
    }

    /**
     * Get the host name (without port)
     *
     * @return string
     */
    public function host()
    {
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        return strtok($host, ':');
    }

    /**
     * Get the scheme (http or https)
     *
     * @return string
     */
    public function scheme()
    {
        return $this->is_secure() ? 'https' : 'http';
    }

    /**
     * Get the request port
     *
     * @return int
     */
    public function port()
    {
        return (int) (isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80);
    }

    /**
     * Get request referrer
     *
     * @return string|null
     */
    public function referrer()
    {
        return $this->header('Referer');
    }

    // ---------------------------------------------------------------
    // IP ADDRESS
    // ---------------------------------------------------------------

    /**
     * Get Client IP Address
     *
     * @param  boolean $trust_proxy  Set to TRUE if behind a trusted proxy
     * @return string
     */
    public function ip_address($trust_proxy = FALSE)
    {
        if ($trust_proxy === TRUE)
        {
            $trusted_headers = array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_REAL_IP');

            foreach ($trusted_headers as $header)
            {
                if (isset($_SERVER[$header]))
                {
                    $ips = explode(',', $_SERVER[$header]);
                    $ip  = trim($ips[0]);

                    if ($this->valid_ip($ip))
                    {
                        return $ip;
                    }
                }
            }
        }

        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    }

    /**
     * Validate an IP Address
     *
     * @param  string $ip     IP address to validate
     * @param  string $which  Protocol: 'ipv4', 'ipv6', or '' (any)
     * @return boolean
     */
    public function valid_ip($ip, $which = '')
    {
        if ( ! is_string($ip) || empty($ip))
        {
            return FALSE;
        }

        switch (strtolower($which))
        {
            case 'ipv4':
                $flags = FILTER_FLAG_IPV4;
                break;
            case 'ipv6':
                $flags = FILTER_FLAG_IPV6;
                break;
            default:
                $flags = 0;
                break;
        }

        return (bool) filter_var($ip, FILTER_VALIDATE_IP, $flags);
    }

    // ---------------------------------------------------------------
    // USER AGENT
    // ---------------------------------------------------------------

    /**
     * Get user agent string
     *
     * @return string|null
     */
    public function user_agent()
    {
        return $this->server('HTTP_USER_AGENT');
    }
}