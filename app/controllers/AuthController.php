<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AuthController extends Controller
{
    private $valid_username = 'admin';
    private $valid_password = 'admin123';

    public function __construct()
    {
        parent::__construct();
        session_start();
    }

    public function login()
    {
        if ($this->io->method() == 'post') {
            $username = $this->io->post('username');
            $password = $this->io->post('password');

            if ($username === $this->valid_username && $password === $this->valid_password) {
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $username;
                redirect('products');
                return;
            } else {
                $data['error'] = 'Invalid username or password';
                $this->call->view('login', $data);
                return;
            }
        }

        $this->call->view('login');
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        redirect('login');
    }
}