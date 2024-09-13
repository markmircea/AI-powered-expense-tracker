<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\Transaction;
use App\Models\BankStatement;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Team;
use Smalot\PdfParser\Parser;

class BankStatementController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'bankStatement' => 'required|file|mimes:csv,xlsx,xls,pdf',
            'teamId' => 'nullable|exists:teams,id',
        ]);

        $file = $request->file('bankStatement');
        $originalName = $file->getClientOriginalName();
        $path = $file->store('bank_statements');

        // Save the uploaded file information
        $bankStatement = BankStatement::create([
            'name' => $originalName,
            'path' => $path,
            'size' => $file->getSize(),
        ]);

        // Extract content from the file
        $content = $this->extractContentFromFile($path, $file->getClientOriginalExtension());

        // Analyze content using OpenAI API
        $analysis = $this->analyzeContentWithOpenAI($content);

        // Process and save transactions
        $transactions = $this->processAndSaveTransactions($analysis, $request->input('teamId'));

        return Inertia::render('SpreadSheetComponent', [
            'transactions' => $transactions,
        ]);
    }

    public function getPreviousUploads()
    {
        $previousUploads = BankStatement::orderBy('created_at', 'desc')->get();
        return response()->json($previousUploads);
    }

    public function downloadUpload($id)
    {
        $bankStatement = BankStatement::findOrFail($id);
        return Storage::download($bankStatement->path, $bankStatement->name);
    }

    public function deleteUpload($id)
    {
        $bankStatement = BankStatement::findOrFail($id);
        Storage::delete($bankStatement->path);
        $bankStatement->delete();
        return response()->json(['message' => 'File deleted successfully']);
    }

    private function extractContentFromFile($path, $extension)
    {
        $fullPath = Storage::path($path);

        if ($extension === 'csv') {
            return file_get_contents($fullPath);
        } elseif ($extension === 'pdf') {
            return $this->extractContentFromPdf($fullPath);
        } else {
            $spreadsheet = IOFactory::load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $content = '';
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }
                $content .= implode(",", $rowData) . "\n";
            }
            return $content;
        }
    }

    private function extractContentFromPdf($fullPath)
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($fullPath);
        $content = $pdf->getText();
        return $content;
    }

    private function analyzeContentWithOpenAI($content)
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => "You are a financial analyst tasked with extracting transaction data from bank statements. Extract each transaction's date (in YYYY-MM-DD format), description (analyze all of the text for each entry and include the vendor), amount (expense should always have a - infront of the number), category (based on the name and description and using one these  'Car', 'Cash Out', 'Communication', 'Divertisment', 'Education', 'Food', 'Gifts', 'Health', 'Insurance', 'Nicotine', 'Personal', 'Rent', 'Revolut', 'Restaurant', 'Shopping', 'Sport', 'Subscriptions', 'Supermarket', 'Transport', 'Travel', 'Utilities') , and whether it's 'Income' or 'Expense' saved as type. Provide the output as a JSON array of transactions."],
                    ['role' => 'user', 'content' => "Here's the bank statement content:\n\n$content"]
                ],
                'temperature' => 0.7,
                'max_tokens' => 4000,
            ]);

            $analysisContent = $response->choices[0]->message->content;

            // Remove markdown code block delimiters if present
            $analysisContent = preg_replace('/```json\s*([\s\S]*?)\s*```/', '$1', $analysisContent);
            $analysisContent = trim($analysisContent);

            Log::info('Cleaned OpenAI response: ' . $analysisContent);

            $decodedContent = json_decode($analysisContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error decoding JSON from OpenAI: ' . json_last_error_msg());
                Log::error('Raw OpenAI response: ' . $analysisContent);
                throw new \Exception("Unable to parse OpenAI response as JSON");
            }

            if (is_array($decodedContent) && !isset($decodedContent['transactions'])) {
                // If the root element is the transactions array
                return ['transactions' => $decodedContent];
            }

            return $decodedContent;
        } catch (\Exception $e) {
            Log::error('Error in OpenAI analysis: ' . $e->getMessage());
            throw $e;
        }
    }

    private function processAndSaveTransactions($analysis, $teamId)
    {
        $savedTransactions = [];
        $currentDate = Carbon::now()->format('Y-m-d');
        $userId = Auth::id();

        if (!isset($analysis['transactions']) || !is_array($analysis['transactions'])) {
            Log::error('Invalid analysis format received from AI: ' . json_encode($analysis));
            throw new \Exception("Invalid analysis format received from AI");
        }

        // Check if the user has access to the team
        if ($teamId) {
            $team = Team::find($teamId);
            if (!$team || ($team->owner_id !== $userId && !$team->members->contains($userId))) {
                throw new \Exception("You don't have access to this team");
            }
        }

        foreach ($analysis['transactions'] as $transaction) {
            try {
                $savedTransaction = Transaction::create([
                    'user_id' => $userId,
                    'team_id' => $teamId, // Set the team_id
                    'date' => $transaction['date'] ?? $currentDate,
                    'description' => $transaction['description'] ?? 'No description',
                    'amount' => $transaction['amount'] ?? 0,
                    'category' => $transaction['category'] ?? 'Uncategorized',
                    'type' => $transaction['type'] ?? 'Expense',
                ]);

                $savedTransactions[] = $savedTransaction;
            } catch (\Exception $e) {
                Log::error('Error saving transaction: ' . json_encode($transaction) . '. Error: ' . $e->getMessage());
                // Continue with the next transaction instead of stopping the whole process
            }
        }

        return $savedTransactions;
    }
}
