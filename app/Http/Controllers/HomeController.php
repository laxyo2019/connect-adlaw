<?php

namespace App\Http\Controllers;

use App\Events\DeleteMessage;
use App\Events\EditMessage;
use App\Events\GroupDelete;
use App\Events\NewMessage;
use App\Events\NewRoom;
// use App\Events\ReadMessage;
use App\Http\Resources\ContactCollection;
use App\Http\Resources\GroupMemberCollection;
use App\Http\Resources\MessageResource;
use App\Http\Resources\MessageResourceCollection;
use App\Http\Resources\Participant;
use App\Http\Resources\ParticipantCollection;
use App\Models\Chatroom;
use App\Models\ChatroomMessage;
use App\Models\ChatroomUser;
use App\Models\UnreadMessage;
use App\User;
use Auth;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Models\Media;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Crypt;
class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    //Load Chat View with User Data
    public function index(){
        $auth_user_id = Auth::user()->id;      
        $rooms = ChatroomUser::where('user_id', $auth_user_id)->pluck('chatroom_id');
        $crooms = Chatroom::whereIn('id',$rooms)->where('type','private')->pluck('id');
        $added_users_id = ChatroomUser::whereIn('chatroom_id', $crooms)->pluck('user_id'); 

        $allcontacts = $this->get_users($auth_user_id)->get();
        $allusers = $this->get_users($auth_user_id)->whereNotIn('id',$added_users_id)->get();
        
        return view('home',compact('allusers','rooms','allcontacts'));
    }

    // Create Chat Rooms Function Starts
    public function newprivatechat(Request $request){
        $auth_user_id = Auth::user()->id;
        $chatrooms = ChatRoom::where('type', 'private')->whereHas('participants', function ($query) use ($auth_user_id) {
            $query->where('user_id', '=', $auth_user_id);
        })
            ->with('participants')
            ->get();

        $count = 0;
        foreach ($chatrooms as $rooms) {
            foreach ($rooms->participants as $value) {
                if ($value->user_id == $request->user_id) {
                    $count++;
                }
            }
        }

        if ($count <= 0) {
            //create new chat room
            $chatroom = new Chatroom;
            $chatroom->title = 'New Chat Room';
            $chatroom->type = 'private';
            $chatroom->initiator_id = $auth_user_id;
            $chatroom->save();

            // Add Chat room members to chat room
            $chatroomuser = new ChatroomUser;
            $chatroomuser->chatroom_id = $chatroom->id;
            $chatroomuser->user_id = $request->user_id;
            $chatroomuser->save();
            // Notify User
            broadcast(new NewRoom($chatroomuser));
            $chatroomuser = new ChatroomUser;
            $chatroomuser->chatroom_id = $chatroom->id;
            $chatroomuser->user_id = $auth_user_id;
            $chatroomuser->save();
            // Notify User
            broadcast(new NewRoom($chatroomuser));
            return response($chatroom);
        }else{
            echo "Already Created";
        }            
    }

    public function getUsersChatRooms(){
        $auth_user_id = Auth::user()->id;

        $chatrooms = Chatroom::whereHas('participants', function ($query) use ($auth_user_id) {
            $query->where('user_id', '=', $auth_user_id);
        })
        ->with('participants')
        ->get();
        
        return response(new ParticipantCollection($chatrooms));
    }

    public function readMessages($room_id) {

        // $unread_msgs = UnreadMessage::where([
        //     'chatroom_id' => $room_id,
        //     'user_id' => auth()->user()->id,
        //     'read_at' => null
        // ])->get();

        UnreadMessage::where('chatroom_id', $room_id)->where('user_id', auth()->user()->id)->update(['read_at'=>date('Y-m-d H:i:s')]);


        // foreach($unread_msgs as $msg)
        // {
        //     UnreadMessage::where('id', $msg->id)->update(['read_at' => date('Y-m-d H:i:s')]);
        //     broadcast(new ReadMessage($msg));
        // }
    }

    //Get All Messages of single ChatRoom
    public function getRoomConversations($room_id) {
        $this->readMessages($room_id);

        $data = ChatroomMessage::where('chatroom_id', $room_id)->orderBy('id','desc')->paginate(20);
        return (new MessageResourceCollection($data))->chatroom_id($room_id);
    }

    // Send Message
    public function sendMessage(Request $request) {
    	$msg_props = array(
    		'parent_id' => "",
    		'sender_id' => "",
    		'sender_name' => "",
    		'msg' => "",
    		'quoted'=> "",
    		'edited'=> ""
    	);

        if($request->quoting)
        {
			$msg_props['parent_id'] = $request->qotemessageid;
			$msg_props['sender_name'] = $request->qoutesendername;
			$msg_props['sender_id'] = $request->qoutesenderid;
			$msg_props['msg'] = $request->quotemessagebody;
        }

        $user_id = Auth::user()->id;


        $newMessage = new ChatroomMessage;
        $newMessage->is_file = ($request->hasfile('file')) ? 1 : 0; 
        $newMessage->message = $request->message;
        $newMessage->msg_props = json_encode($msg_props);
        $newMessage->chatroom_id = $request->room_id;
        $newMessage->sender_id = $user_id;
        $newMessage->save();
        
        $chatroomusers = ChatroomUser::where('chatroom_id', $request->room_id)->pluck('user_id');
        foreach ($chatroomusers as $key => $value) {
            $unreadMessage = new UnreadMessage;
            if ($user_id != $value) {
                $unreadMessage->chatroom_id = $request->room_id;
                $unreadMessage->user_id = $value;
                $unreadMessage->message_id = $newMessage->id;
                $unreadMessage->save();
            }
        }
        
        broadcast(new newMessage($newMessage));

        return response(new MessageResource($newMessage));
    }

    public function editMessage(Request $request){
        $editMessage = ChatroomMessage::findOrFail($request->message_id);
        $msg_props  =  json_decode($editMessage->msg_props);
        $msg_props->edited = true;
        $editMessage->message = $request->newmessage;
        $editMessage->msg_props = json_encode($msg_props);
        $editMessage->save();

        broadcast(new EditMessage($editMessage, $request->index));
    }

    public function createNewGroup(Request $request){
        $group_participants = rtrim($request->users, ',');
        $group_participants = explode(',', $group_participants);

        //create new chat room
        $chatroom = Chatroom::create([
            'title' => $request->groupname,
            'type' => 'group',
            'initiator_id' => auth()->user()->id
        ]);

        // Upload Logo 
        // if ($request->hasfile('logo')) {
        //     $file = $request->file('logo');
        //     $filename = time() . $file->getClientOriginalName();
        //     Storage::disk('public')->put($filename, File::get($request->logo));
        //     $chatroom->icon = $filename;
        // }

        // All Users insertion begins
        foreach ($group_participants as $value) {
            $chatParticipants = new ChatroomUser;
            $chatParticipants->chatroom_id = $chatroom->id;
            $chatParticipants->user_id = $value;
            $chatParticipants->save();

            broadcast(new NewRoom($chatParticipants));
        }

        $chatParticipants = new ChatroomUser;
        $chatParticipants->chatroom_id = $chatroom->id;
        $chatParticipants->user_id = auth()->user()->id;
        $chatParticipants->save();

        broadcast(new NewRoom($chatParticipants));
        return response()->json(['success' => 1]);
    }

    public function deleteMessage(Request $request){
        $deletedMessage = ChatroomMessage::where('id',$request->message_id)->first();

        $media = DB::table('media')->where('model_id', $request->message_id)->first();
        if($media){
            Storage::disk('do_chat')->delete($media->id . '/' . $media->file_name);
            DB::table('media')->where('model_id', $request->message_id)->delete();
        }
        
        ChatroomMessage::where('id', $request->message_id)->delete();
        broadcast(new DeleteMessage($deletedMessage, $request->index));
    }

    public function getGroupMembers($chatroom_id){
        $members = ChatroomUser::where('chatroom_id', $chatroom_id)->get();
        return response(new GroupMemberCollection($members));
    }

    public function getallusernotingroup($chatroom_id){
        $auth_user_id = Auth::user()->id;
        $added_users_id = ChatroomUser::where('chatroom_id', $chatroom_id)->pluck('user_id');
        $allusers = User::whereNotIn('id',$added_users_id)->where('id','!=',$auth_user_id)->get();
        return response()->json($allusers);
    }

    public function addUserToGroup(Request $request){
        $group_participants = rtrim($request->users, ',');
        $group_participants = explode(',', $group_participants);

        //All Users insertion begins
        foreach ($group_participants as $value) {
            $check = ChatroomUser::where('user_id',$value)->first();
            if(!empty($check)){
                $chatParticipants = new ChatroomUser;
                $chatParticipants->chatroom_id = $request->chatroom_id;
                $chatParticipants->user_id = $value;
                $chatParticipants->save();
                broadcast(new NewRoom($chatParticipants));
            }else{
                echo "Already Exists";
            }

        }
        //All Users insertion end
    }
    
    public function removeUserfromGroup(Request $request){
        ChatroomUser::where('chatroom_id',$request->chatroom_id)->where('user_id',$request->user_id)->delete();

    }

    public function deletegroup(Request $request){
        // dd($request->all());
        $chatroom_id = $request->chatroom['room_id'];
        $chatroom_users = ChatroomUser::where('chatroom_id',$chatroom_id)->get();
        foreach($chatroom_users as $user){
            broadcast(new GroupDelete($user));
        }
        Chatroom::where('id',$chatroom_id)->delete();
    }

    // Upload File
    public function uploadfile(Request $request){
        // dd($request->all());
        $file = $request->file('file');
        $name = $file->getClientOriginalName();
        //Storage::disk('do')->put('/PMS/chat/'.$name,file_get_contents($request->file('file')->getRealPath()),'public');
        // $url = Storage::disk('do')->url('/PMS/chat/'.$name);
        // $cdn_url = str_replace('digitaloceanspaces','cdn.digitaloceanspaces',$url);
        $user_id = auth()->user()->id;
        $newMessage = new ChatroomMessage;
        $newMessage->is_file = 1;
        // $newMessage->message = $cdn_url;
        $newMessage->chatroom_id = $request->chatroom_id;
        $newMessage->sender_id = $user_id;
        $newMessage->save();

        $newMessage->addMedia($request->file)->usingFileName($name)->toMediaCollection();
        
        broadcast(new newMessage($newMessage));
      
    }

    public function downloadfile($id){
        $messge = ChatroomMessage::find($id);
        return response()->download($messge->getMedia()[0]->getPath(), $messge->getMedia()[0]->file_name);
    }

    public function DirectDelete($room_id){
        if($room_id != 0){
            $this->getRoomConversations($room_id);               
        }        
        $data = $this->getUsersChatRooms();  
        return $data;        
    }

    public function get_users($auth_user_id){
        $user = User::find($auth_user_id);

        if(Auth::user()->parent_id == null){
            $users = User::where('parent_id',$auth_user_id)->orderBy('name', 'asc');
            if($user->user_catg_id == '4'){
                $users = $users->where('user_catg_id','6');
            }
            else if($user->user_catg_id == '3' || $user->user_catg_id == '2'){
                $users = $users->where('user_catg_id','2');
         
            }else{
                $users = $users->where('user_catg_id','5');
            }
            
        }else{
            $users = User::where('parent_id',Auth::user()->parent_id)->where('user_catg_id',$user->user_catg_id)->where('id','!=',$auth_user_id)->orderBy('name', 'asc');
        }
        return $users;
    }
}
