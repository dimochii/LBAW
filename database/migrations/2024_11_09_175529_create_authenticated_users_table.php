<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthenticatedUsersTable extends Migration
{
    public function up()
    {
        Schema::create('authenticatedUser', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->string('username')->unique();    // Customize as needed
            $table->integer('reputation')->default(0);
            $table->boolean('isSuspended')->default(false);
            $table->boolean('isAdmin')->default(false);
            $table->timestamp('creationDate')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('authenticatedUser');
    }
}
