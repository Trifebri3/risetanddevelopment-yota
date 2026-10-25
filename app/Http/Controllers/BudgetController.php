<?php

namespace App\Http\Controllers;

use App\Models\BudgetItem;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BudgetController extends Controller
{
    public function storeBudgetItem(Request $request, Project $project): RedirectResponse
    {
        if (! $project->hasAccess($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'category' => 'required|string|max:100',
            'item_name' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'estimated_unit_price' => 'required|numeric|min:0',
            'procurement_status' => 'required|in:planned,to_purchase,ordered,received',
        ]);

        BudgetItem::create([
            'project_id' => $project->id,
            'category' => $validated['category'],
            'item_name' => $validated['item_name'],
            'qty' => $validated['qty'],
            'unit' => $validated['unit'],
            'estimated_unit_price' => $validated['estimated_unit_price'],
            'procurement_status' => $validated['procurement_status'],
        ]);

        return back()->with('success', 'Rencana anggaran berhasil ditambahkan.');
    }

    public function updateBudgetItem(Request $request, BudgetItem $budgetItem): RedirectResponse
    {
        if (! $budgetItem->project->hasAccess($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'category' => 'required|string|max:100',
            'item_name' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'estimated_unit_price' => 'required|numeric|min:0',
            'procurement_status' => 'required|in:planned,to_purchase,ordered,received',
        ]);

        $budgetItem->update($validated);

        return back()->with('success', 'Rencana anggaran berhasil diperbarui.');
    }

    public function destroyBudgetItem(Request $request, BudgetItem $budgetItem): RedirectResponse
    {
        if (! $budgetItem->project->hasAccess($request->user())) {
            abort(403);
        }

        $budgetItem->delete();

        return back()->with('success', 'Item anggaran berhasil dihapus.');
    }

    public function storeExpense(Request $request, Project $project): RedirectResponse
    {
        if (! $project->hasAccess($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'budget_item_id' => 'nullable|exists:budget_items,id',
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $expense = Expense::create([
            'project_id' => $project->id,
            'budget_item_id' => $validated['budget_item_id'] ?? null,
            'date' => $validated['date'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Auto-log expense event in development journal
        JournalEntry::create([
            'project_id' => $project->id,
            'user_id' => $request->user()->id,
            'date' => $validated['date'],
            'title' => 'Realisasi Pengeluaran: '.$validated['description'],
            'activity' => 'Mencatat pengeluaran sebesar Rp '.number_format($validated['amount'], 0, ',', '.').' untuk kategori '.$validated['category'],
            'results' => 'Catatan: '.($validated['notes'] ?: 'Pengeluaran sesuai alokasi rencana anggaran.'),
            'entry_type' => 'system',
            'status' => 'finalized',
            'is_public' => false,
        ]);

        return back()->with('success', 'Pengeluaran aktual berhasil dicatat.');
    }

    public function destroyExpense(Request $request, Expense $expense): RedirectResponse
    {
        if (! $expense->project->hasAccess($request->user())) {
            abort(403);
        }

        $expense->delete();

        return back()->with('success', 'Catatan pengeluaran dihapus.');
    }

    public function exportCsv(Request $request, Project $project): StreamedResponse
    {
        if (! $project->hasAccess($request->user())) {
            abort(403);
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="RAB_'.$project->slug.'_'.date('Ymd').'.csv"',
        ];

        return response()->stream(function () use ($project) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['RENCANA ANGGARAN BIAYA (RAB) - '.$project->title]);
            fputcsv($handle, ['Kategori', 'Nama Item', 'Jumlah', 'Satuan', 'Estimasi Harga Satuan (Rp)', 'Total Estimasi (Rp)', 'Status Pengadaan']);

            foreach ($project->budgetItems as $item) {
                fputcsv($handle, [
                    $item->category,
                    $item->item_name,
                    $item->qty,
                    $item->unit,
                    $item->estimated_unit_price,
                    $item->total_estimated_price,
                    $item->procurement_status,
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['REALISASI PENGELUARAN']);
            fputcsv($handle, ['Tanggal', 'Kategori', 'Deskripsi', 'Jumlah Realisasi (Rp)', 'Catatan']);

            foreach ($project->expenses as $expense) {
                fputcsv($handle, [
                    $expense->date->format('Y-m-d'),
                    $expense->category,
                    $expense->description,
                    $expense->amount,
                    $expense->notes,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
