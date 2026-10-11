<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('belanjas', function (Blueprint $table) {
            $table->string('review_status', 20)->default('draf')->index();
            $table
                ->foreignId('submitted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table
                ->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
        });
        Schema::create('lpj_attachments', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('belanja_id')
                ->constrained('belanjas')
                ->cascadeOnDelete();
            $table
                ->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('filename');
            $table->string('mime_type', 100);
            $table->unsignedInteger('size');
            $table->string('sha256', 64);
            // Bundled in encrypted database backups; never exposed through a public URL.
            $table->longText('contents_base64');
            $table->timestamps();
        });
        Schema::create('lpj_events', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('belanja_id')
                ->nullable()
                ->constrained('belanjas')
                ->nullOnDelete();
            $table
                ->foreignId('school_profile_id')
                ->constrained('school_profiles')
                ->restrictOnDelete();
            $table
                ->foreignId('actor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('actor_name');
            $table->string('action', 40);
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();
            $table->text('reason')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpj_events');
        Schema::dropIfExists('lpj_attachments');
        Schema::table('belanjas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('submitted_by');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn([
                'review_status',
                'submitted_at',
                'approved_at',
            ]);
        });
    }
};
