<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RemittedImport;
use App\Models\CRemitted;

class RemittedController extends Controller
{
    public function index()
    {
        $remitted = CRemitted::all();

        return Inertia::render('remitted/index', [
            'remitted' => $remitted
        ]);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new RemittedImport, $request->file('file'));

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
}
