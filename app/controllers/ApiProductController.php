<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ApiProductController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->database();
        $this->call->library('api');
        $this->call->model('ProductModel');
        $this->api->rate_limit();
        // Every product endpoint requires a valid access token
        $this->api->require_jwt();
    }

    /** GET /api/products */
    public function index()
    {
        $this->api->require_method('GET');
        $this->api->respond(['data' => $this->ProductModel->getAll()]);
    }

    /** GET /api/products/{id} */
    public function show($id)
    {
        $this->api->require_method('GET');
        $product = $this->ProductModel->getById($id);
        if (!$product) {
            $this->api->respond_error('Product not found', 404);
        }
        $this->api->respond(['data' => $product]);
    }

    /** POST /api/products */
    public function store()
    {
        $this->api->require_method('POST');
        $data = $this->validated($this->api->body());

        $this->ProductModel->create($data);
        $id = $this->db->last_id();

        $this->api->respond([
            'message' => 'Product created',
            'data'    => $this->ProductModel->getById($id),
        ], 201);
    }

    /** PUT|PATCH /api/products/{id} */
    public function update($id)
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'PATCH'], true)) {
            $this->api->respond_error('Method Not Allowed', 405);
        }
        if (!$this->ProductModel->getById($id)) {
            $this->api->respond_error('Product not found', 404);
        }

        $data = $this->validated($this->api->body());
        $this->ProductModel->updateProduct($id, $data);

        $this->api->respond([
            'message' => 'Product updated',
            'data'    => $this->ProductModel->getById($id),
        ]);
    }

    /** DELETE /api/products/{id} */
    public function destroy($id)
    {
        $this->api->require_method('DELETE');
        if (!$this->ProductModel->getById($id)) {
            $this->api->respond_error('Product not found', 404);
        }
        $this->ProductModel->deleteProduct($id);
        $this->api->respond(['message' => 'Product deleted']);
    }

    private function validated(array $in)
    {
        $name  = $in['product_name'] ?? '';
        $price = $in['price'] ?? null;
        $qty   = $in['quantity'] ?? null;

        if ($name === '' || strlen($name) > 100) {
            $this->api->respond_error('product_name is required (max 100 characters)', 422);
        }
        if (!is_numeric($price) || $price < 0) {
            $this->api->respond_error('price must be a non-negative number', 422);
        }
        if (!is_numeric($qty) || (int) $qty < 0) {
            $this->api->respond_error('quantity must be a non-negative integer', 422);
        }

        return [
            'product_name' => $name,
            'description'  => $in['description'] ?? '',
            'price'        => (float) $price,
            'quantity'     => (int) $qty,
        ];
    }
}
