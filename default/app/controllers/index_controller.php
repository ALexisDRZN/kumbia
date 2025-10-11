<?php

/**
 * Controller por defecto si no se usa el routes
 *
 */
class IndexController extends AppController
{

    public function index()
    {

    }
    public function login()
    {
        View::template('login'); // <--- Esto es lo importante
    }
    public function register()
    {
        View::template('register'); // <--- Esto es lo importante
    }
    public function recupera()
    {
        View::template('recupera'); // <--- Esto es lo importante
    }

}
