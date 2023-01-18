<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->integer('user_id');
            $table->string('user_name'); 
            $table->integer('department_id');
            $table->string('type'); 
            $table->string('slug')->unique();
            $table->text('title');
            $table->text('abstract')->nullable();
            $table->string('url')->nullable();
            $table->string('affiliation')->nullable();
            $table->string('attachment')->nullable();
            $table->boolean('isApproved')->default(0);
            $table->string('attachment')->nullable();
            $table->timestamp('conference')->nullable();
            $table->date('publication_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
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
        Schema::dropIfExists('posts');
    }
}
