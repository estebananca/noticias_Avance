<?php

use models\Usuario;

class loginController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function login()
    {
        if (Session::get('authenticate')) {
            $this->redirect();
        }

        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Login',
            'subject' => 'Iniciar Sesión',
            'process' => 'login/store',
            'send' => $this->encrypt($this->getForm())
        ];

        $this->_view->load('login/login', compact('options','msg_success','msg_error'));
    }

    public function store()
    {
        if (Session::get('authenticate')) {
            $this->redirect();
        }


        $this->validateForm('login/login',[
            'email' => $this->validateEmail(Filter::getPostParam('email')),
            'password' => Filter::getText('password')
        ]);


        $password = Helper::encryptPassword(Filter::getText('password'));

        $usuario = Usuario::with('role')
            ->where('email', Filter::getPostParam('email'))
            ->where('password', $password)
            ->where('activo', 1)
            ->first();

        if(!$usuario){
            Session::set('msg_error','El email o el password no están registrados');
            $this->redirect('login/login');
        }

        Session::set('authenticate', true);
        Session::set('user_id', $usuario->id);
        Session::set('user_name', $usuario->nombre);
        Session::set('user_role', $usuario->role->nombre);
        Session::set('time', time());

        $this->redirect('admin');
    }

    public function logout()
    {
        $this->validateSession();

        Session::destroy();

        $this->redirect();
    }
}