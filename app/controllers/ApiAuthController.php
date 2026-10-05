<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ApiAuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->database();
        $this->call->library('api');
        $this->call->model('UsersModel');
    }

    /** POST /api/register */
    public function register()
    {
        $this->api->require_method('POST');
        $this->api->rate_limit('register', 10, 60);
        $in = $this->api->body();

        $username = $in['username'] ?? '';
        $email    = $in['email'] ?? '';
        $password = $in['password'] ?? '';

        if ($username === '' || $email === '' || strlen($password) < 6) {
            $this->api->respond_error('username, email and a password of 6+ characters are required', 422);
        }
        if (!filter_var(html_entity_decode($email), FILTER_VALIDATE_EMAIL)) {
            $this->api->respond_error('Invalid email address', 422);
        }

        $exists = $this->db->table('users')
            ->where('username', $username)->or_where('email', $email)->get();
        if ($exists) {
            $this->api->respond_error('Username or email already exists', 409);
        }

        $this->db->table('users')->insert([
            'username' => $username,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role'     => 'user',
        ]);

        $this->api->respond(['message' => 'User registered successfully'], 201);
    }

    /** POST /api/login */
    public function login()
    {
        $this->api->require_method('POST');
        $this->api->rate_limit('login', 10, 60);
        $in = $this->api->body();

        $username = $in['username'] ?? '';
        $password = $in['password'] ?? '';

        $user = $this->db->table('users')->where('username', $username)->get();

        if (!$user || !$user['is_active'] || !password_verify($password, $user['password'])) {
            $this->api->respond_error('Invalid username or password', 401);
        }

        $tokens = $this->api->issue_tokens([
            'id'   => (int) $user['id'],
            'role' => $user['role'],
        ]);

        $this->api->respond([
            'message' => 'Login successful',
            'user'    => ['id' => (int) $user['id'], 'username' => $user['username'], 'role' => $user['role']],
            'tokens'  => $tokens,
        ]);
    }

    /** POST /api/refresh  { "refresh_token": "..." } */
    public function refresh()
    {
        $this->api->require_method('POST');
        $in = $this->api->body();
        $this->api->refresh_access_token($in['refresh_token'] ?? '');
    }

    /** POST /api/logout  { "refresh_token": "..." } */
    public function logout()
    {
        $this->api->require_method('POST');
        $in = $this->api->body();
        if (!empty($in['refresh_token'])) {
            $this->api->revoke_refresh_token($in['refresh_token']);
        }
        $this->api->respond(['message' => 'Logged out']);
    }
}
