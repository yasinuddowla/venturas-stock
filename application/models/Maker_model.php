<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Maker_model extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->table = 'maker';
    }
}
