<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_model extends MY_Model
{
    function __construct()
    {
        parent::__construct();
        $this->table = 'product';
    }
    public function get($filters = [], $returnRow = false)
    {
        if (isset($filters['jan'])) {
            $this->db->where('p.jan_code', $filters['jan']);
        }
        $tagFromDescription = TAG_FROM_DESCRIPTION;
        $tagFromReview = TAG_FROM_REVIEW;
        if ($returnRow) $this->db->limit(1);
        $data = $this->db->select(
            "p.jan_code, p.name product_name,
            b.name brand,
            m.name maker,
            pa.attributes,
            (
                select tags from product_tag 
                where jan_code=p.jan_code and extracted_from = '{$tagFromDescription}'
            ) tags_from_description,
            (
                select tags from product_tag 
                where jan_code=p.jan_code and extracted_from = '{$tagFromReview}'
            ) tags_from_review",
            false
        )
            ->join('product_brand pb', 'pb.jan_code = p.jan_code', 'left')
            ->join('product_maker pm', 'pm.jan_code = p.jan_code', 'left')
            ->join('maker m', 'm.id = pm.maker_id', 'left')
            ->join('brand b', 'b.id = pb.brand_id', 'left')
            ->join('product_attribute pa', 'pa.jan_code = p.jan_code', 'left')
            ->get("{$this->table} p")->result_array();

        foreach ($data as $i => $pr) {
            $data[$i]['attributes'] = json_decode($pr['attributes'], true);
            $data[$i]['tags_from_description'] = json_decode($pr['tags_from_description'], true);
            $data[$i]['tags_from_review'] = json_decode($pr['tags_from_review'], true);
        }
        if ($returnRow) return !$data ? null : $data[0];
        return $data;
    }
    function getReport()
    {
        $tagFromDescription = TAG_FROM_DESCRIPTION;
        $tagFromReview = TAG_FROM_REVIEW;

        $productCuont = $this->db->query("SELECT count(jan_code) as total FROM product")->row_array();
        $totalRecords = $productCuont['total'] ?? 1;

        $countQuery = $this->db->query("SELECT count(jan_code) as count FROM `product` 
                                        where name = '' OR name is NULL")->row_array();
        $data['product_name'] = ($countQuery['count'] ?? 0) / $totalRecords;

        $countQuery = $this->db->query("SELECT count(jan_code) as count FROM `product_attribute` 
                                        where JSON_LENGTH(attributes) > 0")->row_array();
        $data['attributes'] = ($countQuery['count'] ?? 0) / $totalRecords;

        $countQuery = $this->db->query("SELECT count(jan_code) as count FROM `product_maker`")->row_array();
        $data['maker'] = ($countQuery['count'] ?? 0) / $totalRecords;

        $countQuery = $this->db->query("SELECT count(jan_code) as count FROM `product_brand`")->row_array();
        $data['brand'] = ($countQuery['count'] ?? 0) / $totalRecords;

        $countQuery = $this->db->query("SELECT count(jan_code) as count FROM `product_tag` 
                                        where JSON_LENGTH(tags) > 0
                                        AND extracted_from = '{$tagFromDescription}'")->row_array();
        $data['tags_from_description'] = ($countQuery['count'] ?? 0) / $totalRecords;

        $countQuery = $this->db->query("SELECT count(jan_code) as count FROM `product_tag` 
                                        where JSON_LENGTH(tags) > 0 
                                        AND extracted_from = '{$tagFromReview}'")->row_array();
        $data['tags_from_review'] = ($countQuery['count'] ?? 0) / $totalRecords;

        return $data;
    }
}
