<?php

namespace App\Http\Controllers\Api;

use App\Events\ReprintEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\RequestReprintRequest;
use App\Models\Receipt;
use App\Models\ReprintReason;
use Illuminate\Http\Request;

class RequestReprintController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $search = request('search');
        $per_page = request('per_page', 10);

        $data = ReprintReason::query()
            ->with('receipt')
            ->when(
                fn($q)
                =>
                $q->whereRelation('receipt', 'external_id', 'like', "%{$search}%")
                    ->orWhereLike('status', "%{$search}%")
            )
            ->latest()
            ->paginate($per_page);

        return response()->json([
            'message' => 'Reprint requests records fetched successfully',
            'data'    => $data
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RequestReprintRequest $request)
    {
        $request->validated();

        $receipt = Receipt::query()
            ->where('external_id', $request->external_id)
            ->first();

        $has_pending_request = $receipt->reprintReasons()
            ->where('status', 'pending')
            ->count() > 0;

        match (true) {
            !$receipt            => abort(404, 'Receipt not found'),
            $has_pending_request => abort(400, 'Reprint request already sent'),
            default              => null
        };

        $receipt->reprintReasons()->create([
            'reason' => $request->reason
        ]);

        return response()->noContent();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReprintReason $reprintReason)
    {
        $reprintReason->update([
            'status' => 'approved'
        ]);

        $reprintReason->receipt->update([
            're_print' => true
        ]);

        $data = [
            'message'     => "Hello, Your {$reprintReason->receipt->external_id} reprint request with reason of \"{$reprintReason->reason}\" has been approved successfully. You can now print the receipt.",
            'branch_code' => explode("-", $reprintReason->receipt->print_by)[0]
        ];

        ReprintEvent::dispatch($data);

        return response()->json([
            'message' => 'Reprint request approved successfully',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, ReprintReason $reprintReason)
    {
        $request->validate([
            'reason' => ['required', 'max:5000', 'min:2'],
        ]);

        $reprintReason->update([
            'status'        => 'rejected',
            'cancel_reason' => $request->reason
        ]);

        $data = [
            'message'     => "Hello, Your {$reprintReason->receipt->external_id} reprint request with reason of \"{$reprintReason->reason}\" has been rejected with cancel reason of \"{$reprintReason->cancel_reason}\". But you can request another for reprint by uploading again the receipt and read the instruction given where can submit a reprint request.",
            'branch_code' => explode("-", $reprintReason->receipt->print_by)[0]
        ];

        ReprintEvent::dispatch($data);

        return response()->json([
            'message' => 'Reprint request approved successfully',
        ], 200);
    }
}
