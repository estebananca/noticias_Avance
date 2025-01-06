<?php

use models\Noticia;

class indexController extends Controller
{

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		list($msg_success, $msg_error) = $this->getMessages();

		$options = [
            'title' => 'Noticias',
            'subject' => 'Lista de Noticias',
            'noticias' => Noticia::with('usuario')->where('vigente', 1)->get(),
            'warning' => 'No hay noticias registradas',
        ];

        $this->_view->load('index/index', compact('options','msg_success','msg_error'));
	}
}