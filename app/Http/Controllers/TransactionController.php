<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Transaction::where('user_id', $user->id);

        if ($request->has('team_id')) {
            $teamId = $request->input('team_id');
            if ($teamId === 'null' || $teamId === null) {
                // Personal transactions
                $query->whereNull('team_id');
            } else {
                // Team transactions
                $team = $user->teams()->findOrFail($teamId);
                $query->where('team_id', $team->id);
            }
        } else {
            // Default to personal transactions if no team_id is provided
            $query->whereNull('team_id');
        }

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
            'description' => 'required|string',
            'amount' => 'required|numeric',
            'type' => 'required|in:Income,Expense',
            'category' => 'required|string',
            'date' => 'required|date',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        $validatedData['user_id'] = Auth::id();

        try {
            $transaction = Transaction::create($validatedData);
            return response()->json($transaction, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save transaction'], 500);
        }
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validatedData = $request->validate([
            'description' => 'sometimes|required|string',
            'amount' => 'sometimes|required|numeric',
            'type' => 'sometimes|required|in:Income,Expense',
            'category' => 'sometimes|required|string',
            'date' => 'sometimes|required|date',
            'team_id' => 'sometimes|nullable|exists:teams,id',
        ]);

        $user = Auth::user();

        // Check if the user has permission to update this transaction
        if ($transaction->user_id !== $user->id &&
            (!$transaction->team_id || !$user->teams->contains($transaction->team_id))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $transaction->update($validatedData);

        return response()->json($transaction, 200);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $transaction = Transaction::findOrFail($id);

        // Check if the user has permission to delete this transaction
        if ($transaction->user_id !== $user->id &&
            (!$transaction->team_id || !$user->teams->contains($transaction->team_id))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $transaction->delete();
        return response()->json(null, 204);
    }
}
