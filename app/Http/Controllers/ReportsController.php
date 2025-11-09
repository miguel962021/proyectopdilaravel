<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ReportsController extends Controller
{
    public function summary(): View
    {
        return view('reports.summary');
    }

    public function students(): View
    {
        return view('reports.students');
    }

    public function surveys(): View
    {
        return view('reports.surveys');
    }
}
