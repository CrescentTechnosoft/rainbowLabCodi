<?php
namespace App\Controllers;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 *
 * @package CodeIgniter
 */

use CodeIgniter\Controller;

class BaseController extends Controller
{

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];


    protected \CodeIgniter\Session\Session $session;
    /**
     * Constructor.
     */

    public function __construct()
    {
        $this->session = \Config\Services::session();
    }

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        //--------------------------------------------------------------------
        // Preload any models, libraries, etc, here.
        //--------------------------------------------------------------------
        // E.g.:
    }

    protected function ValidateUser(string $pageKey=null):void
    {
        if (!$this->session->has('hosID')) {
            \http_response_code(401);
            die('Login to Continue');
        } elseif (!is_null($pageKey) && (!in_array($pageKey, \json_decode($this->session->get('Access'))) || !$this->session->has('hasSubscription'))) {
            \http_response_code(401);
            die('Un Authorized Access');
        }
    }

    public function ValidateUpdate():void
    {
        if (!in_array('update', \json_decode($this->session->get('Access')))) {
            die('Un Authorized Access');
        }
    }

    function ValidateDelete():void
    {
        if (!in_array('delete', \json_decode($this->session->get('Access')))) {
            die('Un Authorized Access');
        }
    }
}
