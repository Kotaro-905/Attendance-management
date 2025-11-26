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
            $table->id();                         // unsigned bigint, PK
            $table->string('name');               // 氏名
            $table->string('email')->unique();    // ログイン用メールアドレス
            $table->timestamp('email_verified_at')->nullable(); // メール認証日時
            $table->string('password');           // ハッシュ化パスワード

            // ★ 追加：権限区別用（0: 一般ユーザー, 1: 管理者）
            $table->tinyInteger('role')->default(0);

            $table->rememberToken();              // remember_token
            $table->timestamps();                 // created_at, updated_at
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