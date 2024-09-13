<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        \Log::info("User ID: " . $user->id);

        if ($request->has('team_id')) {
            $teamId = $request->input('team_id');
            \Log::info("Requested Team ID: " . $teamId);

            // Check if user is a member of the team or owns the team
            $team = $user->teams()->find($teamId) ?? $user->ownedTeams()->find($teamId);

            if (!$team) {
                \Log::error("User " . $user->id . " attempted to access Team " . $teamId . " but is not a member or owner.");
                return response()->json(['error' => 'You do not have access to this team'], 403);
            }

            // Fetch all transactions for the team, regardless of who created them
            $query = Transaction::where('team_id', $teamId);
        } else {
            // Personal transactions
            $query = Transaction::where('user_id', $user->id)->whereNull('team_id');
        }

        // Apply date filters if provided
        if ($request->has('month') && $request->has('year')) {
            $month = $request->input('month');
            $year = $request->input('year');
            $query->whereYear('date', $year)
                ->whereMonth('date', $month);
        }

        $transactions = $query->get();
        \Log::info("Number of transactions fetched: " . $transactions->count());
        return response()->json(['data' => $transactions, 'message' => 'Transactions fetched successfully']);
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
            return response()->json(['data' => $transaction, 'message' => 'Transaction created successfully'], 201);
        } catch (\Exception $e) {
            \Log::error('Failed to save transaction: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save transaction. Please try again.'], 500);
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
        if (
            $transaction->user_id !== $user->id &&
            (!$transaction->team_id || !$user->teams->contains($transaction->team_id))
        ) {
            return response()->json(['error' => 'You are not authorized to update this transaction'], 403);
        }

        try {
            $transaction->update($validatedData);
            return response()->json(['data' => $transaction, 'message' => 'Transaction updated successfully'], 200);
        } catch (\Exception $e) {
            \Log::error('Failed to update transaction: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update transaction. Please try again.'], 500);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $transaction = Transaction::findOrFail($id);

        // Check if the user has permission to delete this transaction
        if (
            $transaction->user_id !== $user->id &&
            (!$transaction->team_id || !$user->teams->contains($transaction->team_id))
        ) {
            return response()->json(['error' => 'You are not authorized to delete this transaction'], 403);
        }

        try {
            $transaction->delete();
            return response()->json(['message' => 'Transaction deleted successfully'], 200);
        } catch (\Exception $e) {
            \Log::error('Failed to delete transaction: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete transaction. Please try again.'], 500);
        }
    }

    public function destroyMultiple(Request $request)
    {
        \Log::info('destroyMultiple called', ['user_id' => Auth::id(), 'input' => $request->all()]);

        $validatedData = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:transactions,id',
        ]);

        $user = Auth::user();

        // Modify this line to specify the table name for the id column
        $teamIds = $user->teams()->pluck('teams.id')->toArray();

        \Log::info('User teams', ['team_ids' => $teamIds]);

        DB::beginTransaction();
        try {
            $transactions = Transaction::whereIn('transactions.id', $validatedData['ids'])
                ->where(function ($query) use ($user, $teamIds) {
                    $query->where('transactions.user_id', $user->id)
                          ->orWhereIn('transactions.team_id', $teamIds);
                })
                ->get();

            $successCount = $transactions->count();
            $failCount = count($validatedData['ids']) - $successCount;

            foreach ($transactions as $transaction) {
                $transaction->delete();
            }

            DB::commit();

            \Log::info('Transactions deleted', [
                'success_count' => $successCount,
                'fail_count' => $failCount,
                'deleted_ids' => $transactions->pluck('id')
            ]);

            return response()->json([
                'message' => "$successCount transactions deleted successfully. $failCount transactions could not be deleted due to permissions.",
                'success_count' => $successCount,
                'fail_count' => $failCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in destroyMultiple', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'An error occurred while deleting transactions. Please try again.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
