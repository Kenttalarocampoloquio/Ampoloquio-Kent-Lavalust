<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ApiPublicController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->database();
        $this->call->library('api');
        $this->api->rate_limit();
    }

    /** GET /api/latest-products - public, only name and quantity of the 3 newest products */
    public function latest()
    {
        $this->api->require_method('GET');

        $rows = $this->db->table('products')
            ->select('id, product_name, quantity')
            ->order_by('id', 'DESC')
            ->limit(3)
            ->get_all();

        $this->api->respond(['data' => $rows ?: []]);
    }
}