<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Brand_model extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->table = 'brand';
    }
}
