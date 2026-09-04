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
*  Class Upload
* ------------------------------------------------------
 */
class Upload
{
    /**
     * store LavaLust Instance Object
     *
     * @var object
     */
    private $LAVA;

    /**
     * store uploaded file information
     *
     * @var array
     */
    public $file = array();

    /**
     * store uploaded file extension
     *
     * @var string
     */
    private $extension;

    /**
     * store uploaded file size
     *
     * @var int
     */
    private $file_size;

    /**
     * Default list of allowed file extensions
     *
     * @var array
     */
    private $default_allowed_extensions = array('gif', 'jpg', 'jpeg', 'png');

    /**
     * store uploaded file MIME types
     *
     * @var array
     */
    private $default_allowed_mimes = array('image/gif', 'image/jpg', 'image/jpeg', 'image/png');

    /**
     * store allowed file extensions
     *
     * @var array
     */
    private $allowed_extensions = array();

    /**
     * store allowed file MIME types
     *
     * @var array
     */
    private $allowed_mimes = array();

    /**
     * store allowed file extensions
     *
     * @var string
     */
    private $dir = '';

    /**
     * store maximum file size
     *
     * @var int
     */
    private $max_size;

    /**
     * store minimum file size
     *
     * @var int
     */
    private $min_size;

    /**
     * store uploaded file errors
     *
     * @var array
     */
    private $upload_errors = array();

    /**
     * filename of uploaded file
     *
     * @var string
     */
    private $filename;

    /**
     * store uploaded file information
     *
     * @var boolean
     */
    private $is_image = FALSE;

    /**
     * store uploaded file information
     *
     * @var boolean
     */
    public $encrypted = FALSE;

    /**
     * mime type of uploaded file
     *
     * @var string
     */
    public $mime;

    /**
     * upload constructor.
     *
     * @param array $file
     */
    public function __construct($file = array())
    {
        $this->LAVA = lava_instance();
        $this->file = $file;
        $this->allowed_extensions = $this->default_allowed_extensions;
        $this->allowed_mimes = $this->default_allowed_mimes;
    }

    /**
     * Set allowed file extensions.
     *
     * @param array $ext
     * @return void
     */
    public function allowed_extensions($ext = array())
    {
        if (is_array($ext) && !empty($ext))
            $this->allowed_extensions = $ext;
        return $this;
    }

    /**
     * Set allowed file MIME types.
     *
     * @param array $mimes
     * @return void
     */
    public function allowed_mimes($mimes = array())
    {
        if (is_array($mimes) && !empty($mimes))
            $this->allowed_mimes = $mimes;
        return $this;
    }

    /**
     * Set upload directory.
     *
     * @param string $dir
     * @return void
     */
    public function set_dir($dir)
    {
        $dir = rtrim($dir, '/');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $this->dir = $dir . '/';
        return $this;
    }

    /**
     * set maximum file size
     *
     * @param int $size
     * @return void
     */
    public function max_size($size)
    {
        $this->max_size = $size * 1024 * 1024; // MB to bytes
        return $this;
    }

    /**
     * Set minimum file size
     *
     * @param int $size
     * @return void
     */
    public function min_size($size)
    {
        $this->min_size = $size * 1024 * 1024;
        return $this;
    }

    /**
     * Check if uploaded file is an image
     *
     * @return boolean
     */
    public function is_image()
    {
        $this->is_image = TRUE;
        return $this;
    }

    /**
     * encrypt file name
     *
     * @return void
     */
    public function encrypt_name()
    {
        $this->encrypted = TRUE;
        return $this;
    }

    /**
     * Get upload errors
     *
     * @return array
     */
    public function get_errors()
    {
        return $this->upload_errors;
    }

    /**
     * get uploaded file name
     *
     * @return string
     */
    public function get_filename()
    {
        return $this->filename;
    }

    /**
     * get uploaded file extension
     *
     * @return string
     */
    public function get_extension()
    {
        return $this->extension;
    }

    /**
     * get uploaded file size
     *
     * @return int
     */
    public function get_size()
    {
        return $this->file_size;
    }

    /**
     * upload the file
     *
     * @param boolean $overwrite
     * @param boolean $no_extension
     * @return boolean
     */
    public function do_upload($overwrite = FALSE, $no_extension = FALSE)
    {
        $file = $this->file;

        if (!isset($file['tmp_name'], $file['name'], $file['error'], $file['size']) || $file['error'] != UPLOAD_ERR_OK) {
            $this->upload_errors[] = 'No valid file selected or upload error.';
            return FALSE;
        }

        $this->file_size = $file['size'];

        if ($this->is_image && !@getimagesize($file['tmp_name'])) {
            $this->upload_errors[] = 'Uploaded file is not a valid image.';
        }

        if (isset($this->max_size) && $file['size'] > $this->max_size) {
            $this->upload_errors[] = 'Uploaded file size is too large.';
        }

        if (isset($this->min_size) && $file['size'] < $this->min_size) {
            $this->upload_errors[] = 'Uploaded file size is too small.';
        }

        $file_info = pathinfo($file['name']);
        $filename = $file_info['filename'] ?? 'file_' . time();
        $this->LAVA->call->helper('security');
        $filename = sanitize_filename($filename);

        $this->extension = strtolower($file_info['extension'] ?? '');

        if (!empty($this->extension) && !$this->allowed_extension($this->extension)) {
            $this->upload_errors[] = 'Invalid uploaded file extension.';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $this->mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!empty($this->mime) && !$this->allowed_mime($this->mime)) {
            $this->upload_errors[] = 'Invalid uploaded file MIME type.';
        }

        $this->LAVA->call->helper('directory');
        if (!is_dir($this->dir) || !is_writable($this->dir)) {
            $this->upload_errors[] = 'Upload directory is not writable.';
        }

        if ($this->encrypted) {
            $filename = sha1($filename . '-' . microtime(true) . '-' . random_int(10000, 99999));
        }

        if ($overwrite) {
            $this->filename = $no_extension ? $filename : "{$filename}.{$this->extension}";
        } else {
            $this->filename = $this->unique_filename($this->dir, $filename, $no_extension ? NULL : $this->extension);
        }

        if (!empty($this->upload_errors)) {
            return FALSE;
        }

        $destination = $this->dir . $this->filename;
        if (!@move_uploaded_file($file['tmp_name'], $destination)) {
            $this->upload_errors[] = 'Failed to move uploaded file.';
            return FALSE;
        }

        @chmod($destination, 0644);
        return TRUE;
    }

    /**
     * Set allowed file extensions.
     *
     * @param string $ext
     * @return void
     */
    private function allowed_extension($ext)
    {
        return in_array(strtolower($ext), $this->allowed_extensions);
    }

    /**
     * Check if uploaded file is an image.
     *
     * @param string $mime
     * @return boolean
     */
    public function allowed_mime($mime)
    {
        return in_array(strtolower($mime), $this->allowed_mimes);
    }

    /**
     * Generate a unique filename in the specified directory.
     *
     * @param string $dir
     * @param string $file
     * @param string $ext
     * @return string
     */
    public function unique_filename($dir, $file, $ext)
    {
        $x = '';
        $ext = $ext ? ".$ext" : '';
        while (file_exists("{$dir}{$file}{$x}{$ext}")) {
            $x = $x === '' ? 1 : $x + 1;
        }
        return "{$file}{$x}{$ext}";
    }
}
