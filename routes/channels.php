<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
 * The "room.{code}" channel is intentionally PUBLIC. The 6-character room
 * code is the access secret — anyone with the code can listen in. This lets
 * anonymous (guest) impostor players subscribe via Reverb without a
 * Laravel auth session.
 */
