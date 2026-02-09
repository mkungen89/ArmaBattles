<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.server.{serverId}', function ($user) {
    return in_array($user->role, ['admin', 'moderator']);
});

Broadcast::channel('heatmap.{serverId}', function ($user) {
    return in_array($user->role, ['admin', 'moderator', 'gm']);
});
