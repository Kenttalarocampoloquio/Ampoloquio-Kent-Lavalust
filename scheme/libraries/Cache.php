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
 * @since Version 1
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */
/**
* ------------------------------------------------------
*  Cache Invoker
* ------------------------------------------------------
 */
class Cache
{
    /**
     * Lava framework instance reference
     * Used to access framework helpers, libraries, and other core functionality
     * 
     * @var object
     */
    private $_lava;
    
    /**
     * Framework configuration array
     * Contains cache settings like directory path, default expiration, and driver type
     * 
     * @var array
     */
    private $_config;
    
    /**
     * Cache storage directory path
     * Trailing directory separator is appended for safe file path construction
     * 
     * @var string
     */
    private $_path;
    
    /**
     * Current cache content being processed
     * Temporarily holds data before writing or after reading from cache
     * 
     * @var mixed
     */
    private $_contents;
    
    /**
     * Current cache filename being operated on
     * Used as the base name before adding .cache extension
     * 
     * @var string
     */
    private $_filename;
    
    /**
     * Expiration timestamp for current cache item
     * Unix timestamp when the cache should become invalid
     * 
     * @var int|null
     */
    private $_expires;
    
    /**
     * Default expiration time in seconds from configuration
     * Used when no explicit expiration is provided
     * 
     * @var int
     */
    private $_default_expires;
    
    /**
     * Creation timestamp for current cache item
     * Unix timestamp when the cache item was created
     * 
     * @var int
     */
    private $_created;
    
    /**
     * Cache dependencies for the current item
     * Array of other cache keys that must be valid for this cache to be valid
     * 
     * @var array
     */
    private $_dependencies = [];
    
    /**
     * Cache tags for the current operation
     * Tags allow grouping and selective invalidation of related cache items
     * 
     * @var array
     */
    private $_tags = [];
    
    /**
     * Storage driver type for serialization
     * 'php' uses serialize/unserialize, 'json' uses json_encode/json_decode
     * 
     * @var string
     */
    private $_driver = 'php';
    
    /**
     * L1 memory cache storage (static - shared across all instances)
     * Provides ultra-fast access to frequently used cache items
     * Cleared on page reload but persists within single request
     * 
     * @var array
     */
    private static $_memory = [];
    
    /**
     * Filename for tag version tracking system
     * Stores current version numbers for each tag to enable tag-based invalidation
     * 
     * @var string
     */
    private $_tag_file = '_tag_versions.cache';
    
    /**
     * Lock acquisition timeout in seconds
     * Prevents infinite waiting when cache stampede protection locks are contested
     * 
     * @var int
     */
    private $_lock_timeout = 5;
    
    /**
     * Lock retry interval in microseconds
     * Controls how frequently to check if lock has been released
     * 
     * @var int
     */
    private $_lock_sleep = 100000;
    
