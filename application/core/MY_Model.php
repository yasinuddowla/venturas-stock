<?php
class MY_Model extends CI_Model
{
    public $table;

    function __construct()
    {
        parent::__construct();
    }
    public function getById($id)
    {
        return $this->getWhere('id', $id);
    }
    public function getByName($name)
    {
        return $this->getWhere('name', $name);
    }
    public function getWhere($field, $value, $returnRow = true)
    {
        if ($returnRow) $this->db->limit(1);
        $data = $this->db->where($field, $value)->get($this->table)->result_array();
        return $returnRow ? ($data[0] ?? null) : $data;
    }
    public function getAll()
    {
        return $this->db->get($this->table)->result_array();
    }
    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        return $this->lastInsertId();
    }
    public function insertBatch($data)
    {
        return count($data) > 0 ? $this->db->insert_batch($this->table, $data) : null;
    }
    public function updateById($id, $data)
    {
        return $this->db->where('id', $id)->update($this->table, $data);
    }
    public function delete($id)
    {
        if (!is_array($id)) {
            $id = [$id];
        }
        return $this->db->where_in('id', $id)->delete($this->table);
    }
    public function deleteWhere($field, $value)
    {
        return $this->db->where($field, $value)->delete($this->table);
    }
    public function lastInsertId()
    {
        return $this->db->insert_id();
    }
}
