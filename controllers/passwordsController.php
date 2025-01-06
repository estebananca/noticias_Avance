<?php

use models\Usuario;

class passwordsController extends Controller
{
    public function __construct()
    {

        parent::__construct();
    }

    public function edit($id = null)
    {
        $this->validateSession();
        $this->validateRol(['Administrador']);

        Validate::validateModel(Usuario::class, $id, 'error/denied');

        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Passwords',
            'subject' => 'Cambiar Password',
            'process' => "passwords/update/{$id}",
            'send' => $this->encrypt($this->getForm()),
            'usuario' => Usuario::select('id','nombre')->find(Filter::filterInt($id))
        ];

        $this->_view->load('passwords/edit', compact('options','msg_success','msg_error'));
    }

    public function update($id = null)
    {
        $this->validateSession();
        $this->validateRol(['Administrador']);

        Validate::validateModel(Usuario::class, $id, 'error/denied');
        $this->validatePUT();

        $this->validateForm("passwords/edit/{$id}",[
            'password' => Filter::getText('password')
        ]);

        #verificar que el largo del password sea igual o mayor que 8
        if (strlen(Filter::getText('password')) < 8) {
            Session::set('msg_error','El password debe contener al menos 8 caracteres');
            $this->redirect('passwords/edit/' . $id);
        }

        #verificar que el password y el confirmado sean iguales
        if (Filter::getText('password') != Filter::getText('password_confirm')) {
            Session::set('msg_error','Los passwords ingresados no coinciden');
            $this->redirect('passwords/edit/' . $id);
        }

        $password = Helper::encryptPassword(Filter::getText('password'));

        $usuario = Usuario::find(Filter::filterInt($id));
        $usuario->password = $password;
        $usuario->save();

        Session::destroy('data');
        Session::set('msg_success','El password modificado correctamente');
        $this->redirect('usuarios/show/' . $id);
    }
}