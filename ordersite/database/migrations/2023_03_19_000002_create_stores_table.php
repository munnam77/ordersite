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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->integer('store_id')->unique()->comment('店舗ID');
            $table->string('prefectures')->comment('都道府県');
            $table->string('store_name')->comment('店舗名');
            $table->string('login_id')->unique()->comment('ログインID');
            $table->string('login_password')->comment('ログインパスワード');
            $table->boolean('admin')->default(0)->comment('管理者フラグ');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
}; 