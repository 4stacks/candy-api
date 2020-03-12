<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use GetCandy\Api\Core\Products\Models\ProductFamily;
use Illuminate\Support\Facades\Schema;

class RemoveAttributeDataFromProductFamilyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $channel = DB::table('channels')->whereDefault(true)->first();

        $families = ProductFamily::all();

        foreach ($families as $family) {
            $name = $family->attribute_data['name'][$channel->handle]['en'];
            // Doing it like this to stop any setters/getters...
            DB::table('product_families')->whereId($family->id)->update([
                'name' => $name,
            ]);
        }
        Schema::table('product_families', function (Blueprint $table) {
            $table->dropColumn('attribute_data');
        });
    }

    public function down()
    {
    }
}
