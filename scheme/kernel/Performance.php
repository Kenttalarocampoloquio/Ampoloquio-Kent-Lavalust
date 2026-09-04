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
 * LavaLust Performance Class
 *
 * Provides timing and memory profiling tools for measuring
 * application performance at any point during request execution.
 *
 * This class is auto-loaded by the framework — do not instantiate it manually.
 */
class Performance
{
    /**
     * Registered time markers
     * Keyed by marker name; each entry stores start and optional stop time.
     *
     * @var array
     */
    private $markers = [];

    /**
     * Memory snapshots
     * Keyed by label; stores bytes at the time of the snapshot.
     *
     * @var array
     */
    private $memory_snapshots = [];

    /**
     * Decimal precision used when formatting elapsed time output
     *
     * @var int
     */
    private $precision = 4;

    // ---------------------------------------------------------------
    // TIMING
    // ---------------------------------------------------------------

    /**
     * Mark the start of a timed block
     *
     * @param  string $name  Arbitrary marker name
     * @return void
     */
    public function start($name)
    {
        $this->markers[$name] = [
            'start' => microtime(TRUE),
            'stop'  => NULL,
        ];
    }

    /**
     * Mark the end of a timed block
     *
     * @param  string $name  The same marker name used in start()
     * @return void
     */
    public function stop($name)
    {
        if (isset($this->markers[$name]))
        {
            $this->markers[$name]['stop'] = microtime(TRUE);
        }
    }

    /**
     * Get the elapsed time between a start and stop marker in seconds
     *
     * If no marker name is given, returns the total elapsed time
     * since the framework was initialised (the global 'app_start' marker).
     *
     * If the stop marker has not been set, the current time is used.
     *
     * @param  string $name       Marker name (default: 'app_start' for total time)
     * @param  int    $decimals   Number of decimal places in the result
     * @return string  Elapsed time as a formatted string
     */
    public function elapsed_time($name = 'app_start', $decimals = NULL)
    {
        $decimals = ($decimals !== NULL) ? (int) $decimals : $this->precision;

        if ( ! isset($this->markers[$name]))
        {
            return '0.' . str_repeat('0', $decimals);
        }

        $start = $this->markers[$name]['start'];
        $stop  = $this->markers[$name]['stop'] ?? microtime(TRUE);

        return number_format($stop - $start, $decimals);
    }

    /**
     * Get the elapsed time as a float (in seconds) rather than a formatted string
     *
     * Useful when you need to compare or calculate with the value programmatically.
     *
     * @param  string $name  Marker name
     * @return float
     */
    public function elapsed_time_float($name = 'app_start')
    {
        if ( ! isset($this->markers[$name]))
        {
            return 0.0;
        }

        $start = $this->markers[$name]['start'];
        $stop  = $this->markers[$name]['stop'] ?? microtime(TRUE);

        return (float) ($stop - $start);
    }

    /**
     * Check whether a named marker has been registered
     *
     * @param  string $name
     * @return bool
     */
    public function has_marker($name)
    {
        return isset($this->markers[$name]);
    }

    /**
     * Remove a named marker
     *
     * Useful to free memory when a marker is no longer needed.
     *
     * @param  string $name
     * @return void
     */
    public function clear_marker($name)
    {
        unset($this->markers[$name]);
    }

    /**
     * Remove all registered markers
     *
     * @return void
     */
    public function clear_all_markers()
    {
        $this->markers = [];
    }

    /**
     * Return all registered markers and their recorded times
     *
     * Each entry contains 'start', 'stop' (or NULL if not stopped),
     * and 'elapsed' (NULL if not stopped).
     *
     * @return array
     */
    public function get_all_markers()
    {
        $result = [];

        foreach ($this->markers as $name => $times)
        {
            $result[$name] = [
                'start'   => $times['start'],
                'stop'    => $times['stop'],
                'elapsed' => $times['stop'] !== NULL
                    ? number_format($times['stop'] - $times['start'], $this->precision)
                    : NULL,
            ];
        }

        return $result;
    }