    /**
     * Constructor - Initializes cache system
     * Sets up configuration, creates cache directory if missing, and resets state
     */
    public function __construct()
    {
        $this->_lava = lava_instance();
        $this->_config = get_config();

        $this->_path = rtrim($this->_config['cache_dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->_default_expires = $this->_config['cache_default_expires'] ?? 0;
        $this->_driver = $this->_config['cache_driver'] ?? 'php';
		$this->_lock_timeout = $this->_config['cache_lock_timeout'] ?? '5';
		$this->_lock_sleep = $this->_config['cache_lock_sleep'] ?? '100000';

        $this->_reset();

        if (!is_dir($this->_path)) {
            mkdir($this->_path, 0777, true);
        }
    }
    
    /**
     * Reset current cache instance properties
     * Clears all temporary data before starting a new cache operation
     */
    private function _reset()
    {
        $this->_contents = null;
        $this->_filename = null;
        $this->_expires = null;
        $this->_created = null;
        $this->_dependencies = [];
        $this->_tags = [];
    }

    /* ---------------------------------------------------------
     * MEMORY LAYER
     * --------------------------------------------------------- */
    
    /**
     * Retrieve value from memory cache (L1 cache)
     * Memory cache provides sub-millisecond access for frequently accessed items
     * 
     * @param string $key Cache key identifier
     * @return mixed|null Returns cached value or null if not found
     */
    private function _memory_get($key)
    {
        return self::$_memory[$key] ?? null;
    }
    
    /**
     * Store value in memory cache (L1 cache)
     * Values stored here are available for the duration of the current request only
     * 
     * @param string $key Cache key identifier
     * @param mixed $value Value to store in memory cache
     */
    private function _memory_set($key, $value)
    {
        self::$_memory[$key] = $value;
    }
    
    /**
     * Delete value from memory cache
     * Removes an item from L1 cache without affecting disk storage
     * 
     * @param string $key Cache key identifier
     */
    private function _memory_delete($key)
    {
        unset(self::$_memory[$key]);
    }

    /* ---------------------------------------------------------
     * TAG VERSIONING
     * --------------------------------------------------------- */
    
    /**
     * Set tags for current cache operation
     * Tags allow grouping related cache items for batch invalidation
     * 
     * @param string|array $tags Single tag string or array of tags
     * @return $this Returns self for method chaining
     */
    public function tags($tags)
    {
        $this->_tags = (array) $tags;
        return $this;
    }
    
    /**
     * Invalidate cache entries by tag(s)
     * Increments version numbers for specified tags, making all tagged cache items stale
     * 
     * @param string|array $tags Tag or array of tags to invalidate
     */
    public function invalidate_tags($tags)
    {
        $tags = (array) $tags;

        $versions = $this->_load_tag_versions();

        foreach ($tags as $tag) {
            if (!isset($versions[$tag])) {
                $versions[$tag] = 1;
            } else {
                $versions[$tag]++;
            }
        }

        $this->_save_tag_versions($versions);
    }
    
    /**
     * Load tag version tracking data from disk
     * Reads the JSON file containing current version numbers for all tags
     * 
     * @return array Associative array of tag names to version numbers
     */
    private function _load_tag_versions()
    {
        $file = $this->_path . $this->_tag_file;

        if (!file_exists($file)) return [];

        $data = file_get_contents($file);
        return $data ? json_decode($data, true) : [];
    }
    
    /**
     * Save tag version tracking data to disk
     * Writes the tag version array back to JSON file for persistence
     * 
     * @param array $versions Associative array of tag names to version numbers
     */
    private function _save_tag_versions($versions)
    {
        file_put_contents($this->_path . $this->_tag_file, json_encode($versions));
    }

    /* ---------------------------------------------------------
     * LOCKING
     * --------------------------------------------------------- */
    
    /**
     * Get lock file path for given cache filename
     * Lock files use .lock extension and are stored alongside cache files
     * 
     * @param string $filename Base cache filename (without extension)
     * @return string Full filesystem path to lock file
     */
    private function _lock_path($filename)
    {
        return $this->_path . $filename . '.lock';
    }
    
    /**
     * Acquire exclusive file lock with timeout protection
     * Prevents cache stampede by allowing only one process to regenerate cache
     * 
     * @param string $filename Cache filename to acquire lock for
     * @return resource|false Returns file pointer resource on success, false on failure
     */
    private function _acquire_lock($filename)
    {
        $fp = fopen($this->_lock_path($filename), 'c');
        if (!$fp) return false;

        $start = time();

        do {
            if (flock($fp, LOCK_EX | LOCK_NB)) {
                return $fp;
            }
            usleep($this->_lock_sleep);
        } while ((time() - $start) < $this->_lock_timeout);

        fclose($fp);
        return false;
    }
    
    /**
     * Release previously acquired file lock
     * Unlocks the file and closes the file pointer resource
     * 
     * @param resource $fp File pointer resource from _acquire_lock()
     */
    private function _release_lock($fp)
    {
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /* ---------------------------------------------------------
     * CORE CALL
     * --------------------------------------------------------- */
    
    /**
     * Cache library method calls with automatic result caching
     * Intercepts calls to library methods and returns cached results when available
     * 
     * @param string $library Name of the library (will be loaded if not already)
     * @param string $method Method name to call on the library
     * @param array $arguments Array of arguments to pass to the method
     * @param int|null $expires Cache expiration time in seconds (null uses default)
     * @return mixed Result from either cache or the actual method call
     */
    public function library($library, $method, $arguments = [], $expires = null)
    {
        if (!class_exists(ucfirst($library))) {
            $this->_lava->call->library($library);
        }

        return $this->_call($library, $method, $arguments, $expires);
    }
    
    /**
     * Cache model method calls with automatic result caching
     * Intercepts calls to model methods and returns cached results when available
     * 
     * @param string $model Name of the model (will be loaded if not already)
     * @param string $method Method name to call on the model
     * @param array $arguments Array of arguments to pass to the method
     * @param int|null $expires Cache expiration time in seconds (null uses default)
     * @return mixed Result from either cache or the actual method call
     */
    public function model($model, $method, $arguments = [], $expires = null)
    {
        if (!class_exists(ucfirst($model))) {
            $this->_lava->call->model($model);
        }

        return $this->_call($model, $method, $arguments, $expires);
    }
    
    /**
     * Core caching logic with stampede protection
     * Implements cache-aside pattern with fallback to stale content when needed
     * 
     * @param string $property Class property name (library or model instance)
     * @param string $method Method name to call
     * @param array $arguments Arguments for the method call
     * @param int|null $expires Cache expiration time in seconds
     * @return mixed Result from cache or fresh method execution
     */
    private function _call($property, $method, $arguments = [], $expires = null)
    {
        $arguments = array_values((array) $arguments);

        $cache_file = $property . DIRECTORY_SEPARATOR . sha1($method . serialize($arguments));

        if ($expires < 0) {
            $this->delete($cache_file);
            return null;
        }

        $cached = $this->get($cache_file);

        if ($cached !== false && $cached !== null) {
            return $cached;
        }

        $lock = $this->_acquire_lock($cache_file);

        if ($lock) {
            $cached = $this->get($cache_file);
            if ($cached !== false && $cached !== null) {
                $this->_release_lock($lock);
                return $cached;
            }

            $result = call_user_func_array([$this->_lava->$property, $method], $arguments);

            $this->write($result, $cache_file, $expires);

            $this->_release_lock($lock);

            return $result;
        }

        $stale = $this->get($cache_file, false);

        if ($stale !== false && $stale !== null) {
            return $stale;
        }

        return call_user_func_array([$this->_lava->$property, $method], $arguments);
    }

    /* ---------------------------------------------------------
     * GET
     * --------------------------------------------------------- */
    
    /**
     * Retrieve cached data from disk storage
     * Loads cache file, validates expiration and tags, then returns content
     * 
     * @param string|null $filename Cache filename to retrieve (null uses current)
     * @param bool $use_expires Whether to check expiration timestamp (false allows stale)
     * @return mixed Returns cached content, false if missing/invalid, null on decode error
     */
    public function get($filename = null, $use_expires = true)
    {
        if ($filename !== null) {
            $this->_reset();
            $this->_filename = $filename;
        }

        $key = $this->_filename;

        $mem = $this->_memory_get($key);
        if ($mem !== null) {
            return $mem;
        }

        $filepath = $this->_path . $this->_filename . '.cache';

        if (!file_exists($filepath)) return false;

        $raw = file_get_contents($filepath);
        if (!$raw) return false;

        $data = $this->_decode($raw);
        if (!$data) return null;

        if (!empty($data['__cache_tags'])) {
            $versions = $this->_load_tag_versions();

            foreach ($data['__cache_tags'] as $tag => $ver) {
                if (!isset($versions[$tag]) || $versions[$tag] != $ver) {
                    return false;
                }
            }
        }

        if (
            $use_expires &&
            isset($data['__cache_expires']) &&
            $data['__cache_expires'] < time()
        ) {
            return false;
        }

        $this->_memory_set($key, $data['__cache_contents']);

        return $data['__cache_contents'];
    }

    /* ---------------------------------------------------------
     * WRITE
     * --------------------------------------------------------- */
    
    /**
     * Write data to cache storage
     * Serializes content with metadata and saves to disk with proper directory structure
     * 
     * @param mixed|null $contents Data to cache (null uses current contents)
     * @param string|null $filename Cache filename (null uses current filename)
     * @param int|null $expires Expiration time in seconds (null uses default)
     * @param array $dependencies Array of cache dependencies
     */
    public function write($contents = null, $filename = null, $expires = null, $dependencies = [])
    {
        if ($contents !== null) {
            $tags = $this->_tags;

            $this->_reset();

            $this->_contents = $contents;
            $this->_filename = $filename;
            $this->_expires = $expires;
            $this->_dependencies = (array) $dependencies;

            $this->_tags = $tags;
        }

        $dir = dirname($this->_path . $this->_filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $versions = $this->_load_tag_versions();
        $tag_versions = [];

        foreach ($this->_tags as $tag) {
            $tag_versions[$tag] = $versions[$tag] ?? 1;
        }

        $payload = [
            '__cache_contents' => $this->_contents,
            '__cache_created' => time(),
            '__cache_dependencies' => $this->_dependencies,
            '__cache_tags' => $tag_versions
        ];

        if (!empty($this->_expires)) {
            $payload['__cache_expires'] = time() + $this->_expires;
        } elseif (!empty($this->_default_expires)) {
            $payload['__cache_expires'] = time() + $this->_default_expires;
        }

        $filepath = $this->_path . $this->_filename . '.cache';

        file_put_contents($filepath, $this->_encode($payload));
        chmod($filepath, 0666);

        $this->_memory_set($this->_filename, $this->_contents);

        $this->_reset();
    }

    /* ---------------------------------------------------------
     * DELETE
     * --------------------------------------------------------- */
    
    /**
     * Delete a specific cache file
     * Removes the cache file from disk and removes from memory cache
     * 
     * @param string|null $filename Cache filename to delete (null uses current)
     */
    public function delete($filename = null)
    {
        if ($filename !== null) {
            $this->_filename = $filename;
        }

        $filepath = $this->_path . $this->_filename . '.cache';

        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $this->_memory_delete($this->_filename);

        $this->_reset();
    }
    
    /**
     * Delete all cache files in a directory
     * Recursively removes all cache files and clears memory cache
     * 
     * @param string $dirname Directory name relative to cache path (empty for root)
     */
    public function delete_all($dirname = '')
    {
        $this->_lava->call->helper('file');

        if (file_exists($this->_path . $dirname)) {
            delete_files($this->_path . $dirname, true, true);
        }

        self::$_memory = [];

        $this->_reset();
    }

    /* ---------------------------------------------------------
     * DRIVER
     * --------------------------------------------------------- */
    
    /**
     * Encode data based on configured driver
     * Uses either PHP serialization or JSON encoding for storage
     * 
     * @param mixed $data Data to encode for storage
     * @return string Encoded string representation of the data
     */
    private function _encode($data)
    {
        if ($this->_driver === 'json') {
            return json_encode($data);
        }

        return serialize($data);
    }
    
    /**
     * Decode data based on configured driver
     * Converts stored string back to original PHP data structure
     * 
     * @param string $data Encoded string data from cache file
     * @return mixed Decoded PHP data structure
     */
    private function _decode($data)
    {
        if ($this->_driver === 'json') {
            return json_decode($data, true);
        }

        return unserialize($data);
    }
}