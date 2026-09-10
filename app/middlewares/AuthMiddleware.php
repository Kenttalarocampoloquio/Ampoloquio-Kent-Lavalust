<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AuthMiddleware
{
    public function handle(Closure $next)
    {
        session_start();

        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            redirect('login');
            return;
        }

        return $next();
    }
}