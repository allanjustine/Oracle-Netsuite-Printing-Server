<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\ReferenceNumberStatusEvent;
use App\Http\Controllers\Controller;
use App\Events\ReceiptRecords;
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
        // Lista natu tanan date filter ari aron dile libog!
        $yesterday = now()->yesterday();
        $startOfTheWeek = now()->startOfWeek();
        $endOfTheWeek = now()->endOfWeek();
        $startOfLastWeek = now()->subWeek()->startOfWeek();
        $endOfLastWeek = now()->subWeek()->endOfWeek();
        $thisMonth = now()->month;
        $thisYear = now()->year;
        $thisLastMonth = now()->subMonth()->month;
        $thisLastMonthYear = now()->subMonth()->year;

        // Kuhaon ang query para mag clone rata testing ug okay ba! hahays buhayts!
        $query = Receipt::query();

        // total invoice and total customer payment used clone to avoid changing the original query
        $totalInvoice = (clone $query)
            ->where('external_id', 'LIKE', '%INV-%')
            ->count();

        $totalCustPay = (clone $query)
            ->whereNot('external_id', 'LIKE', '%INV-%')
            ->count();

        // sum all total invoice and total cr
        $totalInvoiceSum = (clone $query)
            ->where('external_id', 'LIKE', '%INV-%')
            ->sum('total_amount_due');

        $totalCustPaySum = (clone $query)
            ->whereNot('external_id', 'LIKE', '%INV-%')
            ->sum('total_amount_due');

        // total receipts count
        $totalReceipts = (clone $query)->count();

        // fetch latest 10 receipts
        $latest_receipts = (clone $query)->latest()->take(10)->get(['id', 'external_id', 'print_count', 'print_by', 'total_amount_due', 'created_at']);

        // get the total count of today and yesterday receipts
        $todays_receipts_count = (clone $query)->whereToday('created_at')->count();
        $yesterdays_receipts_count = (clone $query)->whereDate('created_at', $yesterday)->count();

        // get the total weekly and last week receipts counts
        $weekly_receipts_count = (clone $query)->whereBetween('created_at', [$startOfTheWeek, $endOfTheWeek])->count();
        $last_weekly_receipts_count = (clone $query)->whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])->count();

        // get the total count of monthly and last month receipts
        $monthly_receipts_count = (clone $query)->whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear)->count();
        $last_monthly_receipts_count = (clone $query)->whereMonth('created_at', $thisLastMonth)->whereYear('created_at', $thisLastMonthYear)->count();

        // get the total count of total branch who have printed records
        $total_branch_print_records = (clone $query)->distinct('print_by')->count();

        // calculating percentage of todays receipts
        $todays_percentage = $yesterdays_receipts_count > 0 ? number_format(($todays_receipts_count - $yesterdays_receipts_count) / $yesterdays_receipts_count * 100, 2) : number_format(100, 2);

        // calculating percentage of weekly receipts
        $weekly_percentage = $last_weekly_receipts_count > 0 ? number_format(($weekly_receipts_count - $last_weekly_receipts_count) / $last_weekly_receipts_count * 100, 2) : number_format(100, 2);

        // calculating percentage of monthly receipts
        $monthly_percentage = $last_monthly_receipts_count > 0 ? number_format(($monthly_receipts_count - $last_monthly_receipts_count) / $last_monthly_receipts_count * 100, 2) : number_format(100, 2);

        // sum all total sales of over all receipts
        $overAllTotalAmountDue = (clone $query)->sum('total_amount_due');

        // sum todays total sales of receipts
        $todaysTotalAmountDue = (clone $query)->whereToday('created_at')->sum('total_amount_due');

        $datas = [
            'latest_receipts'              => $latest_receipts,
            'total_receipts'               => $totalReceipts,
            'todays_receipts_count'        => $todays_receipts_count,
            'weekly_receipts_count'        => $weekly_receipts_count,
            'monthly_receipts_count'       => $monthly_receipts_count,
            'total_branch_print_records'   => $total_branch_print_records,
            'todays_percentage'            => $todays_percentage,
            'monthly_percentage'           => $monthly_percentage,
            'weekly_percentage'            => $weekly_percentage,
            'total_invoice'                => $totalInvoice,
            'total_cust_pay'               => $totalCustPay,
            'yesterdays_receipts_count'    => $yesterdays_receipts_count,
            'last_weekly_receipts_count'   => $last_weekly_receipts_count,
            'last_monthly_receipts_count'  => $last_monthly_receipts_count,
            'over_all_total_amount_due'    => number_format($overAllTotalAmountDue, 2, ".", ","),
            'sum_invoice'                  => number_format($totalInvoiceSum, 2, ".", ","),
            'sum_cust_pay'                 => number_format($totalCustPaySum, 2, ".", ","),
            'todays_total_amount_due'      => number_format($todaysTotalAmountDue, 2, ".", ","),
            'most_print_count_branch'      => $this->mostPrintCountBranch()
        ];


        return response()->json([
            'message' => "All receipts fetched successfully",
            'datas'   => $datas
        ], 200);
    }

    public function getReceiptRecords()
    {
        // fetch all receipts query
        $searchTerm = request('search');
        $column = request('column');
        $direction = request('direction');
        $per_page = request('per_page');

        // query all receipts
        $receipts = Receipt::query()
            ->when(
                $column && $direction,
                fn($query)
                =>
                $query->orderBy($column, $direction)
            )
            ->when(
                $searchTerm,
                fn($query)
                =>
                $query->whereAny([
                    "print_by",
                    "external_id",
                    'customer',
                    'total_amount_due'
                ], "LIKE", "%{$searchTerm}%")
            )
            ->paginate($per_page);

        return response()->json([
            'message'  => "Receipt records fetched successfully",
            "receipts" => $receipts
        ], 200);
    }

    public function existsPrint()
    {
        $external_id = request('external_id', '');

        $searchingIfExists = Receipt::where('external_id', $external_id)
            ->where(
                fn($q)
                =>
                $q->where('print_count', ">=", 1)
                    ->where('re_print', false)
                    ->whereRelation('reprintReasons', 'status', 'pending')
            )
            ->exists();

        return response()->json([
            'message'             => "Checking data...",
            'searching_if_exists' => $searchingIfExists
        ], 200);
    }

    private function mostPrintCountBranch()
    {
        $mostPrintCountBranch = Receipt::query()
            ->where('print_count', '>', 1)
            ->orderBy("print_count", 'desc')
            ->take(10)
            ->get(['external_id', 'print_count', 'print_by', 'updated_at']);

        return $mostPrintCountBranch;
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

        $is_exists_branch = Receipt::query()->where('print_by', $request->print_by)->exists();

        if ($existsReceipt && $existsReceipt->print_count >= 1 && $existsReceipt?->re_print === false) {

            return response()->json([
                'message'       => "{$request->external_id} receipt is already printed by {$request->print_by}",
            ], 400);
        }

        if ($existsReceipt && $existsReceipt?->reprintReasons()?->first()?->status === "pending") {
            return response()->json([
                'message'    => "{$request->external_id} receipt is still pending for reprint please try again later",
                'is_pending' => true
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
                're_print'          => false,
                'total_amount_due'  => $request->total_amount_due,
                'customer'          => $request->customer
            ]);
            $existsReceipt->reprintReasons()
                ->first()
                ->update([
                    'status' => 'completed'
                ]);

            $receipt = $existsReceipt;
        } else {
            $receipt = Receipt::create([
                'external_id'       => $request->external_id,
                'print_by'          => $request->print_by,
                'print_count'       => 1,
                'total_amount_due'  => $request->total_amount_due,
                'customer'          => $request->customer
            ]);
        }

        $receipt["re_print"] = false;

        $data = [
            'receipt'          => $receipt,
            'is_exists_branch' => $is_exists_branch
        ];

        ReceiptRecords::dispatch($data);

        return response()->json([
            'message' => ucfirst($request->external_id) . " receipt is created successfully.",
            'receipt' => $receipt
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

        if ($receiptRecord->re_print === $request->re_print) {
            $print = $request->re_print ? 're-printed' : 'not re-printed';
            return response()->json([
                'message'       => "Ops! this receipt is already set as {$print}",
            ], 400);
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

    public function submit(Request $request)
    {
        $branch_code = $request->branch_code;
        $current_reference_number = $request->current_reference_number;
        $next_reference_number = (int) $current_reference_number + 1;

        if ((int) $current_reference_number <= 0) {
            return response()->json([
                'message'       => 'Ops! current reference number must not be less than or equal to 0',
            ], 400);
        }

        ReferenceNumberStatusEvent::dispatch($branch_code, $current_reference_number, $next_reference_number);

        return response()->json([
            'message'       => 'Next reference number submitted successfully',
        ], 200);
    }
}
