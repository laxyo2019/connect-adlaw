<?php

Route::get('/', function(){
  return redirect('login');
});
// Route::get('/','LoginController@login');	
Auth::routes(['register' => false]);

Route::get('/custom-login', 'Auth\LoginController@customLogin');
Route::post('/customlogout', 'Auth\LoginController@customlogout')->name('customlogout');;

Route::get('/home', 'HomeController@index')->name('home');

Route::post('newprivatechat', 'HomeController@newprivatechat')->name('newprivatechat');
Route::get('getUsersChatRooms', 'HomeController@getUsersChatRooms')->name('getUsersChatRooms');
Route::get('get_room_conversations/{room_id}', 'HomeController@getRoomConversations')->name('get_room_conversations');
Route::post('sendMessage', 'HomeController@sendMessage')->name('sendMessage');
Route::post('editMessage', 'HomeController@editMessage')->name('editMessage');
Route::post('deleteMessage', 'HomeController@deleteMessage')->name('deleteMessage');
Route::post('create-chat-groups', 'HomeController@createNewGroup')->name('create-chat-groups');
Route::post('addUserToGroup', 'HomeController@addUserToGroup')->name('addUserToGroup');
Route::post('removeUserfromGroup', 'HomeController@removeUserfromGroup')->name('removeUserfromGroup');
Route::post('deletegroup', 'HomeController@deletegroup')->name('deletegroup');
Route::get('getGroupMembers/{chatroom_id}', 'HomeController@getGroupMembers')->name('getGroupMembers');
Route::get('getallusernotingroup/{chatroom_id}', 'HomeController@getallusernotingroup')->name('getallusernotingroup');

Route::post('uploadfile', 'HomeController@uploadfile')->name('uploadfile');
Route::resource('/media', 'MediaController');

Route::patch('read_messages/{room_id}', 'HomeController@readMessages');

Route::get('direct_delete/{room_id}', 'HomeController@DirectDelete')->name('direct_delete');
