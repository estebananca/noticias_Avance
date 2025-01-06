<?php
use models\Role;

class rolesController extends Controller
{
    public function __construct()
    {
        $this->validateSession();
        $this->validateRol(['Administrador']);
        parent::__construct();
    }

    #metodo head
    public function index()
    {
        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Roles',
            'subject' => 'Lista de Roles',
            'roles' => Role::select('id','nombre')->orderBy('id','desc')->get(), #esto es lo mismo que hacer SELECT id, nombre FROM roles;
            'warning' => 'No hay roles registrados',
            'link_create' => 'roles/create',
            'button_create' => 'Nuevo Rol'
        ];

        $this->_view->load('roles/index', compact('options','msg_success','msg_error'));
    }

    #metodo get..... disponibiliza una pagina para cargar el formulario 
    public function create()
    {
        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Roles',
            'subject' => 'Nuevo Rol',
            'role' => Session::get('data'),
            'action' => 'create',
            'send' => $this->encrypt($this->getForm()),
            'process' => 'roles/store',
            'back' => 'roles'
        ];

        $this->_view->load('roles/create', compact('options','msg_success','msg_error'));
    }

    #metodo post......procesa la informacion
    public function store()
    {
        #print_r($_POST);exit;
        $this->validateForm('roles/create',[
            'nombre' => Filter::getText('nombre')
        ]);

        #comprueba NO haya otro rol con el mismo nombre
        $rol = Role::select('id')->where('nombre', Filter::getText('nombre'))->first();

        if($rol){
            Session::set('msg_error','El rol ingresado YA está registrado!!... intentar con otro');
            $this->redirect('roles/create');
        }

        $role = new Role;
        $role->nombre = Filter::getText('nombre');
        $role->save();

        Session::destroy('data');
        Session::set('msg_success','REGISTRO DE ROL EN FORMA CORRECTA');
        $this->redirect('roles');
    }

    public function show($id = null)
    { #metodo que recibe un id
        Validate::validateModel(Role::class, $id, 'error/error');
        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Roles',
            'subject' => 'Detalle de Rol',
            'role' => Role::find(Filter::filterInt($id)),
            'warning' => 'No hay un rol asociado',
            'back' => 'roles'
        ];

        $this->_view->load('roles/show', compact('options','msg_success','msg_error'));
    }

    #metodo get....metodo para cargar el formulario
    public function edit($id = null)
    {
        Validate::validateModel(Role::class, $id, 'error/error');

        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Roles',
            'subject' => 'Editar Rol',
            'role' => Role::find(Filter::filterInt($id)),
            'action' => 'edit',
            'send' => $this->encrypt($this->getForm()),
            'process' => "roles/update/{$id}",  #interpolacion
            'back' => 'roles'
        ];

        $this->_view->load('roles/edit', compact('options','msg_success','msg_error'));

    }

    #metodo put
    public function update($id = null)
    {
        Validate::validateModel(Role::class, $id, 'error/error');
        $this->validatePUT();

        $this->validateForm('roles/create',[
            'nombre' => Filter::getText('nombre')
        ]);

        $role = Role::find(Filter::filterInt($id));
        $role->nombre = Filter::getText('nombre');
        $role->save();

        Session::destroy('data');
        Session::set('msg_success','rol se ha modificado correctamente');
        $this->redirect('roles/show/' . $id);
    }
}