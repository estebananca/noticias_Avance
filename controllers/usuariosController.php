<?php
use models\Usuario;
use models\Role;

class usuariosController extends Controller
{
    public function __construct()
    {
        $this->validateSession();
        parent::__construct();
    }

    public function index()
    {
        $this->validateRol(['Administrador','Editor']);
        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Usuarios',
            'subject' => 'Lista de Usuarios',
            'usuarios' => Usuario::with('role')->get(), 
            #SElECT seria como hacer * FROM roles join usuarios on usuarios.role_id = role.id;
            'warning' => 'Sin usuarios registrados',
            'link_create' => 'usuarios/create',
            'button_create' => 'Nuevo Usuario'
        ];

        $this->_view->load('usuarios/index', compact('options','msg_success','msg_error'));
    }

    public function create()
    {
        $this->validateRol(['Administrador']);
        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Usuarios',
            'subject' => 'Nuevo Usuario',
            'usuario' => Session::get('data'),
            'action' => 'create',
            'send' => $this->encrypt($this->getForm()),
            'process' => 'usuarios/store',
            'roles' => Role::select('id','nombre')->orderBy('nombre')->get(),
            'back' => 'usuarios/index'
        ];

        $this->_view->load('usuarios/create', compact('options','msg_success','msg_error'));
    }

    public function store()
    {
        $this->validateRol(['Administrador']);
        #print_r($_POST);exit;
        $this->validateForm('usuarios/create',[
            'run' => Filter::getText('run'),
            'nombre' => Filter::getText('nombre'),
            'email' => $this->validateEmail(Filter::getPostParam('email')),
            'password' => Filter::getText('password'),
            'role' => Filter::getText('role')
        ]);
    #esta valida el Rut que esta en Controlller
        if (!$this->validateRut(Filter::getText('run'))) {
            Session::set('msg_error','RUT NO es válido!!');
            $this->redirect('usuarios/create');
        }

        #verifica largo del password sea igual o mayor que 8
        if (strlen(Filter::getText('password')) < 8) {
            Session::set('msg_error','password debe contener al menos 8 caracteres');
            $this->redirect('usuarios/create');
        }

        #verificar password y el confirmado sean iguales
        if (Filter::getText('password') != Filter::getText('password_confirm')) {
            Session::set('msg_error','Passwords ingresado no coinciden');
            $this->redirect('usuarios/create');
        }

        #comprobar que no haya otro usuario con el mismo email
        $usuario = Usuario::select('id')->where('email', Filter::getPostParam('email'))->first();

        if($usuario){
            Session::set('msg_error','usuario ingresado ya está registrado... intenta con otro');
            $this->redirect('usuarios/create');
        }

        $password = Helper::encryptPassword(Filter::getText('password'));

        $usuario = new Usuario;
        $usuario->run = Filter::getText('run');
        $usuario->nombre = Filter::getText('nombre');
        $usuario->email = Filter::getText('email');
        $usuario->password = $password;
        $usuario->activo = 2; #2 es igual a inactivo
        $usuario->role_id = Filter::getInt('role');
        $usuario->save();

        Session::destroy('data');
        Session::set('msg_success','El usuario se ha registrado correctamente');
        $this->redirect('usuarios');
    }

    public function show($id = null)
    {
        $this->validateRol(['Administrador','Editor']);
        Validate::validateModel(Usuario::class, $id, 'error/error');
        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Usuarios',
            'subject' => 'Detalle de Usuario',
            'usuario' => Usuario::with('role')->find(Filter::filterInt($id)), 
            #SElECT * FROM roles join usuarios on usuarios.role_id = role.id;
            'warning' => 'No hay un usuario asociado',
            'back' => 'usuarios'
        ];

        $this->_view->load('usuarios/show', compact('options','msg_success','msg_error'));
    }

    public function edit($id = null)
    {
        $this->validateRol(['Administrador']);
        Validate::validateModel(Usuario::class, $id, 'error/error');
        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Usuarios',
            'subject' => 'Editar Usuario',
            'usuario' => Usuario::with('role')->find(Filter::filterInt($id)),
            'action' => 'edit',
            'send' => $this->encrypt($this->getForm()),
            'process' => "usuarios/update/{$id}",
            'roles' => Role::select('id','nombre')->orderBy('nombre')->get(),
            'back' => 'usuarios/index'
        ];

        $this->_view->load('usuarios/edit', compact('options','msg_success','msg_error'));
    }

    public function update($id = null)
    {
        $this->validateRol(['Administrador']);
        Validate::validateModel(Usuario::class, $id, 'error/error');
        $this->validatePUT();

        $this->validateForm("usuarios/edit/{$id}",[
            'run' => Filter::getText('run'),
            'nombre' => Filter::getText('nombre'),
            'activo' => Filter::getText('activo'),
            'role' => Filter::getText('role')
        ]);

        if (!$this->validateRut(Filter::getText('run'))) {
            Session::set('msg_error','El RUT ingresado no es válido');
            $this->redirect('usuarios/edit/' . $id);
        }
        
        $usuario = Usuario::find(Filter::filterInt($id));
        $usuario->run = Filter::getText('run');
        $usuario->nombre = Filter::getText('nombre');
        $usuario->activo = Filter::getInt('activo');
        $usuario->role_id = Filter::getInt('role');
        $usuario->save();

        Session::destroy('data');
        Session::set('msg_success','Usuario modificado correctamente!!!');
        $this->redirect('usuarios/show/' . $id);
    }
}