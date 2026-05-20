<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\FinancialAccount;
use App\Models\Note;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function index()
    {
        return Inertia::render('Backup/Index');
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();

        $accounts = $user->financialAccounts()
            ->orderBy('id')
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'type' => $account->type,
                    'initial_balance' => (float) $account->initial_balance,
                    'current_balance' => (float) $account->current_balance,
                    'is_active' => (bool) $account->is_active,
                    'description' => $account->description,
                    'created_at' => optional($account->created_at)->toDateTimeString(),
                    'updated_at' => optional($account->updated_at)->toDateTimeString(),
                ];
            });

        $categories = $user->categories()
            ->orderBy('id')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'type' => $category->type,
                    'color' => $category->color,
                    'icon' => $category->icon,
                    'created_at' => optional($category->created_at)->toDateTimeString(),
                    'updated_at' => optional($category->updated_at)->toDateTimeString(),
                ];
            });

        $transactions = $user->transactions()
            ->orderBy('id')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'financial_account_id' => $transaction->financial_account_id,
                    'category_id' => $transaction->category_id,
                    'type' => $transaction->type,
                    'amount' => (float) $transaction->amount,
                    'transaction_date' => optional($transaction->transaction_date)->format('Y-m-d'),
                    'description' => $transaction->description,
                    'attachment' => $transaction->attachment,
                    'created_at' => optional($transaction->created_at)->toDateTimeString(),
                    'updated_at' => optional($transaction->updated_at)->toDateTimeString(),
                ];
            });

        $notes = $user->notes()
            ->orderBy('id')
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'title' => $note->title,
                    'content' => $note->content,
                    'label' => $note->label,
                    'note_date' => optional($note->note_date)->format('Y-m-d'),
                    'created_at' => optional($note->created_at)->toDateTimeString(),
                    'updated_at' => optional($note->updated_at)->toDateTimeString(),
                ];
            });

        $budgets = $user->budgets()
            ->orderBy('id')
            ->get()
            ->map(function ($budget) {
                return [
                    'id' => $budget->id,
                    'category_id' => $budget->category_id,
                    'month' => $budget->month,
                    'amount' => (float) $budget->amount,
                    'description' => $budget->description,
                    'created_at' => optional($budget->created_at)->toDateTimeString(),
                    'updated_at' => optional($budget->updated_at)->toDateTimeString(),
                ];
            });

        $payload = [
            'app' => 'Loovie Apps',
            'version' => 1,
            'exported_at' => now()->toDateTimeString(),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'data' => [
                'financial_accounts' => $accounts,
                'categories' => $categories,
                'transactions' => $transactions,
                'notes' => $notes,
                'budgets' => $budgets,
            ],
        ];

        $fileName = 'loovie-backup-' . now()->format('Y-m-d-H-i-s') . '.json';

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $fileName, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'backup_file' => ['required', 'file', 'mimes:json,txt', 'max:5120'],
        ]);

        $user = $request->user();

        $content = file_get_contents($request->file('backup_file')->getRealPath());
        $backup = json_decode($content, true);

        if (!is_array($backup) || !isset($backup['data'])) {
            return back()->with('error', 'Format file backup tidak valid.');
        }

        $data = $backup['data'];

        $accountsData = $data['financial_accounts'] ?? [];
        $categoriesData = $data['categories'] ?? [];
        $transactionsData = $data['transactions'] ?? [];
        $notesData = $data['notes'] ?? [];
        $budgetsData = $data['budgets'] ?? [];

        $stats = DB::transaction(function () use (
            $user,
            $accountsData,
            $categoriesData,
            $transactionsData,
            $notesData,
            $budgetsData
        ) {
            $user->transactions()->delete();
            $user->budgets()->delete();
            $user->notes()->delete();
            $user->financialAccounts()->delete();
            $user->categories()->delete();

            $accountMap = [];
            $categoryMap = [];

            foreach ($accountsData as $item) {
                $account = FinancialAccount::create([
                    'user_id' => $user->id,
                    'name' => $item['name'] ?? 'Akun Tanpa Nama',
                    'type' => $item['type'] ?? 'cash',
                    'initial_balance' => $item['initial_balance'] ?? 0,
                    'current_balance' => $item['current_balance'] ?? ($item['initial_balance'] ?? 0),
                    'is_active' => $item['is_active'] ?? true,
                    'description' => $item['description'] ?? null,
                ]);

                if (isset($item['id'])) {
                    $accountMap[$item['id']] = $account->id;
                }
            }

            foreach ($categoriesData as $item) {
                $category = Category::create([
                    'user_id' => $user->id,
                    'name' => $item['name'] ?? 'Kategori Tanpa Nama',
                    'type' => $item['type'] ?? 'expense',
                    'color' => $item['color'] ?? '#6366f1',
                    'icon' => $item['icon'] ?? 'CircleDollarSign',
                ]);

                if (isset($item['id'])) {
                    $categoryMap[$item['id']] = $category->id;
                }
            }

            foreach ($notesData as $item) {
                Note::create([
                    'user_id' => $user->id,
                    'title' => $item['title'] ?? 'Catatan Tanpa Judul',
                    'content' => $item['content'] ?? '',
                    'label' => $item['label'] ?? null,
                    'note_date' => $item['note_date'] ?? now()->format('Y-m-d'),
                ]);
            }

            foreach ($budgetsData as $item) {
                $oldCategoryId = $item['category_id'] ?? null;
                $newCategoryId = $oldCategoryId ? ($categoryMap[$oldCategoryId] ?? null) : null;

                if (!$newCategoryId) {
                    continue;
                }

                Budget::create([
                    'user_id' => $user->id,
                    'category_id' => $newCategoryId,
                    'month' => $item['month'] ?? now()->format('Y-m'),
                    'amount' => $item['amount'] ?? 0,
                    'description' => $item['description'] ?? null,
                ]);
            }

            foreach ($transactionsData as $item) {
                $oldAccountId = $item['financial_account_id'] ?? null;
                $oldCategoryId = $item['category_id'] ?? null;

                $newAccountId = $oldAccountId ? ($accountMap[$oldAccountId] ?? null) : null;
                $newCategoryId = $oldCategoryId ? ($categoryMap[$oldCategoryId] ?? null) : null;

                if (!$newAccountId || !$newCategoryId) {
                    continue;
                }

                Transaction::create([
                    'user_id' => $user->id,
                    'financial_account_id' => $newAccountId,
                    'category_id' => $newCategoryId,
                    'type' => $item['type'] ?? 'expense',
                    'amount' => $item['amount'] ?? 0,
                    'transaction_date' => $item['transaction_date'] ?? now()->format('Y-m-d'),
                    'description' => $item['description'] ?? null,
                    'attachment' => $item['attachment'] ?? null,
                ]);
            }

            $user->financialAccounts()->each(function ($account) {
                if (method_exists($account, 'calculateBalance')) {
                    $account->calculateBalance();
                }
            });

            return [
                'accounts' => count($accountsData),
                'categories' => count($categoriesData),
                'transactions' => count($transactionsData),
                'notes' => count($notesData),
                'budgets' => count($budgetsData),
            ];
        });

        return back()->with(
            'success',
            "Restore berhasil. Data dipulihkan: {$stats['accounts']} akun, {$stats['categories']} kategori, {$stats['transactions']} transaksi, {$stats['notes']} catatan, {$stats['budgets']} budget."
        );
    }
}
