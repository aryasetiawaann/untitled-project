<?php

namespace App\Http\Controllers;

use App\Models\Kompetisi;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(){

      $kompetisis = Kompetisi::whereNull('file_hasil')->get();
      $kompetisi_file = Kompetisi::whereNotNull('file_hasil')->get();
      $kompetisi = Kompetisi::all();

        return view('admin.admin-dashboard', compact('kompetisis', 'kompetisi_file', 'kompetisi'));
      }
}
