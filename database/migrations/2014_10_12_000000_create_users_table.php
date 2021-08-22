<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('user_name')->unique();
            $table->string('avatar')->nullable();
            $table->string('email')->unique()->nullable();
            $table->enum('user_role', ['admin','user']);
            $table->string('password');
            $table->string('verification_code')->nullable();
            $table->datetime('verified_at')->nullable();
            $table->datetime('registered_at');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
