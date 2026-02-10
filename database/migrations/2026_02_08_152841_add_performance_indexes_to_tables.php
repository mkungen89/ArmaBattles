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
        // Player Stats - Critical for leaderboard queries
        Schema::table('player_stats', function (Blueprint $table) {
            // Individual indexes for leaderboard sorting
            $table->index('kills', 'idx_player_stats_kills');
            $table->index('deaths', 'idx_player_stats_deaths');
            $table->index('xp_total', 'idx_player_stats_xp_total');
            $table->index('total_distance', 'idx_player_stats_total_distance');
            $table->index('playtime_seconds', 'idx_player_stats_playtime_seconds');
            $table->index('headshots', 'idx_player_stats_headshots');
            $table->index('total_roadkills', 'idx_player_stats_total_roadkills');

            // Composite index for K/D ratio calculations and filtering
            $table->index(['player_uuid', 'kills', 'deaths'], 'idx_player_stats_uuid_kills_deaths');

            // Index for active player queries
            $table->index('last_seen_at', 'idx_player_stats_last_seen_at');
        });

        // Player Kills - For recent kills and queries
        Schema::table('player_kills', function (Blueprint $table) {
            $table->index('created_at', 'idx_player_kills_created_at');
            $table->index('is_headshot', 'idx_player_kills_is_headshot');
            $table->index('is_roadkill', 'idx_player_kills_is_roadkill');

            // Composite for victim queries
            $table->index(['victim_uuid', 'created_at'], 'idx_player_kills_victim_created');
        });

        // Damage Events - For hit zone analysis
        Schema::table('damage_events', function (Blueprint $table) {
            $table->index('created_at', 'idx_damage_events_created_at');

            // Composite for victim damage queries
            $table->index(['victim_uuid', 'hit_zone_name'], 'idx_damage_events_victim_hitzone');
        });

        // Connections - For player history and recent activity (uses occurred_at)
        Schema::table('connections', function (Blueprint $table) {
            if (Schema::hasColumn('connections', 'occurred_at')) {
                $table->index('occurred_at', 'idx_connections_occurred_at');
            } elseif (Schema::hasColumn('connections', 'created_at')) {
                $table->index('created_at', 'idx_connections_created_at');
            }

            if (Schema::hasColumn('connections', 'event_type')) {
                $table->index('event_type', 'idx_connections_event_type');
            }

            // Composite for player connection history
            if (Schema::hasColumn('connections', 'player_uuid') && Schema::hasColumn('connections', 'event_type')) {
                if (Schema::hasColumn('connections', 'occurred_at')) {
                    $table->index(['player_uuid', 'event_type', 'occurred_at'], 'idx_connections_player_type_occurred');
                } elseif (Schema::hasColumn('connections', 'created_at')) {
                    $table->index(['player_uuid', 'event_type', 'created_at'], 'idx_connections_player_type_created');
                }
            }
        });

        // XP Events - For XP breakdown and recent XP (uses occurred_at)
        if (Schema::hasTable('xp_events')) {
            Schema::table('xp_events', function (Blueprint $table) {
                if (Schema::hasColumn('xp_events', 'occurred_at')) {
                    $table->index('occurred_at', 'idx_xp_events_occurred_at');
                    if (Schema::hasColumn('xp_events', 'player_uuid')) {
                        $table->index(['player_uuid', 'occurred_at'], 'idx_xp_events_player_occurred');
                    }
                } elseif (Schema::hasColumn('xp_events', 'created_at')) {
                    $table->index('created_at', 'idx_xp_events_created_at');
                    if (Schema::hasColumn('xp_events', 'player_uuid')) {
                        $table->index(['player_uuid', 'created_at'], 'idx_xp_events_player_created');
                    }
                }
            });
        }

        // Base Events - For base capture stats (uses occurred_at)
        if (Schema::hasTable('base_events')) {
            Schema::table('base_events', function (Blueprint $table) {
                if (Schema::hasColumn('base_events', 'occurred_at')) {
                    $table->index('occurred_at', 'idx_base_events_occurred_at');
                }
                if (Schema::hasColumn('base_events', 'event_type')) {
                    $table->index('event_type', 'idx_base_events_event_type');
                }
                if (Schema::hasColumn('base_events', 'player_uuid') && Schema::hasColumn('base_events', 'event_type') && Schema::hasColumn('base_events', 'occurred_at')) {
                    $table->index(['player_uuid', 'event_type', 'occurred_at'], 'idx_base_events_player_type_occurred');
                }
            });
        }

        // Player Distance - For distance tracking (uses event_time)
        if (Schema::hasTable('player_distance')) {
            Schema::table('player_distance', function (Blueprint $table) {
                if (Schema::hasColumn('player_distance', 'event_time')) {
                    $table->index('event_time', 'idx_player_distance_event_time');
                    if (Schema::hasColumn('player_distance', 'player_uuid')) {
                        $table->index(['player_uuid', 'event_time'], 'idx_player_distance_player_time');
                    }
                }
            });
        }

        // Player Shooting - For shooting stats (uses event_time)
        if (Schema::hasTable('player_shooting')) {
            Schema::table('player_shooting', function (Blueprint $table) {
                if (Schema::hasColumn('player_shooting', 'event_time')) {
                    $table->index('event_time', 'idx_player_shooting_event_time');
                    if (Schema::hasColumn('player_shooting', 'player_uuid')) {
                        $table->index(['player_uuid', 'event_time'], 'idx_player_shooting_player_time');
                    }
                }
            });
        }

        // Player Grenades - For grenade stats (uses event_time)
        if (Schema::hasTable('player_grenades')) {
            Schema::table('player_grenades', function (Blueprint $table) {
                if (Schema::hasColumn('player_grenades', 'event_time')) {
                    $table->index('event_time', 'idx_player_grenades_event_time');
                    if (Schema::hasColumn('player_grenades', 'player_uuid')) {
                        $table->index(['player_uuid', 'event_time'], 'idx_player_grenades_player_time');
                    }
                }
            });
        }

        // Player Healing RJS - For healing stats (uses event_time and healer_uuid)
        if (Schema::hasTable('player_healing_rjs')) {
            Schema::table('player_healing_rjs', function (Blueprint $table) {
                if (Schema::hasColumn('player_healing_rjs', 'event_time')) {
                    $table->index('event_time', 'idx_player_healing_rjs_event_time');
                }
                // Healer indexes
                if (Schema::hasColumn('player_healing_rjs', 'healer_uuid') && Schema::hasColumn('player_healing_rjs', 'event_time')) {
                    $table->index(['healer_uuid', 'event_time'], 'idx_player_healing_rjs_healer_time');
                }
                // Target indexes
                if (Schema::hasColumn('player_healing_rjs', 'target_uuid') && Schema::hasColumn('player_healing_rjs', 'event_time')) {
                    $table->index(['target_uuid', 'event_time'], 'idx_player_healing_rjs_target_time');
                }
            });
        }

        // Supply Deliveries - For supply stats (uses occurred_at)
        if (Schema::hasTable('supply_deliveries')) {
            Schema::table('supply_deliveries', function (Blueprint $table) {
                if (Schema::hasColumn('supply_deliveries', 'occurred_at')) {
                    $table->index('occurred_at', 'idx_supply_deliveries_occurred_at');
                }
                if (Schema::hasColumn('supply_deliveries', 'player_uuid') && Schema::hasColumn('supply_deliveries', 'occurred_at')) {
                    $table->index(['player_uuid', 'occurred_at'], 'idx_supply_deliveries_player_occurred');
                }
            });
        }

        // Chat Events - For chat history (uses occurred_at)
        if (Schema::hasTable('chat_events')) {
            Schema::table('chat_events', function (Blueprint $table) {
                if (Schema::hasColumn('chat_events', 'occurred_at')) {
                    $table->index('occurred_at', 'idx_chat_events_occurred_at');
                } elseif (Schema::hasColumn('chat_events', 'created_at')) {
                    $table->index('created_at', 'idx_chat_events_created_at');
                }
            });
        }

        // Server Status - For performance graphs (uses recorded_at)
        if (Schema::hasTable('server_status')) {
            Schema::table('server_status', function (Blueprint $table) {
                if (Schema::hasColumn('server_status', 'recorded_at')) {
                    $table->index('recorded_at', 'idx_server_status_recorded_at');
                    if (Schema::hasColumn('server_status', 'server_id')) {
                        $table->index(['server_id', 'recorded_at'], 'idx_server_status_server_recorded');
                    }
                } elseif (Schema::hasColumn('server_status', 'created_at')) {
                    $table->index('created_at', 'idx_server_status_created_at');
                    if (Schema::hasColumn('server_status', 'server_id')) {
                        $table->index(['server_id', 'created_at'], 'idx_server_status_server_created');
                    }
                }
            });
        }

        // Users - For profile queries
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Index for player UUID lookups (links to game stats)
                if (! Schema::hasColumn('users', 'player_uuid')) {
                    // Column doesn't exist, skip
                } else {
                    $table->index('player_uuid', 'idx_users_player_uuid');
                }

                // Index for role-based queries
                $table->index('role', 'idx_users_role');

                // Index for banned users
                if (Schema::hasColumn('users', 'banned_at')) {
                    $table->index('banned_at', 'idx_users_banned_at');
                }
            });
        }

        // Tournament Registrations - For tournament queries
        if (Schema::hasTable('tournament_registrations')) {
            Schema::table('tournament_registrations', function (Blueprint $table) {
                $table->index('status', 'idx_tournament_registrations_status');
                $table->index(['tournament_id', 'status'], 'idx_tournament_registrations_tournament_status');
            });
        }

        // Tournament Matches - For match queries
        if (Schema::hasTable('tournament_matches')) {
            Schema::table('tournament_matches', function (Blueprint $table) {
                $table->index('status', 'idx_tournament_matches_status');
                $table->index(['tournament_id', 'round'], 'idx_tournament_matches_tournament_round');
            });
        }

        // Teams - For team queries
        if (Schema::hasTable('teams')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->index('is_active', 'idx_teams_is_active');
                $table->index('is_verified', 'idx_teams_is_verified');
                $table->index('is_recruiting', 'idx_teams_is_recruiting');
            });
        }

        // Team Members - For member queries
        if (Schema::hasTable('team_members')) {
            Schema::table('team_members', function (Blueprint $table) {
                $table->index('status', 'idx_team_members_status');
                $table->index(['team_id', 'status'], 'idx_team_members_team_status');
            });
        }

        // Anticheat Events - For AC queries
        if (Schema::hasTable('anticheat_events')) {
            Schema::table('anticheat_events', function (Blueprint $table) {
                if (Schema::hasColumn('anticheat_events', 'created_at')) {
                    $table->index('created_at', 'idx_anticheat_events_created_at');
                }
                if (Schema::hasColumn('anticheat_events', 'event_type')) {
                    $table->index('event_type', 'idx_anticheat_events_event_type');
                }
                if (Schema::hasColumn('anticheat_events', 'player_uuid') && Schema::hasColumn('anticheat_events', 'created_at')) {
                    $table->index(['player_uuid', 'created_at'], 'idx_anticheat_events_player_created');
                }
            });
        }

        // Anticheat Stats - For AC stats queries
        if (Schema::hasTable('anticheat_stats')) {
            Schema::table('anticheat_stats', function (Blueprint $table) {
                if (Schema::hasColumn('anticheat_stats', 'created_at')) {
                    $table->index('created_at', 'idx_anticheat_stats_created_at');
                }
                if (Schema::hasColumn('anticheat_stats', 'server_id') && Schema::hasColumn('anticheat_stats', 'created_at')) {
                    $table->index(['server_id', 'created_at'], 'idx_anticheat_stats_server_created');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Helper function to safely drop index
        $dropIndexSafely = function ($table, $indexName) {
            try {
                Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                    $blueprint->dropIndex($indexName);
                });
            } catch (\Exception $e) {
                // Index doesn't exist, skip
            }
        };

        // Player Stats
        if (Schema::hasTable('player_stats')) {
            $dropIndexSafely('player_stats', 'idx_player_stats_kills');
            $dropIndexSafely('player_stats', 'idx_player_stats_deaths');
            $dropIndexSafely('player_stats', 'idx_player_stats_xp_total');
            $dropIndexSafely('player_stats', 'idx_player_stats_total_distance');
            $dropIndexSafely('player_stats', 'idx_player_stats_playtime_seconds');
            $dropIndexSafely('player_stats', 'idx_player_stats_headshots');
            $dropIndexSafely('player_stats', 'idx_player_stats_total_roadkills');
            $dropIndexSafely('player_stats', 'idx_player_stats_uuid_kills_deaths');
            $dropIndexSafely('player_stats', 'idx_player_stats_last_seen_at');
        }

        // Player Kills
        if (Schema::hasTable('player_kills')) {
            $dropIndexSafely('player_kills', 'idx_player_kills_created_at');
            $dropIndexSafely('player_kills', 'idx_player_kills_is_headshot');
            $dropIndexSafely('player_kills', 'idx_player_kills_is_roadkill');
            $dropIndexSafely('player_kills', 'idx_player_kills_victim_created');
        }

        // Damage Events
        if (Schema::hasTable('damage_events')) {
            $dropIndexSafely('damage_events', 'idx_damage_events_created_at');
            $dropIndexSafely('damage_events', 'idx_damage_events_victim_hitzone');
        }

        // Connections
        if (Schema::hasTable('connections')) {
            $dropIndexSafely('connections', 'idx_connections_occurred_at');
            $dropIndexSafely('connections', 'idx_connections_created_at');
            $dropIndexSafely('connections', 'idx_connections_event_type');
            $dropIndexSafely('connections', 'idx_connections_player_type_occurred');
            $dropIndexSafely('connections', 'idx_connections_player_type_created');
        }

        // XP Events
        if (Schema::hasTable('xp_events')) {
            $dropIndexSafely('xp_events', 'idx_xp_events_occurred_at');
            $dropIndexSafely('xp_events', 'idx_xp_events_created_at');
            $dropIndexSafely('xp_events', 'idx_xp_events_player_occurred');
            $dropIndexSafely('xp_events', 'idx_xp_events_player_created');
        }

        // Base Events
        if (Schema::hasTable('base_events')) {
            $dropIndexSafely('base_events', 'idx_base_events_occurred_at');
            $dropIndexSafely('base_events', 'idx_base_events_event_type');
            $dropIndexSafely('base_events', 'idx_base_events_player_type_occurred');
        }

        // Player Distance
        if (Schema::hasTable('player_distance')) {
            $dropIndexSafely('player_distance', 'idx_player_distance_event_time');
            $dropIndexSafely('player_distance', 'idx_player_distance_player_time');
        }

        // Player Shooting
        if (Schema::hasTable('player_shooting')) {
            $dropIndexSafely('player_shooting', 'idx_player_shooting_event_time');
            $dropIndexSafely('player_shooting', 'idx_player_shooting_player_time');
        }

        // Player Grenades
        if (Schema::hasTable('player_grenades')) {
            $dropIndexSafely('player_grenades', 'idx_player_grenades_event_time');
            $dropIndexSafely('player_grenades', 'idx_player_grenades_player_time');
        }

        // Player Healing RJS
        if (Schema::hasTable('player_healing_rjs')) {
            $dropIndexSafely('player_healing_rjs', 'idx_player_healing_rjs_event_time');
            $dropIndexSafely('player_healing_rjs', 'idx_player_healing_rjs_healer_time');
            $dropIndexSafely('player_healing_rjs', 'idx_player_healing_rjs_target_time');
        }

        // Supply Deliveries
        if (Schema::hasTable('supply_deliveries')) {
            $dropIndexSafely('supply_deliveries', 'idx_supply_deliveries_occurred_at');
            $dropIndexSafely('supply_deliveries', 'idx_supply_deliveries_player_occurred');
        }

        // Chat Events
        if (Schema::hasTable('chat_events')) {
            $dropIndexSafely('chat_events', 'idx_chat_events_occurred_at');
            $dropIndexSafely('chat_events', 'idx_chat_events_created_at');
        }

        // Server Status
        if (Schema::hasTable('server_status')) {
            $dropIndexSafely('server_status', 'idx_server_status_recorded_at');
            $dropIndexSafely('server_status', 'idx_server_status_created_at');
            $dropIndexSafely('server_status', 'idx_server_status_server_recorded');
            $dropIndexSafely('server_status', 'idx_server_status_server_created');
        }

        // Users
        if (Schema::hasTable('users')) {
            $dropIndexSafely('users', 'idx_users_player_uuid');
            $dropIndexSafely('users', 'idx_users_role');
            $dropIndexSafely('users', 'idx_users_banned_at');
        }

        // Tournament Registrations
        if (Schema::hasTable('tournament_registrations')) {
            $dropIndexSafely('tournament_registrations', 'idx_tournament_registrations_status');
            $dropIndexSafely('tournament_registrations', 'idx_tournament_registrations_tournament_status');
        }

        // Tournament Matches
        if (Schema::hasTable('tournament_matches')) {
            $dropIndexSafely('tournament_matches', 'idx_tournament_matches_status');
            $dropIndexSafely('tournament_matches', 'idx_tournament_matches_tournament_round');
        }

        // Teams
        if (Schema::hasTable('teams')) {
            $dropIndexSafely('teams', 'idx_teams_is_active');
            $dropIndexSafely('teams', 'idx_teams_is_verified');
            $dropIndexSafely('teams', 'idx_teams_is_recruiting');
        }

        // Team Members
        if (Schema::hasTable('team_members')) {
            $dropIndexSafely('team_members', 'idx_team_members_status');
            $dropIndexSafely('team_members', 'idx_team_members_team_status');
        }

        // Anticheat Events
        if (Schema::hasTable('anticheat_events')) {
            $dropIndexSafely('anticheat_events', 'idx_anticheat_events_created_at');
            $dropIndexSafely('anticheat_events', 'idx_anticheat_events_event_type');
            $dropIndexSafely('anticheat_events', 'idx_anticheat_events_player_created');
        }

        // Anticheat Stats
        if (Schema::hasTable('anticheat_stats')) {
            $dropIndexSafely('anticheat_stats', 'idx_anticheat_stats_created_at');
            $dropIndexSafely('anticheat_stats', 'idx_anticheat_stats_server_created');
        }
    }
};
