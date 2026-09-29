<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers', 'gst_number')) {
                $table->string('gst_number', 32)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('suppliers', 'contact_person')) {
                $table->string('contact_person', 120)->nullable()->after('gst_number');
            }
            if (! Schema::hasColumn('suppliers', 'address')) {
                $table->text('address')->nullable()->after('contact_person');
            }
            if (! Schema::hasColumn('suppliers', 'pending_payment_amount')) {
                $table->decimal('pending_payment_amount', 14, 2)->default(0)->after('address');
            }
        });

        if (! Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number', 64)->unique();
                $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
                $table->date('purchase_date');
                $table->decimal('subtotal', 14, 2)->default(0);
                $table->decimal('tax_percent', 8, 2)->default(0);
                $table->decimal('tax_amount', 14, 2)->default(0);
                $table->decimal('total_amount', 14, 2)->default(0);
                $table->string('payment_status', 24)->default('pending');
                $table->text('notes')->nullable();
                $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['purchase_date', 'supplier_id']);
            });
        }

        if (! Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('quantity');
                $table->decimal('unit_price', 12, 2);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('line_total', 14, 2)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sales')) {
            Schema::create('sales', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number', 64)->unique();
                $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
                $table->string('customer_name');
                $table->string('customer_email')->nullable();
                $table->string('customer_phone', 32)->nullable();
                $table->date('sale_date');
                $table->decimal('subtotal', 14, 2)->default(0);
                $table->decimal('tax_amount', 14, 2)->default(0);
                $table->decimal('total_amount', 14, 2)->default(0);
                $table->string('payment_status', 24)->default('pending');
                $table->string('order_status', 24)->default('pending');
                $table->text('notes')->nullable();
                $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('stock_applied_at')->nullable();
                $table->timestamps();
                $table->index(['sale_date', 'payment_status', 'order_status']);
            });
        }

        if (! Schema::hasTable('sale_items')) {
            Schema::create('sale_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('quantity');
                $table->decimal('unit_price', 12, 2);
                $table->decimal('line_total', 14, 2)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('warehouse_transfers')) {
            Schema::create('warehouse_transfers', function (Blueprint $table) {
                $table->id();
                $table->string('reference', 64)->unique();
                $table->foreignId('from_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->foreignId('to_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->string('status', 24)->default('completed');
                $table->text('notes')->nullable();
                $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('warehouse_transfer_lines')) {
            Schema::create('warehouse_transfer_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_transfer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('quantity');
                $table->timestamps();
            });
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_movements', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('product_variant_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('stock_movements', 'purchase_id')) {
                $table->foreignId('purchase_id')->nullable()->after('return_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('stock_movements', 'sale_id')) {
                $table->foreignId('sale_id')->nullable()->after('purchase_id')->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            if (Schema::hasColumn('stock_movements', 'sale_id')) {
                $table->dropConstrainedForeignId('sale_id');
            }
            if (Schema::hasColumn('stock_movements', 'purchase_id')) {
                $table->dropConstrainedForeignId('purchase_id');
            }
            if (Schema::hasColumn('stock_movements', 'warehouse_id')) {
                $table->dropConstrainedForeignId('warehouse_id');
            }
        });

        Schema::dropIfExists('warehouse_transfer_lines');
        Schema::dropIfExists('warehouse_transfers');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');

        Schema::table('suppliers', function (Blueprint $table) {
            foreach (['pending_payment_amount', 'address', 'contact_person', 'gst_number'] as $column) {
                if (Schema::hasColumn('suppliers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
