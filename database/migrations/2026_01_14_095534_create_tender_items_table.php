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
        Schema::create('tender_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tender_id');
            $table->unsignedBigInteger('division_id');
            $table->string('item_code', 100)->nullable();
            $table->string('hs_code', 100)->nullable();
            $table->text('item_name')->nullable();
            $table->string('item_unit', 100);
            $table->decimal('item_quantity', 20, 4);
            $table->decimal('item_rate', 20, 4);

            $table->timestamps();

            $table->foreign('tender_id')->references('id')->on('tenders')->onDelete('cascade');
            $table->foreign('division_id')->references('id')->on('tander_divisions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tender_items');
    }
};
