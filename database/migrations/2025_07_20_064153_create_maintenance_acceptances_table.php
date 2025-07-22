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
        Schema::create('maintenance_acceptances', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('maintenance_id');
            $table->unsignedInteger('assigned_to_id');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->string('signature_filename')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('maintenance_id')->references('id')->on('asset_maintenances')->onDelete('cascade');
            $table->foreign('assigned_to_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_acceptances');
    }
};
