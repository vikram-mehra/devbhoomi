<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PincodeServiceability;
use App\Services\PincodeCsvImporter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PincodeServiceabilityAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $status = (string) $request->get('status', '');
        $rows = $this->filteredQuery($request)
            ->orderBy('pincode')
            ->paginate(30)
            ->withQueryString();

        return view('admin.pincode-serviceability', compact('rows', 'q', 'status'));
    }

    public function store(Request $request)
    {
        PincodeServiceability::create($this->validated($request));

        return back()->with('status', __('Pincode mapping created.'));
    }

    public function update(Request $request, PincodeServiceability $pincode)
    {
        $pincode->update($this->validated($request, $pincode));

        return back()->with('status', __('Pincode mapping updated.'));
    }

    public function destroy(PincodeServiceability $pincode)
    {
        $pincode->delete();

        return back()->with('status', __('Pincode mapping deleted.'));
    }

    public function toggle(PincodeServiceability $pincode)
    {
        $pincode->update(['status' => ! $pincode->status]);

        return back()->with('status', __('Serviceability status updated.'));
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'pincode-serviceability-'.now()->format('Ymd-His').'.csv';
        $rows = $this->filteredQuery($request)->orderBy('pincode')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['pincode', 'city', 'state', 'day_offset', 'courier_name', 'status']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->pincode,
                    $row->city,
                    $row->state,
                    $row->day_offset,
                    $row->courier_name,
                    $row->status ? 1 : 0,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function template(): StreamedResponse
    {
        $filename = 'pincode-serviceability-template.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['pincode', 'city', 'state', 'day_offset', 'courier_name', 'status']);
            fputcsv($out, ['263645', 'Haldwani', 'Uttarakhand', '3', 'Delhivery', '1']);
            fputcsv($out, ['201307', 'Noida', 'Uttar Pradesh', '2', 'Delhivery', '1']);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(Request $request, PincodeCsvImporter $importer)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $result = $importer->import($request->file('file')->getRealPath());
        $message = __('Imported :created new and updated :updated pincodes.', [
            'created' => $result['created'],
            'updated' => $result['updated'],
        ]);
        if ($result['skipped'] > 0) {
            $message .= ' '.__(':count row(s) skipped.', ['count' => $result['skipped']]);
        }

        return back()
            ->with('status', $message)
            ->with('pincode_import_errors', array_slice($result['errors'], 0, 20));
    }

    protected function filteredQuery(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $status = (string) $request->get('status', '');

        return PincodeServiceability::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('pincode', 'like', '%'.$q.'%')
                        ->orWhere('city', 'like', '%'.$q.'%')
                        ->orWhere('state', 'like', '%'.$q.'%')
                        ->orWhere('courier_name', 'like', '%'.$q.'%');
                });
            })
            ->when(in_array($status, ['0', '1'], true), function ($query) use ($status) {
                $query->where('status', $status === '1');
            });
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?PincodeServiceability $row = null): array
    {
        $data = $request->validate([
            'pincode' => [
                'required',
                'digits:6',
                Rule::unique('pincode_serviceabilities', 'pincode')->ignore($row?->id),
            ],
            'city' => 'required|string|max:120',
            'state' => 'required|string|max:120',
            'status' => 'required|boolean',
            'day_offset' => 'required|integer|min:0|max:30',
            'courier_name' => 'nullable|string|max:120',
        ]);

        $data['pincode'] = PincodeServiceability::normalizePincode($data['pincode']);
        $data['status'] = (bool) $data['status'];

        return $data;
    }
}
