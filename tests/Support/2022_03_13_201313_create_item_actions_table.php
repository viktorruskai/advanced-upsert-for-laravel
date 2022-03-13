<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemActionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('itemActions', static function (Blueprint $table) {
            $table->increments('id');
            $table->integer('itemId')->unsigned();
            $table->foreign('itemId')->references('id')->on('items')->onDelete('cascade')->onUpdate('cascade');
            $table->string('actionName');
            $table->string('actionDescription');
            $table->double('actionValue')->nullable();
            $table->timestamp('updatedAt');
            $table->timestamp('createdAt');

            $table->unique(['itemId', 'actionName']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itemActions');
    }
}
