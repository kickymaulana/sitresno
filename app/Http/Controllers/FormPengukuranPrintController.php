<?php

namespace App\Http\Controllers;

use App\Models\FormPengukuran;
use Illuminate\Contracts\View\View;

class FormPengukuranPrintController extends Controller
{
    public function __invoke(FormPengukuran $formPengukuran): View
    {
        $formPengukuran->load([
            'area',
            'masterMould',
            'customer',
            'items.actuals',
        ]);

        return view('form-pengukurans.print', ['formPengukuran' => $formPengukuran]);
    }
}
