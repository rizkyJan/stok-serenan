<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('petugas');
            $table->boolean('is_active')->default(true);
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 40)->nullable();
            $table->string('contact_name')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 60)->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('unit', 50)->default('pcs'); // satuan stok terkecil
            $table->unsignedInteger('minimum_stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('name');
        });
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('invoice_no', 100);
            $table->date('invoice_date');
            $table->date('received_date');
            $table->date('due_date');
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('discount', 16, 2)->default(0);
            $table->decimal('tax', 16, 2)->default(0);
            $table->decimal('shipping', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->decimal('paid_amount', 16, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['supplier_id', 'invoice_no']);
            $table->index('due_date');
        });
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('batch_number', 100);
            $table->date('expires_at')->nullable();
            $table->unsignedInteger('quantity_received');
            $table->unsignedInteger('quantity_available');
            $table->decimal('unit_cost', 16, 4); // per satuan terkecil
            $table->timestamps();
            $table->index(['product_id', 'expires_at']);
        });
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_batch_id')->unique()->constrained()->restrictOnDelete();
            $table->unsignedInteger('purchase_quantity');
            $table->string('purchase_unit', 50);
            $table->unsignedInteger('unit_multiplier');
            $table->unsignedInteger('quantity_base');
            $table->decimal('purchase_unit_cost', 16, 2);
            $table->decimal('line_total', 16, 2);
            $table->timestamps();
        });
        Schema::create('stock_outs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity'); // satuan stok terkecil
            $table->string('reason', 50);
            $table->string('recipient')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_batch_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('old_quantity');
            $table->unsignedInteger('new_quantity');
            $table->integer('delta');
            $table->text('reason');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_batch_id')->constrained()->restrictOnDelete();
            $table->string('type', 30); // in, out, adjustment_in, adjustment_out
            $table->integer('quantity_change');
            $table->string('reference_type', 50);
            $table->unsignedBigInteger('reference_id');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['product_id', 'created_at']);
        });
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained()->restrictOnDelete();
            $table->date('paid_at');
            $table->decimal('amount', 16, 2);
            $table->string('method', 30);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['supplier_payments','stock_movements','stock_adjustments','stock_outs','purchase_items','product_batches','purchase_invoices','products','suppliers'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['role','is_active']));
    }
};
