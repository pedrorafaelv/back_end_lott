<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('raffles', function (Blueprint $table) {
            $table->timestamp('min_players_reached_at')->nullable()->after('start_date');
            $table->index('min_players_reached_at');
            $table->boolean('allow_bots')->nullable()->after('reward_full');
            $table->integer('minimun_user_play')->nullable()->after('maximun_user_play');
        });
    }
    public function down(): void {
        Schema::table('raffles', function (Blueprint $table) {
            $table->dropIndex(['min_players_reached_at']);
            $table->dropColumn('min_players_reached_at');
            $table->dropColumn('allow_bots');
            $table->dropColumn('minimun_user_play');
        });
    }
};