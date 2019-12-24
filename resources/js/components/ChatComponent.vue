<template>
    <div>
    	<div class="preloader_div text-center" v-if="!loading_chat && !newChat" >
    	<div class="container" style="margin-top:12%">
	        <br/><br/>
	         <img src="/custom/mini_logo.png" class="mb-3">
	        <h3 class="h3">Laxyo Connect</h3>
	        <div class="row">
	            <div class="col-md-12">
	                <div class="loader17">
	                    <span></span>
	                    <span></span>
	                    <span></span>
	                    <span></span>
	                    <span></span>
	                    <span></span>
	                </div>
	            </div>
	        </div>
	        <br/><br/>
	    </div>
    	</div>
        
        <!-- FOR WEB VIEW -->
        <div v-if="bigScreenView">
            <ContactsList 
                :onlineuserslist="onlineuserslist"
                :allusers="contacts"
                :user="user" @switchUser="switchUser"
                @selected="emitStartConversationWith"
                :group_permission="group_permission"
                ref="contactlistcompnentref">
            </ContactsList>
        </div>
        <!-- FOR MOBILE VIEW -->
        <div v-else id="mySidenav" :style="{width: menuWidth}" class="sidenav">
            <ContactsList
                @menuWidth="closeNav" @selected="emitStartConversationWith"
                :mobileView="mobileView" :onlineuserslist="onlineuserslist"
                :allusers="contacts" :user="user"
                :group_permission="group_permission"
                ref="contactlistcompnentref">
            </ContactsList>
        </div>

        <div class="content">
            <Conversation :mobileView="mobileView"
                @openNav = "openNav()" @groupdeleted="groupdeleted"
                @newMessage="gotnewMessage" @paginate_data="paginate_data"
                :messages="messages" :user="user" 
                :selectedContact="selectedContact" :allusers="contacts"
                :group_permission="group_permission"
                :loading_more="loading_more" :hasChatHistory="hasChatHistory"
                :loading_chat="loading_chat">
            </Conversation>    
        </div>

        <div class="modal fade" id="addusermodal" tabindex="-1" role="dialog"    
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Start New Chat</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <AddNewUser :allcontacts="allusers"></AddNewUser>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="createnewgroup" tabindex="-1" role="dialog" aria-labelledby="createnewgroupLabel"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createnewgroupLabel">Create New Group</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <CreateNewGroup :allcontacts="allcontacts"></CreateNewGroup>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import Toasted from 'vue-toasted';
    Vue.use(Toasted)

    // var FaviconNotification = require('favicon-notification');
    import Conversation from './ConversationComponent';
    import ContactsList from './ContactListComponent';
    import ComposeMessage from './ComposeMessageComponent';
    import AddNewUser from './AddNewUserComponent';
    import CreateNewGroup from './CreateGroupComponent';
    // When your app loads
    // FaviconNotification.init({
    //   color: '#000000'
    // });
    export default {
        props: {
            user: {
                type: Object,
                required: true
            },
            rooms: {
                type: Array,
                default: []
            },
            allusers: {
                type: Array,
                default: []
            },
            allcontacts: {
                type: Array,
                default: []
            },
            group_permission: {
                type: Number,
                default: 0
            },
           
        },

        data() {
            return {
                newChat : false,
                usercontactlist:[],
                contacts:[],
                messages: [],
                selectedContact: null,
                activeUser: false,
                typingStatus: false,
                loading: false,
                // typing_indicator:'',

                // ####r
                mobileView: false,
                menuWidth: '0px',
                bigScreenView: true,
                page:1,
                scrollends:false,
                loading_more:false,
                hasChatHistory:false,
                loading_chat:false,
                // onlineuserscount:0,
                onlineuserslist:[]
            };
        },

        created() {
            this.getcontactlist();
        },

        mounted(){
            this.windowChecker();
            // ###r
            if( window.screen.availWidth <= 500 ){
                this.mobileView         = true;
                this.bigScreenView      = false;
                this.openNav();
            }
            
            this.loading = true;

            // Remove Notification Indicator from tab
            // document.addEventListener("visibilitychange", function() {
            //     if (document.visibilityState === 'visible') {
            //         FaviconNotification.remove();
            //     }
            // }, false);

            let rev_rooms = this.rooms.slice().reverse();

            Echo.private(`EditMessage.${rev_rooms[0]}`)
                .listen('EditMessage', (e) => {
                    this.handleEditedMessage(e.editedmessage)
                });

            Echo.private(`DeleteMessage.${rev_rooms[0]}`)
                .listen('DeleteMessage', (e) => {
                    this.handleDeletedMessage(e.deletedmessage)
                });

            if (this.rooms.length > 0) 
            {
                for (var i = 0; i < this.rooms.length; i++) 
                {
                    Echo.private(`ReadMessage.${this.rooms[i]}`)
                        .listen('ReadMessage', (e) => {
                            console.log('msg seen', e);
                        });

                    // Echo.private(`DeleteMessage.${this.rooms[i]}`)
                    //     .listen('DeleteMessage', (e) => {
                    //         this.handleDeletedMessage(e.deletedmessage)
                    //     });
                    
                    // Echo.private(`GroupDelete.${this.rooms[i]}`)
                    //     .listen('GroupDelete', (e) => {
                    //         this.getcontactlist()
                    //     });

                    Echo.private(`newMessage.${this.rooms[i]}`)
                        .listen('NewMessage', (e) => {
                            this.handleIncoming(e.message);
                        })
                        .listenForWhisper('typing', (e) => {
  
                            if(this.typingStatus) {
                                this.updateTypingStatus(e.chatroom_id, true);
                                clearTimeout(this.typingStatus);
                            }

                            this.typingStatus = setTimeout(() => {
                                this.updateTypingStatus(e.chatroom_id, false);
                            }, 2000);
                        }); 
                }
            }

            this.newRoomListner();

            // PRESENCE CHANNELS
            Echo.join(`chat`)
                .here((users) => {
                    users.forEach((user) => {
                        this.onlineuserslist.push(user);
                    });
                })
                .joining((user) => {
                    this.onlineuserslist.push(user);
                    Vue.toasted.success(`${user.name} is online`, { duration: 2000 });
                })
                .leaving((user) => {
                    this.onlineuserslist = this.onlineuserslist.filter(u => u.id !== user.id);
                    Vue.toasted.show(`${user.name} went offline`, { duration: 2000 });
                });
        },

        methods:{
            switchUser(contact){
                this.selectedContact = contact;
            },
            windowChecker() {
                var count = 0;
                var myInterval;
                let x = this;
                
                window.addEventListener('focus', startTimer); // Active
                window.addEventListener('blur', stopTimer); // Inactive

                function timerHandler() {
                    count++;
                }

                function startTimer() { // Start timer
                    console.log('focus');
                    x.readMessages();
                    if(this.mobileView) { // force refresh on mobile phones
                        window.reload();
                    }
                    myInterval = window.setInterval(timerHandler, 1000);
                }
                
                function stopTimer() { // Stop timer
                    window.clearInterval(myInterval);
                }
            },

            readMessages() {
                axios.patch(`read_messages/${this.selectedContact.room_id}`, {}).then(response => {
                    console.log(response.data);
                }).catch(error => console.log(error.response.data));
            },

            updateTypingStatus(room_id, status)
            {
                this.contacts.forEach((e) => {
                    if(e.room_id == room_id) {
                        e.typing = status;
                    }
                });
            },

        	emitStartConversationWith(contact){
        		this.newChat = true;
        		this.startConversationWith(contact);
            },
            
            // ABANDONED
            // updateusersstatus(){
            //     this.onlineuserslist.forEach((user)=>{
            //         $('.onlineuserscount').text(this.onlineuserslist.length);
            //         $('.contact_no_'+user.id).removeClass('busy');
            //         $('.contact_no_'+user.id).addClass('online');
            //     });
            // },

            paginate_data(room){
                this.page++;
                this.loading_more = true;
                if(!this.scrollends && this.messages.length>0){
                    this.loadMoreMessages(room);
                }else{
                    this.loading_more = false;
                }
            },

            gotnewMessage(message){
                axios.get('getUsersChatRooms')
                    .then((response) => {
                        this.contacts = response.data;
                        // this.updateusersstatus();                       
                });
            },

            getcontactlist(){
                axios.get('getUsersChatRooms')
                    .then((response) => {
                        if (response.data.length > 0) 
                        {
                            this.contacts = response.data;
                            this.contacts = _.orderBy(this.contacts, 'lastmessagetime', 'desc')
                            this.selectedContact = this.contacts[0];
                            this.startConversationWith(this.contacts[0]);
                        }
                        // console.log('length', this.contacts.length);
                        if(this.contacts.length < 1)
                        {
                            this.loading_chat = true;
                            this.newChat = true;
                        }
                    });
            },

            loadMoreMessages(room){
                // console.log('from chat',room.room_id)
                axios.get('get_room_conversations/' + room.room_id+'?page='+this.page).then((response) => {
                    if (response.data.links.next || response.data.meta.current_page <= response.data.meta.last_page)
                    {
                        for(var i=0;i<response.data.data.length;i++)
                        {
                            var moremessage = response.data.data;
                            this.messages.unshift(moremessage[i])
                        }
                        this.loading_more = false
                    }
                    else
                    {
                        this.scrollends = true
                    }
                });
            },

            startConversationWith(contact) {
                this.messages = [];
                this.scrollends = false;
                this.loading_chat = false;
                this.hasChatHistory = false;
                this.page = 1;
                axios.get('get_room_conversations/' + contact.room_id)
                    .then((response) => { 
                        this.messages = response.data.data.slice(0).reverse()
                        this.selectedContact = contact;
                        this.loading = false;
                        if(response.data.meta.sayhi>0){
                            this.hasChatHistory = true;
                        }
                        this.loading_chat = true
                        this.newChat = false
                    });
            },
            
            saveNewMessage(message) {
                this.messages.push(message);
            },

            handleIncoming(message) {
                this.gotnewMessage(message);
                if(message.sender_id != this.user.id){
                    if (document.visibilityState === 'hidden') {
                        // FaviconNotification.add(); 
                        this.showMessageNotification(message);
                    }
                }
                if (this.selectedContact.room_id === message.room_id) {
                    this.saveNewMessage(message);
                    return;
                }
            },

            handleEditedMessage(message){
                var index = 0;
                if(message.message_index || message.message_index!=''){
                    index = message.message_index;
                }
                this.messages[index].message = message.message;
                this.messages[index].msg_props = message.msg_props;
            },

            handleDeletedMessage(message){
                this.messages = this.messages.filter(m => m.message_id != message.message_id);
            },

            showMessageNotification(message){
                var iconURL = "/favicon.ico";
                var title='';
                var custommessage='';
                
                if(message.is_file == 1) {
                    title = message.sender_name + ' Shared a File';
                    custommessage = message.file_name;
                    var audio = new Audio('audio/notification.mp3');
                    audio.play();
                }
                else
                {
                    if (message.message.includes('@'+this.user.name.split(" ")[0]) > 0) { // @ Mention Notification
                        title = message.sender_name + ' has mentioned you in ' + message.chatroom_title;
                        custommessage = message.message;
                        custommessage = custommessage.replace(/<\/?[^>]+(>|$)/g, "");
                        var audio = new Audio('audio/mention.mp3');
                        audio.play();
                    }
                    else { // New Message Notification
                        title = 'New Message From '+message.sender_name;
                        custommessage = message.message;
                        custommessage = custommessage.replace(/<\/?[^>]+(>|$)/g, "");
                        var audio = new Audio('audio/notification1.mp3');
                        audio.play();
                    }
                }
                Notification.requestPermission(permission => {
                        let notificationabc = new Notification(title, {
                            body: custommessage, // content for the alert
                            icon: iconURL // optional image url
                        });
                        // link to page on clicking the notification
                        notificationabc.onclick = () => {
                            if (window.location.pathname == "/home") {
                                location.reload();
                            } else {
                                window.open("/home");
                            }
                        };
                    });
            },

            showNewUserNotification(newRoomData){
                var iconURL = "/favicon.ico";
                var title = '';
                var message = '';
                if (newRoomData.chatroom.type == 'group') {
                    title = 'New Group '+newRoomData.chatroom.title;
                    message = 'Added in '+newRoomData.chatroom.title;
                } else {
                    title = 'New Room';
                    message = 'New Chat is initiated';
                }
                Notification.requestPermission(permission => {
                    let notificationabc = new Notification(title, {
                        body: message, // content for the alert
                        icon: iconURL // optional image url
                    });
                });
            },

            newRoomListner(){
                Echo.private(`newroomcreated.${this.user.id}`)
                    .listen('NewRoom', (e) => {
                        this.updateRoomListners(e.newroomdata);
                        this.showNewUserNotification(e.newroomdata);
                        this.getcontactlist();
                        // this.selectedContact = e.newRoomData;
                        // $('#contact_no_'+e.newroomdata.user_id).removeClass('busy');
                        // $('#contact_no_'+e.newroomdata.user_id).addClass('online');
                });
            },

            updateRoomListners(newlyaddedroom) {
                this.getcontactlist();

                Echo.private(`EditMessage.${newlyaddedroom.chatroom_id}`)
                    .listen('EditMessage', (e) => {
                        console.log('edit_id', e);
                        this.handleEditedMessage(e.editedmessage)
                    });
                Echo.private(`DeleteMessage.${newlyaddedroom.chatroom_id}`)
                    .listen('DeleteMessage', (e) => {
                        console.log('delete_id', e);
                        this.handleDeletedMessage(e.deletedmessage)
                    });
                Echo.private(`newMessage.${newlyaddedroom.chatroom_id}`)
                    .listen('NewMessage', (e) => {
                        this.handleIncoming(e.message);
                    })
                    .listenForWhisper('typing', (e) => {
                        if(this.typingStatus) {
                            this.updateTypingStatus(e.chatroom_id, true);
                            clearTimeout(this.typingStatus);
                        }

                        this.typingStatus = setTimeout(() => {
                            this.updateTypingStatus(e.chatroom_id, false);
                        }, 2000);
                    });                      
            },

            groupdeleted(group){
                this.getcontactlist();
            },
            // ###r
            openNav() {
                this.menuWidth = '100%';
            },
            closeNav() {
                this.menuWidth = '0px';
            }
        },
        watch: {
            rooms (n) {
                this.usercontactlist = n;
            }
        },
        components: {Conversation, ContactsList,AddNewUser,CreateNewGroup}
    }
