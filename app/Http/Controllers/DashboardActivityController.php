<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DailyNote;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardActivityController extends Controller
{
    /** GET /dashboard/activity?date=YYYY-MM-DD&limit=5 */
    public function index(Request $r)
    {
        $date = $r->query('date', now()->toDateString());
        $limit = (int) $r->query('limit', 5);

        $rows = ActivityLog::whereDate('log_date', $date)
            ->latest('created_at')
            ->limit($limit ?: 5)
            ->get(['id','source','action','item_name','qty_change','note','created_at']);

        return response()->json($rows);
    }

    /** POST /activity  (dipanggil dari halaman Inventory via fetch) */
    public function store(Request $r)
    {
        $data = $r->validate([
            'source'     => 'required|string',
            'action'     => 'required|string',
            'item_name'  => 'nullable|string',
            'qty_change' => 'nullable|integer',
            'note'       => 'nullable|string',
            'meta'       => 'nullable|array',
        ]);

        $row = ActivityLog::create(array_merge($data, [
            'log_date' => now()->toDateString(),
        ]));

        return response()->json(['ok' => true, 'id' => $row->id]);
    }

    /** GET /dashboard/activity/export?date=YYYY-MM-DD  → CSV */
    public function export(Request $r): StreamedResponse
    {
        $date = $r->query('date', now()->toDateString());
        $filename = "activity_{$date}.csv";

        $rows = ActivityLog::whereDate('log_date', $date)
            ->orderBy('created_at', 'asc')
            ->get();

        $headers = ['Content-Type' => 'text/csv'];
        return response()->streamDownload(function() use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['created_at','source','action','item_name','qty_change','note']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->created_at->format('Y-m-d H:i:s'),
                    $r->source, $r->action, $r->item_name, $r->qty_change, $r->note
                ]);
            }
            fclose($out);
        }, $filename, $headers);
    }

    /** GET /dashboard/notes?date=YYYY-MM-DD → {content:""} */
    public function getNote(Request $r)
    {
        $date = $r->query('date', now()->toDateString());
        $note = DailyNote::firstOrCreate(['note_date' => $date], ['content' => '']);
        return response()->json(['content' => $note->content ?? '']);
    }

    /** POST /dashboard/notes (body: {content}) → simpan catatan hari ini */
    public function saveNote(Request $r)
    {
        $data = $r->validate(['content' => 'nullable|string']);
        $note = DailyNote::firstOrCreate(['note_date' => now()->toDateString()]);
        $note->content = $data['content'] ?? '';
        $note->save();

        return response()->json(['ok' => true]);
    }

    /** POST /dashboard/notes/import (file CSV 1 kolom: content) */
    public function importNoteCsv(Request $r)
    {
        $r->validate(['file' => 'required|file|mimes:csv,txt']);
        $text = file_get_contents($r->file('file')->getRealPath());
        // join semua baris jadi satu paragraf
        $lines = array_map('trim', preg_split("/\\r\\n|\\r|\\n/", $text));
        $content = implode("\n", array_filter($lines, fn($x)=>$x!==''));

        $note = DailyNote::firstOrCreate(['note_date' => now()->toDateString()]);
        $note->content = $content;
        $note->save();

        return response()->json(['ok' => true, 'length' => strlen($content)]);
    }
}
