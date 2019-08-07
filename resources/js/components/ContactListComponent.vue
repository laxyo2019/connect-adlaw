<template>
  <div id="sidepanel">
    <div id="profile">
      <div class="wrap"  >
        <!-- <img
          id="profile-img"
          :src="profile_pre + user.name.toUpperCase().charAt(0) +'.png'"
          class="online"> -->
        <span class="online sender-avatar-icon profile_avatar_s">{{ user.name.toUpperCase().charAt(0) }}
         <span class='online-dot-profile'></span>
       </span>
        <p><b>{{ user.name }}</b></p>
        <span class='float-right mt-3' @click="openMenu=!openMenu" style="position: absolute;right: 0">
        	<i class='fa fa-ellipsis-v ' style="font-size: 1.3rem;padding: 0 20px!important;"></i>
        	<ul class='dropdown_css' v-if='openMenu'>
          	<li>
							 <a id="addcontact" data-toggle="modal" data-target="#addusermodal">
					        <i class="fa fa-user-plus fa-fw" aria-hidden="true"></i>
					        <span>Add</span>
					      </a>
          	</li>
          	<li> 
				      <a v-if="group_permission==1"
				              id="creategroup"
				              data-toggle="modal"
				              data-target="#createnewgroup">
				        <i class="fa fa-users fa-fw" aria-hidden="true"></i>
				        <span>Group</span>
				      </a>
          	</li>
          	<li>
          		<a id='logout' href="#" class="" @click.prevent="logout()" title="Logout">
			          <i class="fa fa-sign-out fa-lg"></i>
			          <span>Log out</span>
			        </a> 
          	</li>
          </ul>
        </span>

      <!--<a href="#" class="float-right mt-3" @click.prevent="logout()" title="Logout">
          <i class="fa fa-sign-out fa-lg"></i>
        </a> -->
      </div>
    </div>
    <div class="form-group has-search" style="position:relative">
    	<i class="fa fa-search search_box_icon"></i>
      <input style="background: #eee;border-radius: 23px;" type="text" class="search_box form-control" v-model="search" placeholder="Search Chats...">
    </div>

    <div class="recent_chats">
      <span>Recent Chats</span>
      <hr class="mb-0">
    </div>
    <div id="contacts">
      <div v-if="allusers.length > 0">
        <ul class="contactlists">
          <li v-for="(contact,index) in orderedUsersLists"
              :key="index"
              class="contact"
              v-bind:class="{ 'active' :room_id == contact.room_id}"
              @click="selectContact(contact)">
              <div class="wrap">
                <span class="contact-status" v-bind:class="[{ 'busy' :contact.room_type == 'private'}, 'contact_no_'+contact.user_id]"></span>
                <span class="sender-avatar-icon float-right">{{ contact.roomname.toUpperCase().charAt(0) }}</span>
                <div class="meta">
                  <p class="name">{{ contact.roomname | truncate(18,'...')}}</p>
                  <span class="last_messge_time">{{ contact.displaylastmessagetime }}</span>
                </div>
              </div>
            <!-- <span class="last_message_info">{{ removetagsfromcontactlist(contact.lastmessage.slice(0,30)) }}</span> -->
            <span v-show="contact.unreadcount>0" class="badge badge-light" v-bind:class="'preunread_'+contact.room_id">{{ contact.unreadcount }}</span>
          </li>
        </ul>
      </div>
      <div v-else>
        <div class="meta">
          <p class="name">No Contact Available</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
  	mobileView:{
  		type:Boolean,
  	},
    user: {
      type: Object,
      required: true
    },
    allusers: {
      type: Array,
      default: []
    },
    group_permission: {
      type: Number,
      default: 0
    }
  },
  data() {
    return {
    	openMenu:false,
      contacts: [],
      room_id: "",
      selected: "",
      usercontactlist: [],
      search: "",
      profile_pre: "/custom/alphabets/",
    };
  },
  methods: {
    removetagsfromcontactlist(message){
      return message.replace(/<\/?[^>]+(>|$)/g, "");
    },
    selectContact(contact) {
      $('.preunread_'+contact.room_id).hide();
      this.room_id = contact.room_id;
      this.selected = contact;
      this.$emit("selected", contact);
      // ###r
      this.$emit("menuWidth", "0px");
      $('#message_composer').focus();
    },
    logout() {
      axios.post("logout", {}).then(response => {
        location.reload();
      });
    }
  },
  computed: {
    // filterChatlist: function() {
    //   return this.usercontactlist.filter(contact => {
    //     return contact.roomname.toLowerCase().match(this.search);
    //   });
    // },
    orderedUsersLists: function () {
      if(this.search!=''){
        return this.usercontactlist.filter(contact => {
          return contact.roomname.toLowerCase().match(this.search);
        });
      }else{
        return _.orderBy(this.usercontactlist, 'lastmessagetime','desc')
      }
    }
  },
  watch: {
    allusers(contacts) {
      contacts = _.orderBy(contacts, 'lastmessagetime','desc')
      this.usercontactlist = contacts;
      this.room_id = contacts[0].room_id;
    }
  }
};
</script>

<style scoped>
.dropdown_css li{
	line-height: 33px;
	padding-left:10px;
}
.dropdown_css li a {
	color:#7d7e7f;
	display:block;
	cursor:pointer;
}
.dropdown_css{
	list-style-type: none;
	width:150px;
	position: absolute;
  right: 0;
  top: 20px;
  padding: 5px 10px;
  background: #fff;
 	z-index:999;
 	margin:0;
  border: solid #e6dede 1px;
}
#frame #sidepanel #contacts ul li.contact .wrap .avatar {
  width: 45px !important;
  height: 45px !important;
  border-radius: 50%;
  float: left;
  margin-right: 10px;
  vertical-align: middle;
  border-style: none;
  display: unset !important;
}
.avatar span {
  margin-left: -5px;
}
 
.sender-avatar-icon{
    height: 35px;
    text-align: center;
    width: 35px;
    color: white;
    background-color:#71e5ec;
    float: left;
    border-radius: 50%;
    display: inline-block;
    line-height: 2.5;
    margin: 10px;
    margin-top:6px;
    position: relative;
    line-height: 35px;
}
.search_box_icon{
	    position: absolute;
    top: 13px;
    left: 32px;
    font-size: 16px;
    color: #a9acad;
}
.search_box:focus{
	box-shadow:none!important;
}
.profile_avatar_s{
    width: 40px;
    background: #e5e4e8;
    line-height: 40px;
    color: #000;
    height: 40px;
    font-weight: 600;

}
.badge-light {
    position: absolute;
    color: #ffffff;
    right: 5%;
    top: 50%;
    background-color: #44a66d;
}
span.last_messge_time {
    right: 0;
    position: absolute;
    top: .68rem;
    color: grey !important;
    font-size: 8px;
    background: #ffffff00 !important;
}

.onlineuserscount{
  margin-top: 10%;
  margin-right: 10%;
  position: unset;
}
</style>
