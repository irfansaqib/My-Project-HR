<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientDocumentController extends Controller
{
    public function index()
    {
        return view('client_portal.documents.index');
    }
}