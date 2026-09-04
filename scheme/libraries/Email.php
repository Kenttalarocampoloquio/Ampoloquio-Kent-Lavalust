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
 *  Class Email
 * ------------------------------------------------------
 *
 * Composes and sends MIME emails via PHP's mail() function.
 * Supports plain text and HTML bodies, CC, BCC, reply-to,
 * file attachments, priority flags, and fluent method chaining.
 *
 * Usage:
 *   $this->load->library('email');
 *   $this->email->sender('from@example.com', 'My App')
 *               ->recipient('to@example.com')
 *               ->subject('Hello')
 *               ->email_content('<p>Hi</p>', 'html')
 *               ->send();
 */
class Email
{
    // ---------------------------------------------------------------
    // Properties
    // ---------------------------------------------------------------

    /**
     * Sender email address
     * @var string
     */
    private $sender = '';

    /**
     * Sender display name (shown in From field)
     * @var string
     */
    private $sender_name = '';

    /**
     * Primary recipient addresses
     * @var array
     */
    private $recipients = [];

    /**
     * CC recipient addresses
     * @var array
     */
    private $cc = [];

    /**
     * BCC recipient addresses
     * @var array
     */
    private $bcc = [];

    /**
     * Reply-To address (defaults to sender when not set)
     * @var string
     */
    private $reply_to = '';

    /**
     * Email subject line
     * @var string
     */
    private $subject = '';

    /**
     * Email body content
     * @var string
     */
    private $email_body = '';

    /**
     * Body MIME type: 'plain' or 'html'
     * @var string
     */
    private $email_type = 'plain';

    /**
     * File paths queued for attachment
     * @var array
     */
    private $attach_files = [];

    /**
     * Email priority: 1 = High, 3 = Normal, 5 = Low
     * @var int
     */
    private $priority = 3;

    /**
     * Character set used for HTML emails.
     * Defaults to the app charset from config.
     * @var string
     */
    private $charset = '';

    // ---------------------------------------------------------------
    // Constructor
    // ---------------------------------------------------------------

    public function __construct()
    {
        // Read the app charset once; fall back to utf-8
        $this->charset = config_item('charset') ?: 'utf-8';
    }

    // ---------------------------------------------------------------
    // Fluent Setters
    // ---------------------------------------------------------------

    /**
     * Set the sender address and optional display name.
     *
     * @param  string $sender_email  Valid email address
     * @param  string $display_name  Name shown in the From field
     * @return $this
     * @throws Exception if the address is invalid
     */
    public function sender($sender_email, $display_name = '')
    {
        $this->valid_email($sender_email);
        $this->sender      = $sender_email;
        $this->sender_name = $this->filter_header($display_name);
        return $this;
    }

    /**
     * Add a primary (To) recipient.
     * Duplicates are silently ignored.
     *
     * @param  string $recipient  Valid email address
     * @return $this
     * @throws Exception if the address is invalid
     */
    public function recipient($recipient)
    {
        $this->valid_email($recipient);
        if ( ! in_array($recipient, $this->recipients, TRUE))
        {
            $this->recipients[] = $recipient;
        }
        return $this;
    }

    /**
     * Add a CC recipient.
     * Duplicates are silently ignored.
     *
     * @param  string $cc_email  Valid email address
     * @return $this
     * @throws Exception if the address is invalid
     */
    public function cc($cc_email)
    {
        $this->valid_email($cc_email);
        if ( ! in_array($cc_email, $this->cc, TRUE))
        {
            $this->cc[] = $cc_email;
        }
        return $this;
    }

    /**
     * Add a BCC recipient.
     * Duplicates are silently ignored.
     *
     * @param  string $bcc_email  Valid email address
     * @return $this
     * @throws Exception if the address is invalid
     */
    public function bcc($bcc_email)
    {
        $this->valid_email($bcc_email);
        if ( ! in_array($bcc_email, $this->bcc, TRUE))
        {
            $this->bcc[] = $bcc_email;
        }
        return $this;
    }

    /**
     * Set a Reply-To address.
     * If not called, replies go to the sender address.
     *
     * @param  string $reply_to  Valid email address
     * @return $this
     * @throws Exception if the address is invalid
     */
    public function reply_to($reply_to)
    {
        $this->valid_email($reply_to);
        $this->reply_to = $reply_to;
        return $this;
    }

    /**
     * Set the email subject.
     *
     * @param  string $subject
     * @return $this
     * @throws Exception if the subject is empty
     */
    public function subject($subject)
    {
        if (empty(trim($subject)))
        {
            throw new Exception('Email subject cannot be empty.');
        }
        $this->subject = $this->filter_header($subject);
        return $this;
    }

