<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('file_name')->comment('文件名称');
            $table->string('file_id')->comment('文件id');
            $table->string('order_number')->comment('订单编号');
            $table->string('logistics_number')->comment('物流单号')->default('空');
            $table->string('solitaire_number')->comment('接龙号');
            $table->string('goods')->comment('商品名称');
            $table->string('count')->comment('商品数量');
            $table->string('logistics')->comment('物流公司')->default('空');
            $table->string('phone')->comment('电话');
            $table->string('receiver')->comment('收货人');
            $table->string('province')->comment('省')->default('空');
            $table->string('city')->comment('市')->default('空');
            $table->string('area')->comment('区')->default('空');
            $table->string('address')->comment('详细地址');
            $table->string('remarks')->comment('备注')->default('空');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