</script>
<style scoped>
   .loader {
        position: absolute;
        top: 50%;
        right: 50%;
    }

   .loader-overlay-custom {
       background: #000a0a87;
       position: absolute;
       top: 0;
       left: 0;
       bottom: 0;
       right: 0;
       z-index: 100;
       width: 100%;
       height: 105%;
    }
   
	/* preloader */
	.loader17{position:relative;width:65px;border:1px solid transparent;margin:40px auto}
	.loader17 span{position:absolute;bottom:0;display:block;width:9px;height:5px;border-radius:5px;
        background:rgba(0,0,0,.1);-webkit-animation:preloader 2s infinite ease-in-out;animation:preloader 2s infinite ease-in-out}
	.loader17 span:nth-child(2){left:11px;-webkit-animation-delay:.2s;animation-delay:.2s}
	.loader17 span:nth-child(3){left:22px;-webkit-animation-delay:.4s;animation-delay:.4s}
	.loader17 span:nth-child(4){left:33px;-webkit-animation-delay:.6s;animation-delay:.6s}
	.loader17 span:nth-child(5){left:44px;-webkit-animation-delay:.8s;animation-delay:.8s}
	.loader17 span:nth-child(6){left:55px;-webkit-animation-delay:1s;animation-delay:1s}

	@-webkit-keyframes preloader{
		0%,100%,50%{height:5px;-webkit-transform:translateY(0);transform:translateY(0);background:rgba(0,0,0,.1)}
		25%{height:30px;-webkit-transform:translateY(15px);transform:translateY(15px);background:#f8990c}
	}

	@keyframes preloader{
		0%,100%,50%{height:5px;-webkit-transform:translateY(0);transform:translateY(0);background:rgba(0,0,0,.1)}
		25%{height:30px;-webkit-transform:translateY(15px);transform:translateY(15px);background:#f8990c}
    }
</style>