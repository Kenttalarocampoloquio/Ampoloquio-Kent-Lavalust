<?php
class Security_headers
{
    public function __construct()
    {
        $this->set_csp();
    }

    private function set_csp()
    {
        $nonce = base64_encode(random_bytes(32));

        // Make nonce available globally as early as possible
        $_lava = lava_instance();
        $_lava->call->library('session');
        $_lava->session->set_userdata('csp_nonce', $nonce);

        // Also put it in a global variable that error pages can access
        if (!defined('CSP_NONCE')) {
            define('CSP_NONCE', $nonce);
        }

        $csp = "default-src 'self'; " .
               "script-src 'self' 'nonce-{$nonce}'; " .
               "style-src 'self' 'nonce-{$nonce}'; " .
               "img-src 'self' data:; " .
               "font-src 'self'; " .
               "object-src 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self'; " .
               "frame-ancestors 'none'; " .
               "upgrade-insecure-requests;";

        header("Content-Security-Policy: " . $csp);
        // header("Content-Security-Policy-Report-Only: " . $csp); // for testing
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains"); // if HTTPS
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()"); // feature policy
    }
}