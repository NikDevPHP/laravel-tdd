<?php

use CodePress\CodePost\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCodePostsTable extends Migration
{
    public function up()
    {
        Schema::create('codepress_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('slug');
            $table->text('content');
            $table->integer('state')->default(Post::STATE_DRAFT);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('codepress_posts');
    }
}
