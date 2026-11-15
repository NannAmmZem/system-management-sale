<?php

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
        Schema::table('sales', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->unique()->after('order_number');
            $table->timestamp('invoice_date')->nullable()->after('status');
            $table->timestamp('due_date')->nullable()->after('invoice_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'invoice_date', 'due_date']);
        });
    }
};
