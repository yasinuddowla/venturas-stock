<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
    }
    function index()
    {
        returnResponse("Ping!");
    }
}
