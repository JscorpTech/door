<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeleteAccount extends Controller
{

    public function get()
    {
        return view('delete-account');
    }
}
