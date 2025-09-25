<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GSSRemittedImport;
use App\Models\GSSRemitted;

class GSSRemittedController extends Controller
{
    public function index()
    {
        $remitted = GSSRemitted::all();

        return Inertia::render('GSSremitted/index', [
            'gssremitted' => $remitted
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new GSSRemittedImport, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Import successful'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function api()
    {
        return response()->json([
            'remitted' => GSSRemitted::all()
        ]);
    }
}
