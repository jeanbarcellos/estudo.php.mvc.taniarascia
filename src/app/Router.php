<?php

/**
 * Router Class
 *
 * This code was originally created for The PHP Practitioner course on Laracasts.
 * I have modified it for my purposes, adding Session, List, and User classes into the
 * controller, as well as routing usernames and dynamic URLs such as edit/:list_id.
 *
 * Este código foi originalmente criado para o curso Practitioner PHP em Laracasts.
 * Modifiquei-o para meus propósitos, adicionando classes Session, List e User ao
 * controlador, bem como roteamento de nomes de usuário e URLs dinâmicos, como edit /: list_id.
 *
 * @link https://github.com/laracasts/The-PHP-Practitioner-Full-Source-Code/blob/master/core/Router.php
 */

use Laconia\Session;
use Laconia\ListClass;
use Laconia\User;
use Laconia\Comment;

class Router
{
    /**
     * All registered routes.
     */
    public $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

    /**
     * Load a user's routes file.
     */
    public static function load($file)
    {
        $router = new static;

        require $file;

        return $router;
    }

    /**
     * Register a GET route.
     */
    public function get($uri, $controller)
    {
        $this->routes['GET'][$uri] = $controller;
    }

    /**
     * Register a POST route.
     */
    public function post($uri, $controller)
    {
        $this->routes['POST'][$uri] = $controller;
    }
    /**
     * Register a PUT route.
     */
    public function put($uri, $controller)
    {
        $this->routes['PUT'][$uri] = $controller;
    }
    /**
     * Register a DELETE route.
     */
    public function delete($uri, $controller)
    {
        $this->routes['DELETE'][$uri] = $controller;
    }

    /**
     * Load the requested URI's associated controller method.
     * Carregar o método do controlador associado do URI solicitado.
     */
    public function direct($uri, $requestType)
    {
        $userControl = new User;
        $username = $userControl->getUserByUsername($uri);

        // If uri contains edit, go to edit controller
        if (($pos = strpos($uri, '/')) !== false) {
            if (strpos($uri, 'edit') !== false) {
                $param = substr($uri, $pos + 1);
                $uri = 'edit';
            }
        }
        // Gather all users from the database and compare against uri
        elseif ($username) {
            $uri = 'user';
        }

        if (array_key_exists($uri, $this->routes[$requestType])) {
            return $this->callAction(
                ...explode('@', $this->routes[$requestType][$uri])
            );
        } else {
            return $this->callAction(
                ...explode('@', $this->routes[$requestType]['404'])
            );
        }

        throw new Exception('No route defined for this URI.');
    }

    /**
     * Load and call the relevant controller action.
     * Carregue e chame a ação do controlador relevante.
     */
    protected function callAction($controller, $action)
    {
        $session = new Session;
        $userControl = new User;
        $list = new ListClass;
        $comment = new Comment;

        $controller = new $controller($session, $userControl, $list, $comment);

        if (!method_exists($controller, $action)) {
            throw new Exception(
                "{$controller} does not respond to the {$action} action."
            );
        }
        return $controller->$action();
    }
}
