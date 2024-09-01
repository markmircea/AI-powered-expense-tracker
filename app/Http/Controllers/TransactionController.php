<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query();

        if ($request->has('month') && $request->has('year')) {
            $month = $request->input('month');
            $year = $request->input('year');

            $query->whereYear('date', $year)
                  ->whereMonth('date', $month);
        }

        $transactions = $query->get();

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user' => 'required|in:Mark,Roxi',
            'description' => 'required|string',
            'amount' => 'required|numeric',
            'type' => 'required|in:Income,Expense',
            'category' => 'required|string',
            'date' => 'required|date',
        ]);

        $transaction = Transaction::create($validatedData);

        return response()->json($transaction, 201);
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validatedData = $request->validate([
            'user' => 'sometimes|required|in:Mark,Roxi',
            'description' => 'sometimes|required|string',
            'amount' => 'sometimes|required|numeric',
            'type' => 'sometimes|required|in:Income,Expense',
            'category' => 'sometimes|required|string',
            'date' => 'sometimes|required|date',
        ]);

        $transaction->update($validatedData);

        return response()->json($transaction, 200);
    }



    public function destroy($id)
{
    $transaction = Transaction::findOrFail($id);
    $transaction->delete();
    return response()->json(null, 204);
}
}
