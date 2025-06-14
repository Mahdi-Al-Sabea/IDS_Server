<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('action_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->longText("description");
            $table->enum("status",["Completed","Pending"]);
            $table->date("dueDate");

            $table->unsignedBigInteger("assignedTo");
            $table->foreign("assignedTo")->references("id")->on("users")->onDelete("cascade");

            $table->foreignId("minutes_of_meeting_id")->constrained()->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_items');
    }
};
