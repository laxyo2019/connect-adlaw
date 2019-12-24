<template>
    <div>
    	<button class="backButtonMobileView" @click="$emit('openNav')"  v-if="mobileView">
            <i class='fa fa-arrow-left'></i>
        </button>

	    <div class="contact-profile">
            <span 
				:style="[mobileView ? {'margin-left':'28px !important'}:'']"
            	v-if="selectedContact" 
                class="sender-avatar-icon"
                data-toggle="modal" 
                data-target="#groupMembersModal">
                {{ selectedContact.roomname.toUpperCase().charAt(0) }}
                <!-- <span class='online-dot-profile'></span> -->
            </span>

            <p v-if="selectedContact"><b>{{ selectedContact.roomname}}</b></p>
            <p v-else>Start a new chat</p>

            <span class="typing_indicator float-right mt-3" v-if="!isEmpty(selectedContact) && selectedContact.typing">
                <div class="ticontainer">
                    <div class="tiblock">
                        <div class="tidot"></div>
                        <div class="tidot"></div>
                        <div class="tidot"></div>
                    </div>
                </div>
            </span>

        </div>
        <div class="messages" style="position:absolute!important">
            <img src="/custom/loader.gif" style="position:absolute;top:0;left:50%;width:20px;z-index:100;" v-show="loading_more">
            <img 	src="/custom/loader.gif" style="position:absolute;top:30%;left:50%;width:50px;z-index: 100;" v-if="!loading_chat">
            <!-- <div>
                <h4 style="position:absolute;top:40%;left:44%;">No Messages Yet</h4>
                <button class="btn btn-primary" style="position:absolute;top:50%;left:50%;" @click="sendFirstHi()">Say Hi</button>
            </div> -->
            <ul class="messages" v-if="loading_chat" v-on:scroll="scrollevent()" 
                v-chat-scroll="{always: false}" id="message_container">
                <li class="sent message sent_message" :id="message.message_id" v-for="(message,index) in messages" 
                    :key="index" v-if="message.sender_id== user.id">
                	<div class="text-center">
        		      <span v-if="checkdate(message)" class="date_line">
                        <span class="subtitle fancy">
                            <span>{{message.group_at}}</span>
                        </span>
     		          </span>
				    </div>
              <div v-if="message.message_deleted=='0'">
				        <div class="chat-body">
				            <span v-if="message.is_file=='0'">
				                <TextAsMessage :right="true"
			                		@forwardMessageModal = 'forwardMessageModal'
			                		@edit='openEditMessageEditor'
			                		@quote='quoteMessage'
			                		@deleted = 'deleteMessage'
			                    :content="message"
			                    :sender_name="selectedContact.room_type=='group' ? message.sender_name.split(' ')[0] : false"
			                    :index="index"
			                    :user="user" />
				            </span>
				            <span v-else-if="message.is_image">
				                <ImageAsMessage 
			                		:right="true" 
			                		:index="index"
			                		@deleted = 'deleteMessage'
			                		:user="user"
			                    :content="message"
			                    :file_id="message.file_id" 
			                    :created_at="message.created_at" 
			                    :sender_name="selectedContact.room_type=='group' ? message.sender_name.split(' ')[0] : false"
			                    :fileurl="message.fileurl" />
				            </span>
				  	        <span v-else>
				                <FileAsMessage :right="true" 
			                		@deleted = 'deleteMessage'
				                    :link="message.file_id" 
				                    :created_at="message.created_at" 
				                    :filename="message.file_name" 
		                        :user="user"
				                    :index="index"
				                    :content="message"
				                    :sender_name="selectedContact.room_type=='group' ? message.sender_name.split(' ')[0] : false"
				                    :filesize="message.file_size" />
				            </span>
				        </div>
				    </div>
                </li>
                <li v-else class="replies message received_message">
                	<div class="text-center">
	            		<span v-if="checkdate(message)" class="date_line">
                    <span class="subtitle fancy">
                      <span>{{message.group_at}}</span>
                    </span>
               		</span>
				    </div>
	                <div class="chat-body" v-if="message.message_deleted=='0'">
			              <span v-if="message.is_file=='0'">
			                <TextAsMessage :right="false" 
			                	@forwardMessageModal = 'forwardMessageModal'
				                @quote='quoteMessage'
		                    :content="message" 
		                    :index="index"
		                    :sender_name="selectedContact.room_type=='group' ? message.sender_name.split(' ')[0] : false"
		                    :user="user"/>
			              </span>
			              <span v-else-if="message.is_image">
			                <ImageAsMessage 
		                		@deleted = 'deleteMessage'
		                		:user="user"
		                    :content="message"
		                		:right="false" 
		                		:sender_name="selectedContact.room_type=='group' ? message.sender_name.split(' ')[0] : false"
		                    :file_id="message.file_id" 
		                    :created_at="message.created_at" 
		                    :fileurl="message.fileurl" />
			              </span>
			              <span v-else>
			                <FileAsMessage :right="false" 
		                    :link="message.file_id" 
		                    :user="user"
				                :content="message"
				                :index="index"
		                    :sender_name="selectedContact.room_type=='group' ? message.sender_name.split(' ')[0] : false"
		                    :created_at="message.created_at" 
		                    :filename="message.file_name" 
		                    :filesize="message.file_size" />
			              </span>
			            </div>         
                </li>
            </ul>
        </div>
        <div class="quotemesg" style="white-space: unset;" v-if="qoutemessagebody!=''">
            <div style="white-space: unset;">
                <i class="fa fa-quote-left fa-1x"></i> 
                <br>
                    <span style="margin: 0;" v-html="qoutemessagebody"></span>
                <br>
                    <b style="" v-text="quotemessagename"></b>
                <hr>
            </div>
        </div>

        <i v-if="qoutemessagebody!=''" @click="cancelquotemessage" class="quotemesgclose fa fa-close"></i>

        <ComposeMessage 
            :mobileView="mobileView" 
            ref="messagecomposercomponent"
            @send="sendMessage" 
            :selectedContact="selectedContact" 
            :user="user">
        </ComposeMessage>
        <!-- Group Members Model Starts -->
        <div class="modal fade" id="groupMembersModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <!-- <p v-if="selectedContact">{{ selectedContact.roomname}} 
                            <button v-if="group_permission===1" type="button" 
                                    class="btn btn-primary deletegroupbutton" 
                                    data-toggle="modal" 
                                    data-target="#deleteGroupConfirmation"
                                    @click="hideGroupmodel">
                                    <i  class="fa fa-trash"></i>
                            </button>
                        </p> -->
                        <button  type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div style="width:100%;" v-if="selectedContact && group_permission===1 && selectedContact.room_type == 'group'">
                            <div style="width:80%;float:left;">
                                <multiselect v-model="selectedMembers"  
                                        track-by="name" label="name" 
                                        placeholder="Add Members" 
                                        :options="addnewtogrouplist" 
                                        :searchable="true"
                                        :close-on-select="false"
                                        :multiple="true">
                                        <template slot="singleLabel" slot-scope="{ option }">
                                            <strong>{{ option.name }}</strong>
                                        </template>
                                </multiselect>
                            </div>
                            <div style="width:20%;float:left;text-align:right;">
                                <button class="btn btn-success" style="height: 45px;" @click="addMemberstoGroup()"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>
                        <!-- <p class="text-danger" v-if="selectedContact && selectedContact.room_type == 'private'">All the Chat history will clear out in case of delete</p> -->
                        <br>
                        <div class="clearfix"></div>
                        <ul class="list-group" v-if="selectedContact && selectedContact.room_type == 'group'">
                            <li class="list-group-item" v-for="(member,index) in groupmembers" :key="index">
                                <table style="width:100%;">
                                    <tr>
                                        <td style="width:20%">
                                            <span class="sender-avatar-icon">{{ member.user_name.toUpperCase().charAt(0) }}</span>
                                            <!-- <img class="img" width="50px" :src="profile_pre + member.user_name.toUpperCase().charAt(0) +'.png'"> -->
                                            </td>
                                        <td style="width:60%;"><p style="line-height:3.5;margin-bottom:0px;" >{{member.user_name}}</p></td>
                                        <td style="width:20%;text-align:right;" v-if="group_permission===1"><i style="line-height:4;" 
                                            class="fa fa-trash" 
                                            @click="removeMembersfromGroup(member.user_id)"></i>
                                        </td>
                                    </tr>
                                </table>
                                
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="deleteGroupConfirmation" tabindex="-1" role="dialog" aria-labelledby="deleteGroupConfirmationTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteGroupConfirmationTitle">Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are You Sure?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" @click="deletegroup()">Delete</button>
                </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="forwardMessageModal" tabindex="-1" role="dialog" aria-labelledby="forwardModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forwardModalLabel">Forward Message</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
            		<div class="form-group">
            			<input v-model="searchForwardTo" type="text" class="form-control" placeholder="Search Name.." 
                            style="border-radius: 25px;" @keyup="filterForwardToList()">
            		</div>
                    <ul class="list-group messages" id="message_container" v-if="loading_chat" v-on:scroll="scrollevent()" v-chat-scroll="{always: false}">
                        <li class="list-group-item" v-for="(member,index) in forwardToList" :key="index">
                            <table style="width:100%;">
                                <tr style="border-bottom: solid rgb(229, 228, 232); border-width: 2px;">
                                    <td style="width:13%">
                                        <span class="sender-avatar-icon">{{ member.roomname.toUpperCase().charAt(0) }}</span>
                                        <!-- <img class="img" width="50px" :src="profile_pre + member.user_name.toUpperCase().charAt(0) +'.png'"> -->
                                        </td>
                                    <td style="width:67%;"><p style="line-height:3.5;margin-bottom:0px;" >{{member.roomname}}</p></td>
                                    <td style="width:20%;text-align:right;">
                                    	<button class="btn forward_send_btn"
                                    		style="border-radius: 19px;color: #fff;background: #198ff1; border: solid 1px #198ff1"
																				@click="forwardMessage(member.room_id)">
                                    		<i  class="fa fa-share mr-2" ></i>Send
																			</button>
                                    </td>
                                </tr>
                            </table>	
                        </li>
                    </ul>
                </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import ComposeMessage from './ComposeMessageComponent';
    import multiselect from 'vue-multiselect';
    import TextAsMessage from './Message/TextAsMessage';
    import ImageAsMessage from './Message/ImageAsMessage';
    import FileAsMessage from './Message/FileAsMessage';

    export default {
        props: {
       		mobileView:{
       			type:Boolean
       		},
            selectedContact:{
                type: Object,
                default: null
            },
            user: {
                type: Object,
                required: true
            },
            messages: {
                type: Array,
                default: []
            },
            group_permission: {
                type: Number,
                default: 0
            },
            allusers: {
                type: Array,
                default: []
            },
            loading_more:{
                type:Boolean,
                default:false
            },
            loading_chat:{
                type:Boolean,
                default:false
            },
            hasChatHistory:{
                type:Boolean,
                default:false
            },
        },
        components: {
            ComposeMessage,
            multiselect,
            TextAsMessage,
            ImageAsMessage,
            FileAsMessage
        },
        data() {
            return {
                searchForwardTo:'',
                forwardToList:'',
                selectedMembers:[],
                // selecteduser:null,
                editmessage:'',
                edit_text_prefix:'edit_message_',
                edit_button_prefix:'edit_button_',
                delete_button_prefix:'delete_button_',
                messagetextprefix:'message_text_',
                profile_pre:'/custom/alphabets/',
                groupmembers:[],
                addnewtogrouplist:[],
                editflag:false,
                selectedUserIds:'',
                editing_index:null,
                editing_message:null,
                hidegroupmodel:false,
                qoutemessagebody:'',
                qoutemessageid:'',
                quoting:false,
                qoutesenderid : '',
                qoutesendername : '',
                quotemessagename:'',
                senderavatar:'',
                forwardingMessage:'',
                // loading:true,
                temphasChatHistory:false
            };
        },
        methods:{
        	checkIsQuote(message){
        		let msg = JSON.parse(message);
        		if(msg.parent_id != ""){
				    	return true;
        		}
        		return false;
        	},
            checkdate(message){
                if(message.group_at == this.lastmessage.group_at){
                    return false;
                }else{
                    this.lastmessage = message
                    return true;
                }
            },
            scrollevent(){
                var pos = $('#message_container').scrollTop();
                if (pos == 0) {
                    // console.log('from conver',this.selectedContact.room_id)
                    this.$emit('paginate_data', this.selectedContact);
                }
            },
            forwardMessageModal(message,index) {
            	this.forwardToList = this.allusers;
              this.searchForwardTo = '';
              $('#forwardMessageModal').modal('show');
              this.forwardingMessage = message.message;
            },
            filterForwardToList() {
            	let searchKey = this.searchForwardTo;
            	//create  a regression to match a particular string in the name , i stands in insensitive 
            	let searchReg = new RegExp(searchKey, "i"); 
            	this.forwardToList = this.allusers.filter((e) => { if(e.roomname.match(searchReg)){ return e; } });
            },
            forwardMessage(room_id) {
                axios.post('sendMessage', {
                    room_id: room_id,
                    message: this.forwardingMessage,
                }).then((response) => {
                		this.$emit('newMessage', response.data);
                  	$('#forwardMessageModal').modal('hide');
                });
            },
            isEmpty(obj) {
                for(var key in obj) {
                    if(obj.hasOwnProperty(key))
                        return false;
                    }
                return true;
            },  
            // sendFirstHi() {
            //     axios.post('sendMessage', {
            //         room_id: this.selectedContact.room_id,
            //         message: 'Hi',
            //     }).then((response) => {
            //         this.$emit('newMessage', response.data);
            //         this.temphasChatHistory = true
            //     });
            // },
            cancelquotemessage(){
                this.qoutemessagebody = '';
                this.qoutemessageid = '';
                this.quoting = false;
                this.qoutesenderid = '';
                this.qoutesendername = '';
            },
            quoteMessage(message,index){
                this.quoting = true;
                this.senderavatar = message.sender_name.toUpperCase().charAt(0);
                this.qoutemessagebody = message.message;
                this.qoutemessageid = message.message_id;
                this.qoutesenderid = message.sender_id;
                this.qoutesendername = message.sender_name;
                this.quotemessagename = message.sender_name;
                // console.log($('.quotemesg').html())
            },
            checkURL(url) {
                return(url.match(/\.(jpeg|jpg|gif|png)$/) != null);
            },
            sendMessage(message){
                if(this.editflag){
                    if(this.editmessage==this.$refs.messagecomposercomponent.message){
                        return;
                    }
                    axios.post('editMessage', {
                        newmessage: this.$refs.messagecomposercomponent.message,
                        message_id: this.editing_message.message_id,
                        index :this.editing_index
                    }).then((response) => {
                        this.editmessage='';
                        this.$refs.messagecomposercomponent.message = '';
                        this.editflag = false;
                        this.editing_index = null;
                        this.editing_message = null;
                        this.$emit('newMessage', response.data);
                    });
                    return;
                }
                axios.post('sendMessage', {
                    room_id: this.selectedContact.room_id,
                    message: message,
                    quoting:this.quoting,
                    // qoutemessagebody:$('.quotemesg').html()
                    quotemessagebody : this.qoutemessagebody,
                    qotemessageid : this.qoutemessageid,
                    qoutesendername : this.qoutesendername,
                    qoutesenderid : this.qoutesenderid,

                }).then((response) => {
                   this.$emit('newMessage', response.data);
                });
                if(this.quoting){
                    this.quoting = false;
                    this.qoutemessagebody = ''; 
                    this.qoutemessageid = ''; 
                    this.qoutesendername = ''; 
                    this.qoutesenderid = ''; 
                }
            },
            openEditMessageEditor(message,index){
                // console.log('msg',message)
                // $('#edit_message_'+message.message_id).show();
                var editmessage = '';
                editmessage = message.message.replace(/<\/?[^>]+(>|$)/g, "")
                // editmessage = editmessage.replace("( Edited )", "");
                this.editmessage = editmessage;
                this.$refs.messagecomposercomponent.message = this.editmessage;
                this.editflag = true;
                this.editing_index = index;
                this.editing_message = message;
            },
            hideGroupmodel(){
               $('#groupMembersModal').modal('hide')
            },
            deleteMessage(message_id){
            	console.log('message_id', message_id);
            	let key = '';
            	this.messages.forEach(function(v,k){
            		if(v.message_id == message_id){
            			console.log('key',k);
            			key = k;
            		}
            	});
              this.messages.splice(key, 1);
            },
            addMemberstoGroup(){
                if(this.selectedMembers.length>0){
                    for (var i = 0; i < this.selectedMembers.length; i++) {
                        this.selectedUserIds = this.selectedUserIds.concat(this.selectedMembers[i].id);
                        this.selectedUserIds = this.selectedUserIds.concat(',');
                    }
                    axios.post('addUserToGroup', {
                        chatroom_id: this.selectedContact.room_id,
                        users :this.selectedUserIds
                    }).then((response) => {
                        axios.get('getGroupMembers/' + this.selectedContact.room_id)
                        .then((response) => {
                            this.groupmembers = response.data;
                        });
                        axios.get('getallusernotingroup/' + this.selectedContact.room_id)
                        .then((response) => {
                            this.addnewtogrouplist = response.data;
                        });
                        this.selectedMembers = [];
                        this.selectedUserIds = '';
                    });
                }
            },
            removeMembersfromGroup(user_id){
                axios.post('removeUserfromGroup', {
                    chatroom_id: this.selectedContact.room_id,
                    user_id :user_id
                }).then((response) => {
                    axios.get('getGroupMembers/' + this.selectedContact.room_id)
                    .then((response) => {
                        this.groupmembers = response.data;
                    });
                    axios.get('getallusernotingroup/' + this.selectedContact.room_id)
                    .then((response) => {
                        this.addnewtogrouplist = response.data;
                    });
                });
            },
            deletegroup(){
                this.$emit('groupdeleted', this.selectedContact);
                axios.post('deletegroup', {
                    chatroom: this.selectedContact,
                }).then((response) => {

                });
            }
        },
        watch: {
            selectedContact (room) {
                if(room.room_type !='private'){
                    axios.get('getGroupMembers/' + room.room_id)
                    .then((response) => {
                        this.groupmembers = response.data;
                    });
                    axios.get('getallusernotingroup/' + room.room_id)
                    .then((response) => {
                        this.addnewtogrouplist = response.data;
                    });
                }
                    
            },
            messages(messages){
                this.messages = messages;
                this.lastmessage = messages[0];
                this.loading = false;
            }
        }
    }
