<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

        if ($existsReceipt && $existsReceipt->print_count === "1") {

            return response()->json([
                'message'       => "{$request->external_id} receipt is already printed by {$request->print_by}",
            ], 400);
        }

        if($request->external_id === null || $request->print_by === null) {
            return response()->json([
                'message'       => "Ops! Something went wrong. Please try again.",
            ], 400);
        }

        $receipt = Receipt::create([
            'external_id'       => $request->external_id,
            'print_by'          => $request->print_by,
            'print_count'       => 1
        ]);

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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
