<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('product_model', 'productModel');
        $this->load->model('brand_model', 'brandModel');
        $this->load->model('maker_model', 'makerModel');
    }
    function index()
    {
        $filters = [
            'jan' => $this->input->get('jan')
        ];
        if (empty($filters['jan'])) {
            throwError(ITEM_NOT_FOUND, 'Invalid JAN Code');
        }
        $product = $this->productModel->get($filters, true);
        returnResponse($product);
    }

    function report()
    {
        $data = $this->productModel->getReport();
        returnResponse($data);
    }
}
