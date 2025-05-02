<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\ReceiptRecords;
use App\Http\Controllers\Controller;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // fetch all receipts query
        $searchTerm = $request->search;
        $column = $request->column;
        $direction = $request->direction;
        $per_page = $request->per_page;

        // query all receipts
        $query = Receipt::query();

        // total invoice and total customer payment used clone to avoid changing the original query
        $totalInvoice = (clone $query)->where('external_id', 'LIKE', '%INV-%')->count();
        $totalCustPay = (clone $query)->where('external_id', 'LIKE', '%CustPay-%')->count();

        // total receipts count
        $totalReceipts = $query->count();

        // fetch all receipts with when to apply search and sorting and pagination and per page
        $receipts = $query->when($column && $direction, fn($query) => $query->orderBy($column, $direction))
            ->when($searchTerm, fn($query) => $query->where("print_by", "LIKE", "%{$searchTerm}%")
                ->orWhere("external_id", "LIKE", "%{$searchTerm}%"))
            ->paginate($per_page);

        // fetch latest 10 receipts
        $latest_receipts = Receipt::latest()->take(10)->get();

        // get the total count of today and yesterday receipts
        $todays_receipts_count = Receipt::whereToday('created_at')->count();
        $yesterdays_receipts_count = Receipt::whereBeforeToday('created_at')->count();

        // get the total weekly and last week receipts counts
        $weekly_receipts_count = Receipt::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $last_weekly_receipts_count = Receipt::whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->count();

        // get the total count of monthly and last month receipts
        $monthly_receipts_count = Receipt::whereMonth('created_at', now())->count();
        $last_monthly_receipts_count = Receipt::whereMonth('created_at', now()->subMonth()->month)->count();

        // get the total count of total branch who have printed records
        $total_branch_print_records = Receipt::distinct('print_by')->count();

        // calculating percentage of todays receipts
        $todays_percentage = $yesterdays_receipts_count > 0 ? number_format($todays_receipts_count / $yesterdays_receipts_count * 100, 2) : number_format(100, 2);

        // calculating percentage of weekly receipts
        $weekly_percentage = $last_weekly_receipts_count > 0 ? number_format($weekly_receipts_count / $last_weekly_receipts_count * 100, 2) : number_format(100, 2);

        // calculating percentage of monthly receipts
        $monthly_percentage = $last_monthly_receipts_count > 0 ? number_format($monthly_receipts_count / $last_monthly_receipts_count * 100, 2) : number_format(100, 2);

        // get all receipts with external_id, print_count and re_print for existing receipt without pagination
        $searchingIfExists = Receipt::get(['external_id', 'print_count', 're_print']);

        return response()->json([
            'message'                      => "All receipts fetched successfully",
            'receipts'                     => $receipts,
            'latest_receipts'              => $latest_receipts,
            'total_receipts'               => $totalReceipts,
            'todays_receipts_count'        => $todays_receipts_count,
            'weekly_receipts_count'        => $weekly_receipts_count,
            'monthly_receipts_count'       => $monthly_receipts_count,
            'total_branch_print_records'   => $total_branch_print_records,
            'todays_percentage'            => $todays_percentage,
            'monthly_percentage'           => $monthly_percentage,
            'weekly_percentage'            => $weekly_percentage,
            'searching_if_exists'          => $searchingIfExists,
            'total_invoice'                => $totalInvoice,
            'total_cust_pay'               => $totalCustPay
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $existsReceipt = Receipt::where('external_id', $request->external_id)
            ->first();

        if ($existsReceipt && $existsReceipt->print_count >= 1 && $existsReceipt?->re_print === false) {

            return response()->json([
                'message'       => "{$request->external_id} receipt is already printed by {$request->print_by}",
            ], 400);
        }

        if ($request->external_id === null || $request->print_by === null) {
            return response()->json([
                'message'       => "Ops! Something went wrong. Please try again.",
            ], 400);
        }

        if ($existsReceipt || $existsReceipt?->re_print === true) {
            $existsReceipt->increment('print_count');
            $existsReceipt->update([
                're_print' => false
            ]);
            $receipt = $existsReceipt;
        } else {
            $receipt = Receipt::create([
                'external_id'       => $request->external_id,
                'print_by'          => $request->print_by,
                'print_count'       => 1
            ]);
        }


        ReceiptRecords::dispatch($receipt);

        return response()->json([
            'message'       => ucfirst($request->external_id) . " receipt is created successfully.",
            'receipt'       => $receipt
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $receiptRecord = Receipt::find($id);

        $validation = Validator::make($request->all(), [
            'external_id'       => ['required'],
            'print_by'          => ['required'],
            're_print'          => ['required']
        ]);

        if ($validation->fails()) {
            return response()->json([
                'errors'        => $validation->errors()
            ], 400);
        }

        if (!$receiptRecord) {
            return response()->json([
                'message'       => 'Receipt record not found',
            ], 404);
        }

        $receiptRecord->update([
            'external_id'       => $request->external_id,
            'print_by'          => $request->print_by,
            're_print'          => $request->re_print
        ]);

        return response()->json([
            'message'       => 'Receipt updated successfully',
        ], 204);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $receiptRecord = Receipt::find($id);

        if (!$receiptRecord) {
            return response()->json([
                'message'       => 'Receipt record not found',
            ], 404);
        }

        $receiptRecord->delete();

        return response()->json([
            'message'       => 'Receipt deleted successfully',
        ], 204);
    }
}
