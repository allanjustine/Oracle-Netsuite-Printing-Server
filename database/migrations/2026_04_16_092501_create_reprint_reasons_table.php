<?php

use App\Models\Receipt;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reprint_reasons', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Receipt::class)->constrained()->cascadeOnDelete();
            $table->text('reason');
            $table->text('cancel_reason')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reprint_reasons');
    }
};
