<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->string('discount_mode', 20)->default('combined');
            $table->date('settlement_date')->nullable();
        });
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('selling_unit_price', 16, 2)->nullable();
        });
        Schema::create('purchase_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('invoice_no', 100)->nullable();
            $table->json('payload');
            $table->timestamps();
            $table->index(['created_by', 'updated_at']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('purchase_drafts');
        Schema::table('purchase_items', fn (Blueprint $table) => $table->dropColumn('selling_unit_price'));
        Schema::table('purchase_invoices', fn (Blueprint $table) => $table->dropColumn(['discount_mode','settlement_date']));
    }
};
