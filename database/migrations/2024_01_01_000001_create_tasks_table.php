<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up():void {Schema::create('tasks',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('title',100);$t->text('description')->nullable();$t->enum('status',['pending','in_progress','done'])->default('pending');$t->date('due_date')->nullable();$t->boolean('is_archived')->default(false);$t->timestamps();});} public function down():void {Schema::dropIfExists('tasks');} };
