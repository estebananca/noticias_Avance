<?php

class adminController extends Controller
{
    public function __construct()
    {
        $this->validateSession();
        parent::__construct();
    }

    public function index()
    {
        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Admin',
            'subject' => 'Lista de Módulos',
        ];

        $this->_view->load('admin/index', compact('options','msg_success','msg_error'));
    }
}
