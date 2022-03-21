<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemActionsTable extends Migration
{

    public const CUSTOM_UNIQUE_KEY_FOR_ITEM_ACTIONS = 'custom_unique_key_for_item_actions';

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

            $table->unique(['itemId', 'actionName'], self::CUSTOM_UNIQUE_KEY_FOR_ITEM_ACTIONS);
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
