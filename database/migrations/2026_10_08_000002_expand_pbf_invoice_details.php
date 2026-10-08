<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('npwp', 40)->nullable();
        });
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->string('recipient_name')->nullable();
            $table->decimal('document_total', 16, 2)->nullable();
            $table->decimal('line_discount_total', 16, 2)->default(0);
            $table->decimal('net_items_total', 16, 2)->default(0);
            $table->decimal('dpp', 16, 2)->default(0);
            $table->decimal('other_dpp', 16, 2)->default(0);
            $table->string('tax_mode', 20)->default('manual');
            $table->decimal('tax_rate', 7, 3)->default(0);
            $table->string('attachment_path')->nullable();
            $table->string('attachment_filename')->nullable();
            $table->string('attachment_mime', 80)->nullable();
        });
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->string('zb_code', 30)->nullable();
            $table->decimal('line_gross', 16, 2)->default(0);
            $table->decimal('line_discount_percent', 7, 3)->default(0);
            $table->decimal('line_discount_amount', 16, 2)->default(0);
            $table->decimal('line_discount_total', 16, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', fn (Blueprint $table) => $table->dropColumn([
            'zb_code', 'line_gross', 'line_discount_percent', 'line_discount_amount', 'line_discount_total',
        ]));
        Schema::table('purchase_invoices', fn (Blueprint $table) => $table->dropColumn([
            'recipient_name', 'document_total', 'line_discount_total', 'net_items_total', 'dpp', 'other_dpp',
            'tax_mode', 'tax_rate', 'attachment_path', 'attachment_filename', 'attachment_mime',
        ]));
        Schema::table('suppliers', fn (Blueprint $table) => $table->dropColumn('npwp'));
    }
};
