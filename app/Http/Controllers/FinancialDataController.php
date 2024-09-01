<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FinancialDataController extends Controller
{
    public function getData($month)
    {
        $filename = "financial_data_{$month}.json";
        if (Storage::exists($filename)) {
            $data = json_decode(Storage::get($filename), true);
            return response()->json($data);
        }
        return response()->json([]);
    }

    public function saveData(Request $request, $month)
    {
        $data = $request->all();
        $filename = "financial_data_{$month}.json";
        Storage::put($filename, json_encode($data));
        return response()->json(['message' => 'Data saved successfully']);
    }
}
