<?php

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat', function ($user) {
    return $user;
});

Broadcast::channel('newMessage.{id}', function ($user, $rooms) {
    return true;
});

// Broadcast::channel('ReadMessage.{id}', function ($user, $rooms) {
//     return true; 
// });

Broadcast::channel('EditMessage.{id}', function ($user, $rooms) {
    return true; 
});

Broadcast::channel('DeleteMessage.{id}', function ($user, $rooms) {
    return true; 
});

Broadcast::channel('GroupDelete.{id}', function ($user, $rooms) {
    return true; 
});

Broadcast::channel('newroomcreated.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});