    /**
     * Time a callable and return an array with the result and elapsed time
     *
     * Convenient for benchmarking a single function or closure without
     * manually calling start() and stop().
     *
     * @param  callable $callback   The function to time
     * @param  string   $name       Optional marker name (default: 'callback')
     * @return array    ['result' => mixed, 'elapsed' => string, 'memory_delta' => int]
     */
    public function measure(callable $callback, $name = 'callback')
    {
        $mem_before = memory_get_usage();

        $this->start($name);
        $result = $callback();
        $this->stop($name);

        $mem_after = memory_get_usage();

        return [
            'result'       => $result,
            'elapsed'      => $this->elapsed_time($name),
            'memory_delta' => $mem_after - $mem_before,
        ];
    }

    // ---------------------------------------------------------------
    // MEMORY
    // ---------------------------------------------------------------

    /**
     * Get the current memory usage
     *
     * Returns a human-readable string (e.g. "2.45 MB").
     *
     * @param  bool $real_usage  TRUE to use real allocated memory blocks;
     *                           FALSE (default) to use emalloc() usage
     * @return string
     */
    public function memory_usage($real_usage = FALSE)
    {
        return $this->_format_bytes(memory_get_usage($real_usage));
    }

    /**
     * Get the peak memory usage since the script started
     *
     * Returns a human-readable string (e.g. "4.12 MB").
     * More reliable than memory_usage() for profiling worst-case consumption.
     *
     * @param  bool $real_usage  TRUE to use real allocated blocks
     * @return string
     */
    public function peak_memory_usage($real_usage = FALSE)
    {
        return $this->_format_bytes(memory_get_peak_usage($real_usage));
    }

    /**
     * Get the memory consumed between two snapshot labels
     *
     * @param  string $label  The snapshot label set by memory_snapshot()
     * @return string  Human-readable difference (e.g. "512 KB"), or '0 B' if not found
     */
    public function memory_delta($label)
    {
        if ( ! isset($this->memory_snapshots[$label]['start']))
        {
            return '0 B';
        }

        $start = $this->memory_snapshots[$label]['start'];
        $end   = $this->memory_snapshots[$label]['end'] ?? memory_get_usage();

        return $this->_format_bytes(max(0, $end - $start));
    }

    /**
     * Take a named memory snapshot (start point)
     *
     * Call memory_snapshot_end() with the same label to capture the end point,
     * then use memory_delta() to retrieve the difference.
     *
     * @param  string $label
     * @return void
     */
    public function memory_snapshot($label)
    {
        $this->memory_snapshots[$label] = [
            'start' => memory_get_usage(),
            'end'   => NULL,
        ];
    }

    /**
     * Close a named memory snapshot (end point)
     *
     * @param  string $label  The same label used in memory_snapshot()
     * @return void
     */
    public function memory_snapshot_end($label)
    {
        if (isset($this->memory_snapshots[$label]))
        {
            $this->memory_snapshots[$label]['end'] = memory_get_usage();
        }
    }

    // ---------------------------------------------------------------
    // REPORTING
    // ---------------------------------------------------------------

    /**
     * Set the decimal precision used for formatted elapsed time output
     *
     * @param  int $precision  Number of decimal places (default: 4)
     * @return void
     */
    public function set_precision($precision)
    {
        $this->precision = max(0, (int) $precision);
    }

    /**
     * Return a full performance report as an array
     *
     * Includes all marker elapsed times, current memory usage,
     * and peak memory usage.
     *
     * @return array
     */
    public function report()
    {
        $markers = [];

        foreach ($this->markers as $name => $times)
        {
            $stop = $times['stop'] ?? microtime(TRUE);

            $markers[$name] = [
                'elapsed_seconds' => number_format($stop - $times['start'], $this->precision),
                'stopped'         => $times['stop'] !== NULL,
            ];
        }

        return [
            'markers'      => $markers,
            'memory'       => $this->memory_usage(),
            'peak_memory'  => $this->peak_memory_usage(),
            'total_time'   => $this->elapsed_time('app_start'),
        ];
    }

    // ---------------------------------------------------------------
    // PRIVATE HELPERS
    // ---------------------------------------------------------------

    /**
     * Format a byte count into a human-readable string
     *
     * @param  int $bytes
     * @return string  e.g. "1.25 MB", "512 KB", "256 B"
     */
    private function _format_bytes($bytes)
    {
        if ($bytes >= 1048576)
        {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024)
        {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
}