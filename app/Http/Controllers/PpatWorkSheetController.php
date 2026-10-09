<?php

namespace App\Http\Controllers;

use App\Models\WorkSheet;
use App\Models\WorkSheetFile;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PpatWorkSheetController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = WorkSheet::query()
            ->with(['order.service', 'order.client'])
            ->whereHas('order', function ($orders) {
                $orders->whereHas('service', fn ($service) => $service->where('service_type', 'PPAT'))
                    ->orWhere(fn ($custom) => $custom->whereNull('service_id')->whereNotNull('other_service'));
            });

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->editColumn('deed_date', fn (WorkSheet $sheet) => $sheet->deed_date?->format('d/m/Y') ?? '-')
                ->editColumn('deed_name', fn (WorkSheet $sheet) => $sheet->order?->service_name_snapshot ?: ($sheet->deed_name ?: '-'))
                ->addColumn('order_title', fn (WorkSheet $sheet) => $sheet->order?->title ?? '-')
                ->addColumn('client_name', fn (WorkSheet $sheet) => $sheet->order?->client_name_snapshot ?? '-')
                ->addColumn('party_one_name', fn (WorkSheet $sheet) => data_get($sheet->party_one, 'name') ?: '-')
                ->addColumn('party_two_name', fn (WorkSheet $sheet) => data_get($sheet->party_two, 'name') ?: '-')
                ->addColumn('certificate_status', fn (WorkSheet $sheet) => filled($sheet->certificate_details) ? 'Terisi' : 'Belum')
                ->addColumn('tax_status', fn (WorkSheet $sheet) => [
                    'pph' => $sheet->pph_amount !== null,
                    'bphtb' => $sheet->bphtb_amount !== null,
                ])
                ->addColumn('code_label', fn (WorkSheet $sheet) => $sheet->code)
                ->editColumn('code', fn (WorkSheet $sheet) => view('ppat.work-sheets.code', compact('sheet'))->render())
                ->addColumn('actions', fn (WorkSheet $sheet) => view('ppat.work-sheets.action', compact('sheet'))->render())
                ->rawColumns(['code', 'actions'])
                ->toJson();
        }

        return view('ppat.work-sheets.index', [
            'role' => 'PPAT',
            'dashboardRoute' => 'ppat.dashboard',
        ]);
    }

    public function show(WorkSheet $workSheet): View
    {
        $workSheet->load(['order.service', 'order.client', 'order.creator', 'files']);
        abort_unless($this->isPpatSheet($workSheet), 404);

        return view('ppat.work-sheets.show', [
            'role' => 'PPAT',
            'dashboardRoute' => 'ppat.dashboard',
            'sheet' => $workSheet,
        ]);
    }

    public function downloadQr(WorkSheet $workSheet)
    {
        $workSheet->load('order.service');
        abort_unless($this->isPpatSheet($workSheet), 404);

        $path = route('ppat.work-sheets.show', ['workSheet' => $workSheet->code], false);
        $url = rtrim(config('app.url'), '/').'/'.ltrim($path, '/');
        $qrCode = new QrCode(
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 480,
            margin: 16,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        $png = (new PngWriter())->write($qrCode)->getString();
        $image = imagecreatefromstring($png);
        abort_if($image === false, 500, 'QR code gagal dibuat.');

        ob_start();
        imagejpeg($image, null, 100);
        $jpeg = ob_get_clean();
        imagedestroy($image);

        return response($jpeg, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="'.$workSheet->code.'-QR.jpg"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function update(Request $request, WorkSheet $workSheet): RedirectResponse|JsonResponse
    {
        $workSheet->load('order.service');
        abort_unless($this->isPpatSheet($workSheet), 404);

        $validated = $request->validate([
            'deed_date' => ['nullable', 'date'],
            'deed_number' => ['nullable', 'string', 'max:100'],
            'party_one.type' => ['nullable', Rule::in(['perorangan', 'badan_usaha', 'lembaga_non_ahu'])],
            'party_one.name' => ['nullable', 'string', 'max:255'],
            'party_one.birth_info' => ['nullable', 'string', 'max:255'],
            'party_one.identity' => ['nullable', 'string', 'max:100'],
            'party_one.address' => ['nullable', 'string', 'max:2000'],
            'party_two.type' => ['nullable', Rule::in(['perorangan', 'badan_usaha', 'lembaga_non_ahu'])],
            'party_two.name' => ['nullable', 'string', 'max:255'],
            'party_two.birth_info' => ['nullable', 'string', 'max:255'],
            'party_two.identity' => ['nullable', 'string', 'max:100'],
            'party_two.address' => ['nullable', 'string', 'max:2000'],
            'certificate_details' => ['nullable', 'string', 'max:5000'],
            'transaction_value' => ['nullable', 'integer', 'min:0'],
            'pbb_amount' => ['nullable', 'integer', 'min:0'],
            'bphtb_amount' => ['nullable', 'integer', 'min:0'],
            'pph_amount' => ['nullable', 'integer', 'min:0'],
            'documents' => ['sometimes', 'array', 'max:20'],
            'documents.*.category' => ['required_with:documents.*.file', Rule::in(['akta', 'party_one', 'party_two', 'certificate', 'pbb', 'bphtb', 'pph', 'other'])],
            'documents.*.label' => ['required_with:documents.*.file', 'string', 'max:255'],
            'documents.*.file' => ['required_with:documents.*.label', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ], [
            'documents.*.file.mimes' => 'Berkas harus berformat PDF, DOC, atau DOCX.',
            'documents.*.file.max' => 'Ukuran setiap berkas maksimal 10 MB.',
        ]);

        $workSheet->update([
            'deed_date' => $validated['deed_date'] ?? null,
            'deed_number' => $validated['deed_number'] ?? null,
            'party_one' => $this->cleanParty($validated['party_one'] ?? []),
            'party_two' => $this->cleanParty($validated['party_two'] ?? []),
            'certificate_details' => $validated['certificate_details'] ?? null,
            'transaction_value' => $validated['transaction_value'] ?? null,
            'pbb_amount' => $validated['pbb_amount'] ?? null,
            'bphtb_amount' => $validated['bphtb_amount'] ?? null,
            'pph_amount' => $validated['pph_amount'] ?? null,
        ]);

        foreach ($request->file('documents', []) as $index => $document) {
            $file = $document['file'] ?? null;
            if (! $file) {
                continue;
            }
            $path = $file->store("work-sheet-files/{$workSheet->id}", 'local');
            $workSheet->files()->create([
                'category' => $validated['documents'][$index]['category'],
                'label' => $validated['documents'][$index]['label'],
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Lembar kerja berhasil disimpan.']);
        }

        return back()->with('success', 'Lembar kerja berhasil diperbarui.');
    }

    public function previewFile(WorkSheetFile $workSheetFile)
    {
        $workSheetFile->load('workSheet.order.service');
        abort_unless($workSheetFile->workSheet && $this->isPpatSheet($workSheetFile->workSheet), 404);
        abort_unless(Storage::disk('local')->exists($workSheetFile->path), 404);
        $isPdf = strtolower(pathinfo($workSheetFile->original_name, PATHINFO_EXTENSION)) === 'pdf';

        return Storage::disk('local')->response($workSheetFile->path, $workSheetFile->original_name, [
            'Content-Type' => $workSheetFile->mime_type ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ], $isPdf ? 'inline' : 'attachment');
    }

    private function cleanParty(array $party): ?array
    {
        $party = array_filter($party, fn ($value) => filled($value));
        return $party ?: null;
    }

    private function isPpatSheet(WorkSheet $workSheet): bool
    {
        return $workSheet->order?->service?->service_type === 'PPAT'
            || ($workSheet->order?->service_id === null && filled($workSheet->order?->other_service));
    }
}
