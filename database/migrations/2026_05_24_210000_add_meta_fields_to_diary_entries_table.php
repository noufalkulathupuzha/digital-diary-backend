<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diary_entries', function (Blueprint $table): void {
            $table->string('weather', 50)->nullable()->after('mood');
            $table->string('location')->nullable()->after('weather');
            $table->boolean('is_favorite')->default(false)->after('location');
            $table->boolean('is_draft')->default(false)->after('is_favorite');
            $table->json('attachments')->nullable()->after('tags');
        });
    }

    public function down(): void
    {
        Schema::table('diary_entries', function (Blueprint $table): void {
            $table->dropColumn(['weather', 'location', 'is_favorite', 'is_draft', 'attachments']);
        });
    }
};