    /**
     * Set the email body content and type.
     *
     * @param  string $content  Email body (plain text or HTML)
     * @param  string $type     'plain' (default) or 'html'
     * @return $this
     */
    public function email_content($content, $type = 'plain')
    {
        // FIX: original used \n for wordwrap — MIME spec requires \r\n
        $this->email_body = wordwrap($content, 70, "\r\n");
        $this->email_type = ($type === 'html') ? 'html' : 'plain';
        return $this;
    }

    /**
     * Shortcut: set an HTML body without specifying the type argument.
     *
     * @param  string $html  HTML content
     * @return $this
     */
    public function html($html)
    {
        return $this->email_content($html, 'html');
    }

    /**
     * Set the email priority.
     *
     * @param  int $level  1 = High, 3 = Normal (default), 5 = Low
     * @return $this
     */
    public function priority($level = 3)
    {
        $allowed = [1, 2, 3, 4, 5];
        $this->priority = in_array((int) $level, $allowed, TRUE) ? (int) $level : 3;
        return $this;
    }

    /**
     * Override the charset used for HTML emails.
     * Defaults to config_item('charset').
     *
     * @param  string $charset  e.g. 'utf-8', 'iso-8859-1'
     * @return $this
     */
    public function charset($charset)
    {
        $this->charset = strtolower(trim($charset));
        return $this;
    }

    /**
     * Queue a file attachment.
     * Duplicates are silently ignored.
     *
     * @param  string $filepath  Absolute path to the file
     * @return $this
     * @throws Exception if the path is empty or the file does not exist
     */
    public function attachment($filepath)
    {
        if (empty($filepath))
        {
            throw new Exception('Attachment path cannot be empty.');
        }
        if ( ! file_exists($filepath))
        {
            throw new Exception('Attachment file not found: ' . $filepath);
        }
        if ( ! in_array($filepath, $this->attach_files, TRUE))
        {
            $this->attach_files[] = $filepath;
        }
        return $this;
    }

    // ---------------------------------------------------------------
    // Accessors (useful for testing and logging)
    // ---------------------------------------------------------------

    /**
     * Return the sender address.
     * @return string
     */
    public function get_sender()
    {
        return $this->sender;
    }

    /**
     * Return the To recipient list.
     * @return array
     */
    public function get_recipients()
    {
        return $this->recipients;
    }

    /**
     * Return the CC list.
     * @return array
     */
    public function get_cc()
    {
        return $this->cc;
    }

    /**
     * Return the BCC list.
     * @return array
     */
    public function get_bcc()
    {
        return $this->bcc;
    }

    /**
     * Return the current subject line.
     * @return string
     */
    public function get_subject()
    {
        return $this->subject;
    }

    /**
     * Return true when the body type is HTML.
     * @return bool
     */
    public function is_html()
    {
        return $this->email_type === 'html';
    }

    // ---------------------------------------------------------------
    // Reset
    // ---------------------------------------------------------------

    /**
     * Reset all state so the instance can be reused for a new email
     * without reinstantiating the library.
     *
     * @return $this
     */
    public function reset()
    {
        $this->sender       = '';
        $this->sender_name  = '';
        $this->recipients   = [];
        $this->cc           = [];
        $this->bcc          = [];
        $this->reply_to     = '';
        $this->subject      = '';
        $this->email_body   = '';
        $this->email_type   = 'plain';
        $this->attach_files = [];
        $this->priority     = 3;
        $this->charset      = config_item('charset') ?: 'utf-8';
        return $this;
    }

    // ---------------------------------------------------------------
    // Validation helpers
    // ---------------------------------------------------------------

    /**
     * Validate an email address.
     * Sanitises then validates using PHP's filter functions.
     *
     * @param  string $email
     * @return bool   TRUE on success
     * @throws Exception if the address is invalid
     */
    public function valid_email($email)
    {
        $clean = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        if ( ! filter_var($clean, FILTER_VALIDATE_EMAIL))
        {
            throw new Exception('Invalid email address: ' . htmlspecialchars($email));
        }
        return TRUE;
    }

    /**
     * Strip characters that are unsafe in email header fields
     * (prevents header injection via newlines and high-byte chars).
     *
     * FIX: original used FILTER_UNSAFE_RAW which does NOT strip anything
     * by default — renamed and corrected to remove newlines and high bytes.
     *
     * @param  string $string
     * @return string
     */
    public function filter_header($string)
    {
        // Remove newlines / carriage returns — primary header injection vector
        $string = str_replace(["\r", "\n", "\r\n"], '', $string);
        // Strip high-byte characters
        $string = filter_var($string, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_HIGH);
        return trim($string);
    }

