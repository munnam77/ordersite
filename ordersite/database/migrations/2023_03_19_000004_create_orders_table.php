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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->comment('店舗ID');
            $table->unsignedBigInteger('schedule_id')->comment('スケジュールID');
            $table->string('schedule_name')->comment('スケジュール名');
            $table->float('p_quantity')->comment('発注数量');
            $table->text('comment')->nullable()->comment('コメント');
            $table->date('delivery_date')->nullable()->comment('配送日');
            $table->string('vehicle')->nullable()->comment('車両');
            $table->dateTime('working_day')->nullable()->comment('作業日');
            $table->time('working_time')->nullable()->comment('作業時間');
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
}; 