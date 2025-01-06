<?php
use models\Estado;

class estadosController extends Controller
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
            'title' => 'Estados',
            'subject' => 'Lista de Estados',
            'estados' => Estado::select('id','nombre')->orderBy('id','desc')->get(), #esto es lo mismo que hacer SELECT id, nombre FROM estados;
            'warning' => 'No hay estados registrados',
            'link_create' => 'estados/create',
            'button_create' => 'Nuevo Estado'
        ];

        $this->_view->load('estados/index', compact('options','msg_success','msg_error'));
    }

    #metodo get..... disponibiliza una pagina para cargar el formulario 
    public function create()
    {
        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Estados',
            'subject' => 'Nuevo Estado',
            'estado' => Session::get('data'),
            'action' => 'create',
            'send' => $this->encrypt($this->getForm()),
            'process' => 'estados/store',
            'back' => 'estados'
        ];

        $this->_view->load('estados/create', compact('options','msg_success','msg_error'));
    }

    #metodo post......procesa la informacion
    public function store()
    {
        #print_r($_POST);exit;
        $this->validateForm('estados/create',[
            'nombre' => Filter::getText('nombre')
        ]);

        #comprueba NO haya otro estado con el mismo nombre
        $estad = Estado::select('id')->where('nombre', Filter::getText('nombre'))->first();

        if($estad){
            Session::set('msg_error','El estado ingresado YA está registrado!!... intentar con otro');
            $this->redirect('estados/create');
        }

        $estado = new Estado;
        $estado->nombre = Filter::getText('nombre');
        $estado->save();

        Session::destroy('data');
        Session::set('msg_success','REGISTRO DE ESTADO EN FORMA CORRECTA');
        $this->redirect('estados');
    }

    public function show($id = null)
    { #metodo que recibe un id
        Validate::validateModel(Estado::class, $id, 'error/error');
        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Estados',
            'subject' => 'Detalle de Estado',
            'estado' => Estado::find(Filter::filterInt($id)),
            'warning' => 'No hay un estado asociado',
            'back' => 'roles'
        ];

        $this->_view->load('estados/show', compact('options','msg_success','msg_error'));
    }

    #metodo get....metodo para cargar el formulario
    public function edit($id = null)
    {
        Validate::validateModel(Estado::class, $id, 'error/error');

        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Estados',
            'subject' => 'Editar Estado',
            'estado' => Estado::find(Filter::filterInt($id)),
            'action' => 'edit',
            'send' => $this->encrypt($this->getForm()),
            'process' => "estados/update/{$id}",  #interpolacion
            'back' => 'estados'
        ];

        $this->_view->load('estados/edit', compact('options','msg_success','msg_error'));

    }

    #metodo put
    public function update($id = null)
    {
        Validate::validateModel(Estado::class, $id, 'error/error');
        $this->validatePUT();

        $this->validateForm('estados/create',[
            'nombre' => Filter::getText('nombre')
        ]);

        $estado = Estado::find(Filter::filterInt($id));
        $estado->nombre = Filter::getText('nombre');
        $estado->save();

        Session::destroy('data');
        Session::set('msg_success','rol se ha modificado correctamente');
        $this->redirect('estados/show/' . $id);
    }
}