</script>

<style scoped>
  ::-webkit-input-placeholder {
    color: peachpuff;
    font-size: 13px;
  }
  ::-moz-placeholder {
    color: peachpuff;
    font-size: 13px;
  }
  :-ms-input-placeholder {
    color: peachpuff;
    font-size: 13px;
  }
  ::placeholder {
    color: peachpuff;
    font-size: 13px;
  }
  .date_line{
    /*position: relative;
    left: 50%;
    top: -20px;*/
  }
  .btn:focus, .btn.focus {
      outline: 0;
      box-shadow: unset !important;
  }
  .fa-ellipsis-v{
      color:#8e8585;
  }
  .edit_message_input{
    border-radius: 0px;
  }
  .typing_indicator{
    font-size: 12px;
    color: #2c3e50;
  }
  .groupsendername {
    position: relative;
    text-transform: capitalize;
  }
  .text-muted{
    font-style: italic;
  }
  .sent_message{
    position: relative;
  }
  .replies{
    position: relative;
  }

  .sendmessagetime{
    position: absolute;
    top: 10px;
    right: 37px;
  }
  .actionbuttonssend {
    top: 3px;
    right: 108px;
    position: absolute;
  }
  .actionsbuttonreply{
    position: absolute;
    left: 60%;
    top: -3px;
  }
  .actionbuttonssendgroup {
    top: 3px;
    right: 108px;
    position: absolute;
  }
  .actionsbuttonreplygroup {
    position: absolute;
    left: 106px;
    top: -6px;
  }
  .recievemessagetime{
    position: relative;
    top: 0;
    left: 10px;
  }
  .messages .text-muted{
    font-size: 10px;
  }
  #frame .content .messages{
    /* max-height: unset !important; */
    word-spacing: 2px;
  }
  .replies .text-muted{
    color: #abafb3 !important
  }
  .list-group-item{
    border: unset;
    padding: unset;
  }
  .deletegroupbutton{
    position: absolute;
    right: 10%;
    top: 4%;
  }
  .file_thumbnail{
    width: 30% !important;
    /* border-radius: unset !important; */
  }
  .file_thumbnail1{
    width: 60% !important;
    border-radius: 5px !important;
  }

  .sender-avatar-icon{
    height: 37px;
    text-align: center;
    width: 37px;
    color: #000;
    background-color: #e5e4e8;
    float: left;
    border-radius: 50%;
    display: inline-block;
    line-height: 37px;
    position: relative;
    margin: 6px 10px 10px;
  }
  .actionbuttonsender{
    top: 30px;
  }
  .quotemesg{
    left: 2%;
    width: 95%;
    position: absolute;
    bottom: 17%;
    background: whitesmoke;
    border-radius: 10px;
    padding: 10px;
  }
  .quotemesgclose{
    position: absolute;
    bottom: 20%;
    right: 5%;
  }
  .container_send_message{
    word-break: unset;
    white-space: unset;
  }
  #qouteMsgTable tr > td {
    margin: 0;
    padding: 0;
  }
  .file_container_send{
    width: 50%;
    float: right;
    background: rgb(241, 241, 244) !important;
    padding: 25px;
    word-break: unset;
    white-space: unset;
  }
  .file_container_recieve{
    width: 70%;
    float: left;
    padding: 25px;
    word-break: unset;
    white-space: unset;
  }
  @media screen and (max-width: 735px) {
    .typing_indicator{
        font-size: 8px;
        color: #2c3e50;
    }
    .contact-profile p{
        font-size: 12px;
    }
  }

  .subtitle {
    margin: 0 0 2em 0;
    color: grey;
  }
  .fancy {
    font-size: 11px;
    background: #c7edfc;
    padding: 4px;
    border-radius: 10px;
    line-height: 0.5;
    text-align: center;
    padding-right: 8px;
    padding-left: 8px;
  }
  .fancy span {
    display: inline-block;
    position: relative;  
  }
  .fancy span:before,
  .fancy span:after {
    content: "";
    position: absolute;
    height: 5px;
    top: 0;
    width: 600%;
  }
  .fancy span:before {
    right: 100%;
    margin-right: 15px;
  }
  .fancy span:after {
    left: 100%;
    margin-left: 15px;
  }
  .modal-header{
    background: #3490dc;
    color: #fff;
  }

  /* typing indicator */
  .tiblock {
    align-items: center;
    display: flex;
    height: 17px;
  }

  .ticontainer .tidot {
    background-color: #90949c;
  }

  .tidot {
    -webkit-animation: mercuryTypingAnimation 1.5s infinite ease-in-out;
    border-radius: 2px;
    display: inline-block;
    height: 4px;
    margin-right: 2px;
    width: 4px;
  }

  @-webkit-keyframes mercuryTypingAnimation{
    0%{
    	-webkit-transform:translateY(0px)
    }
    28%{
    	-webkit-transform:translateY(-5px)
    }
    44%{
    	-webkit-transform:translateY(0px)
    }
  }

  .tidot:nth-child(1){
  	-webkit-animation-delay:200ms;
  }
  .tidot:nth-child(2){
  	-webkit-animation-delay:300ms;
  }
  .tidot:nth-child(3){
  	-webkit-animation-delay:400ms;
  }
</style>
