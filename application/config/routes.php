<?php
defined('BASEPATH') or exit('No direct script access allowed');

$route['products']['get'] = 'product/index';
$route['reports']['get'] = 'product/report';
$route['process/load/products']['get'] = 'process/loadProducts';

$route['process/data/split']['post'] = 'process/splitData';
$route['process/load/makers']['get'] = 'process/loadMakers';
$route['process/load/brands']['get'] = 'process/loadBrands';

$route['default_controller'] = 'home';
$route['404_override'] = 'My_error/error_404';
$route['translate_uri_dashes'] = FALSE;
$route['(.*)'] = 'My_error/error_404';
