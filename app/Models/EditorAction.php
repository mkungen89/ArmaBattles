<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EditorAction extends Model
{
    protected $fillable = [
        'server_id',
        'player_name',
        'player_uuid',
        'player_id',
        'action',
        'hovered_entity_component_name',
        'hovered_entity_component_owner_id',
        'selected_entity_components_owners_ids',
        'selected_entity_components_names',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];
}
