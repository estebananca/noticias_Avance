<?php

use models\Noticia;

class noticiasController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->validateSession();
        $this->validateRol(['Administrador','Editor']);
        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Noticias',
            'subject' => 'Lista de Noticias',
            'noticias' => Noticia::with('usuario')->get(),
            'warning' => 'No hay noticias registradas',
            'link_create' => 'noticias/create',
            'button_create' => 'Nueva Noticia'
        ];

        $this->_view->load('noticias/index', compact('options','msg_success','msg_error'));
    }

    public function create()
    {
        $this->validateSession();
        $this->validateRol(['Periodista']);
        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Noticias',
            'subject' => 'Nueva Noticia',
            'noticia' => Session::get('data'),
            'action' => 'create',
            'send' => $this->encrypt($this->getForm()),
            'process' => 'noticias/store',
            'back' => 'noticias/noticiasUsuario/' . Session::get('user_id')
        ];

        $this->_view->load('noticias/create', compact('options','msg_success','msg_error'));
    }

    public function store()
    {
        $this->validateSession();
        $this->validateRol(['Periodista']);

        $this->validateForm('noticias/create',[
            'titulo' => Filter::getText('titulo'),
            'descripcion' => Filter::getText('descripcion')
        ]);

        $noticia = Noticia::select('id')->where('titulo', Filter::getText('titulo'))->first();

        if ($noticia) {
            Session::set('msg_error','La noticia ingresada ya está registrada... intenta con otra');
            $this->redirect('noticias/create');
        }

        $noticia = new Noticia;
        $noticia->titulo = Filter::getText('titulo');
        $noticia->descripcion = Filter::getText('descripcion');
        $noticia->vigente = 2; #la noticia no esta vigente
        $noticia->ruta = Helper::friendlyRoute(Filter::getText('titulo'));
        $noticia->usuario_id = Session::get('user_id');
        $noticia->save();

        Session::destroy('data');
        Session::set('msg_success','La noticia se ha registrado correctamente');
        $this->redirect('noticias/noticiasUsuario/' . Session::get('user_id'));
    }

    public function show($id = null)
    {
        $this->validateSession();
        $this->validateRol(['Administrador','Periodista','Editor']);

        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Noticias',
            'subject' => 'Detalle de Noticia',
            'noticia' => Noticia::with('usuario')->find(Filter::filterInt($id)),
            'warning' => 'No hay una noticia asociada',
        ];

        $this->_view->load('noticias/show', compact('options','msg_success','msg_error'));
    }

    public function edit($id = null)
    {
        $this->validateSession();
        $this->validateRol(['Periodista']);

        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Noticias',
            'subject' => 'Editar Noticia',
            'noticia' => Noticia::with('usuario')->find(Filter::filterInt($id)),
            'action' => 'edit',
            'send' => $this->encrypt($this->getForm()),
            'process' => "noticias/update/{$id}",
            'back' => 'noticias/noticiasUsuario/' . Session::get('user_id')
        ];

        $this->_view->load('noticias/edit', compact('options','msg_success','msg_error'));
    }

    public function update($id = null)
    {
        $this->validateSession();
        $this->validateRol(['Periodista']);

        $this->validateForm('noticias/create',[
            'titulo' => Filter::getText('titulo'),
            'descripcion' => Filter::getText('descripcion'),
            'vigente' => Filter::getText('vigente')
        ]);

        $noticia = Noticia::find(Filter::filterInt($id));
        $noticia->titulo = Filter::getText('titulo');
        $noticia->descripcion = Filter::getText('descripcion');
        $noticia->vigente = Filter::getInt('vigente');
        $noticia->ruta = Helper::friendlyRoute(Filter::getText('titulo'));
        $noticia->save();

        Session::destroy('data');
        Session::set('msg_success','La noticia se ha modificado correctamente');
        $this->redirect('noticias/show/' . $id);
    }

    public function noticiasUsuario($usuario = null)
    {
        $this->validateSession();
        $this->validateRol(['Periodista']);

        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Noticias',
            'subject' => 'Lista de Noticias',
            'noticias' => Noticia::with('usuario')->where('usuario_id',Session::get('user_id'))->get(),
            'warning' => 'No hay noticias registradas',
            'link_create' => 'noticias/create',
            'button_create' => 'Nueva Noticia'
        ];

        $this->_view->load('noticias/noticiasUsuario', compact('options','msg_success','msg_error'));
    }

    public function noticia($ruta = null)
    {
        $noticia = Noticia::with('usuario')->where('ruta', $ruta)->where('vigente', 1)->first();

        if(!$noticia)
        {
            $this->redirect();
        }

        list($msg_success, $msg_error) = $this->getMessages();

        $options = [
            'title' => 'Noticias',
            'subject' => 'Detalle de Noticia',
            'noticia' => $noticia,
            'warning' => 'No hay una noticia asociada',
            'back' => 'index/index'
        ];

        $this->_view->load('noticias/noticia', compact('options','msg_success','msg_error'));
    }
}