<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('works', function (Blueprint $table) {
 	    $table->bigIncrements('id');
            $table->string('work_id')->default(0);
            $table->string('status')->comment('状态')->default(1);
            $table->string('supplier')->comment('供应商')->default('错误数据');
            $table->json('files')->comment('文件名称');
            $table->string('wx_id')->comment('微信群ID')->default('0');
            $table->string('export_url')->comment('导出模版链接')->default('错误数据');
            $table->string('order_count')->comment('订单数')->default('错误数据');
            $table->string('reback_count')->comment('回执单数')->default('错误数据');
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
        Schema::dropIfExists('works');
    }
}
