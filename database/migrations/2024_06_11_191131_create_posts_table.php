<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->string('title_en')->index();
            $table->string('slug')->unique();
            $table->string('slug_en')->unique();
            $table->text('description');
            $table->text('description_en');
            $table->unsignedTinyInteger('status')->default(0);
            $table->string('post_type')->default('post');
            $table->unsignedTinyInteger('comment_able')->default(1);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Specify key length for the description and description_en indexes
            $table->index([DB::raw('description(255)')], 'posts_description_index');
            $table->index([DB::raw('description_en(255)')], 'posts_description_en_index');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
