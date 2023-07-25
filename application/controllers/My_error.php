<?php
class My_error extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    public function error_404()
    {
        $this->output->set_status_header('404');
        throwError(REQUEST_NOT_FOUND);
    }
}
