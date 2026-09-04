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

class Database_session_handler implements SessionHandlerInterface
{
    /**
     * Database instance
     * @var object
     */
    private $db;

    /**
     * Session table name
     * @var string
     */
    private $table = 'sessions';

    /**
     * Session lifetime
     * @var int
     */
    private $lifetime;

    public function __construct()
    {
        $this->db = load_class('Database', 'database');

        $this->table = $this->config['sess_table'] ?? 'sessions';
        $this->lifetime = (int)ini_get('session.gc_maxlifetime');
    }

    /**
     * Open the session
     *
     * @param string $save_path
     * @param string $session_name
     * @return boolean
     */
    public function open($save_path, $session_name): bool
    {
        return true;
    }

    /**
     * Close the session
     *
     * @return boolean
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $session_id
     * @return string
     */
    public function read($session_id): string
    {
        $session = $this->db->table($this->table)
            ->select('data')
            ->where('id', $session_id)
            ->where('timestamp', '>', time() - $this->lifetime)
            ->get();

        return $session ? $session['data'] : '';
    }

    /**
     * Write session data
     *
     * @param string $session_id
     * @param string $session_data
     * @return boolean
     */
    public function write($session_id, $session_data): bool
    {
        $timestamp = time();
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $sql = "
            INSERT INTO {$this->table} (id, ip_address, user_agent, timestamp, data)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                data = VALUES(data),
                timestamp = VALUES(timestamp),
                ip_address = VALUES(ip_address),
                user_agent = VALUES(user_agent)
        ";

        // Execute safely
        $this->db->raw($sql, [
            $session_id,
            $ip_address,
            $user_agent,
            $timestamp,
            $session_data
        ]);

        return true;  
    }

    /**
     * Destroy a session
     *
     * @param string $session_id
     * @return boolean
     */
    public function destroy($session_id): bool
    {
        return $this->db->table($this->table)
            ->where('id', $session_id)
            ->delete();
    }

    /**
     * Cleanup old sessions
     *
     * @param int $maxlifetime
     * @return int
     */
    public function gc($maxlifetime): int
    {
        return $this->db->table($this->table)
            ->where('timestamp', '<', time() - $maxlifetime)
            ->delete();
    }
}
