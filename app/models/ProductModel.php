<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Model: ModelUsers
 * 
 * Automatically generated via CLI.
 */
class ProductModel extends Model {
    protected $table = 'products';

    public function getAll()
    {
        return $this->db->table($this->table)->get_all();
    }

    public function getById($id)
    {
        return $this->db->table($this->table)->where('id', $id)->get();
    }

    public function create($data)
    {
        return $this->db->table($this->table)->insert($data);
    }

    public function updateProduct($id, $data)
    {
        return $this->db->table($this->table)->where('id', $id)->update($data);
    }

    public function deleteProduct($id)
    {
        return $this->db->table($this->table)->where('id', $id)->delete();
    }
    protected $primary_key = 'id';
    protected $fillable = [];
    protected $guarded = ['id'];

    
    public function __construct()
    {
        parent::__construct();
    }
}