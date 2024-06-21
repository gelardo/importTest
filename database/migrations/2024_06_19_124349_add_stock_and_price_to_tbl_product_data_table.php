<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tblProductData', function (Blueprint $table) {
            $table->integer('stock_level')->unsigned()->nullable();
            $table->decimal('price', 8, 2)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('tblProductData', function (Blueprint $table) {
            $table->dropColumn('stock_level');
            $table->dropColumn('price');
        });
    }
    
};
