<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemActionAdditionalTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('itemActionAdditional', static function (Blueprint $table) {
            $table->integer('itemActionId')->unsigned();
            $table->foreign('itemActionId')->references('id')->on('itemActions')->onDelete('cascade')->onUpdate('cascade');
            $table->string('specialData');
            $table->string('description')->nullable();
            $table->timestamp('updatedAt');
            $table->timestamp('createdAt');

            $table->primary(['itemActionId', 'specialData']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itemActionAdditional');
    }
}
