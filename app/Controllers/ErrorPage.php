<?php namespace App\Controllers;

use \CodeIgniter\Controller;

class ErrorPage extends Controller
{
    public function index()
    {
    }

    public function PageNotFound()
    {
        echo view('Templates/Error_404');
    }

    public function RestrictedAccess()
    {
    }
}