    /**
     * Filter a string for safe inclusion in email headers.
     * Alias for filter_header() to maintain backward compatibility.
     *
     * @param  string $string
     * @return string
     */
    public function filter_string($string)
    {
        return $this->filter_header($string);
    }

    // ---------------------------------------------------------------
    // Attachment builder (protected — internal use only)
    // ---------------------------------------------------------------

    /**
     * Encode a single file as a base64 MIME attachment part.
     *
     * @param  string $filepath  Absolute path to the file
     * @return string|false  MIME part string, or false if file is unreadable
     */
    protected function recreate_attachment($filepath)
    {
        if ( ! file_exists($filepath))
        {
            return FALSE;
        }

        $mime     = mime_content_type($filepath);
        $size     = filesize($filepath);
        $handle   = fopen($filepath, 'rb');
        $raw      = fread($handle, $size);
        fclose($handle);

        $encoded  = chunk_split(base64_encode($raw));
        $filename = basename($filepath);
        $eol      = "\r\n";

        return 'Content-Type: ' . $mime . '; name="' . $filename . '"' . $eol
             . 'Content-Transfer-Encoding: base64' . $eol
             . 'Content-Disposition: attachment; filename="' . $filename . '"' . $eol
             . 'Content-ID: <' . $filename . '>' . $eol
             . $eol . $encoded . $eol;
    }

    // ---------------------------------------------------------------
    // Send
    // ---------------------------------------------------------------

    /**
     * Compose and send the email.
     *
     * Returns TRUE on success, FALSE on failure.
     * Throws Exception when required fields (sender, recipient, subject)
     * are missing.
     *
     * @return bool
     * @throws Exception if sender, recipients, or subject are not set
     */
    public function send()
    {
        // --- Guards ---
        if (empty($this->sender))
        {
            throw new Exception('Email sender is not set. Call sender() before send().');
        }
        if (empty($this->recipients))
        {
            throw new Exception('No recipients set. Call recipient() before send().');
        }
        if (empty($this->subject))
        {
            throw new Exception('Email subject is not set. Call subject() before send().');
        }

        $eol = "\r\n";

        // Unique MIME boundaries
        $bm = '----=_Part_' . md5(uniqid((string) mt_rand(), TRUE));
        $bc = '----=_Alt_'  . md5(uniqid((string) mt_rand(), TRUE));

        // Content-Type for the body part
        $content_type = ($this->email_type === 'html')
            ? 'Content-Type: text/html; charset=' . $this->charset
            : 'Content-Type: text/plain; charset=iso-8859-1';

        // Priority label
        $priority_labels = [1 => 'High', 2 => 'High', 3 => 'Normal', 4 => 'Low', 5 => 'Low'];
        $priority_label  = $priority_labels[$this->priority] ?? 'Normal';

        // From header
        $from = empty($this->sender_name)
            ? $this->sender
            : $this->sender_name . ' <' . $this->sender . '>';

        // Reply-To header — fall back to sender when not explicitly set
        $reply_to = ! empty($this->reply_to) ? $this->reply_to : $this->sender;

        // Build headers
        $headers   = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'X-Mailer: PHP/' . phpversion();
        $headers[] = 'From: ' . $from;
        $headers[] = 'Return-Path: ' . $from;
        $headers[] = 'Reply-To: ' . $reply_to;
        $headers[] = 'X-Priority: ' . $this->priority;
        $headers[] = 'X-MS-Priority: ' . $this->priority;
        $headers[] = 'Importance: ' . $priority_label;

        if ( ! empty($this->cc))
        {
            $headers[] = 'Cc: ' . implode(', ', $this->cc);
        }

        if ( ! empty($this->bcc))
        {
            $headers[] = 'Bcc: ' . implode(', ', $this->bcc);
        }

        $headers[] = 'Content-Type: multipart/related; boundary="' . $bm . '"';

        // Build body
        $body  = $eol . '--' . $bm . $eol;
        $body .= 'Content-Type: multipart/alternative; boundary="' . $bc . '"' . $eol;

        if ( ! empty($this->email_body))
        {
            $body .= $eol . '--' . $bc . $eol;
            $body .= $content_type . $eol;
            $body .= $eol . $this->email_body . $eol;
        }

        $body .= $eol . '--' . $bc . '--' . $eol;

        // Attachments
        foreach ($this->attach_files as $filepath)
        {
            $part = $this->recreate_attachment($filepath);
            if ($part !== FALSE)
            {
                $body .= $eol . '--' . $bm . $eol;
                $body .= $part;
            }
        }

        $body .= $eol . '--' . $bm . '--' . $eol;

        $to = implode(', ', $this->recipients);

        return mail(
            $to,
            $this->subject,
            $body,
            implode($eol, $headers),
            '-f' . $this->sender
        );
    }
}