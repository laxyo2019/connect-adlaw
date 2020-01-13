<template>
  <div id="sidepanel">
    <div id="profile">
      <div class="wrap">
        <span class="online sender-avatar-icon profile_avatar_s">{{ user.name.toUpperCase().charAt(0) }}
          <span class='online-dot-profile'></span>
        </span>
        <b>{{ user.name }}</b>
        <a href="#" id='logout' class="float-right mt-4 mr-2" style="color: #ff5252" 
        @click.prevent="logout()" title="Logout">
          <i class="fa fa-sign-out fa-lg"></i>
        </a>
      </div>
    </div>

    <div class="form-group has-search" style="position:relative">
      <i class="fa fa-search search_box_icon"></i>
      <input style="background: #eee;border-radius: 23px;" type="text" class="search_box form-control" v-model="search" placeholder="Search Chats...">
    </div>

    <div class="recent_chats">
      <button id="addcontact" data-toggle="modal" data-target="#addusermodal" class="btn btn-sm btn-primary add_btn mr-2" title="New Chat">
        <i class="text-white fa fa-user-plus fa-fw" aria-hidden="true"></i>
      </button>

      <button v-if="group_permission == 1" id="creategroup" data-toggle="modal" data-target="#createnewgroup" 
        class="btn btn-sm btn-secondary add_btn mr-2" title="New Group">
        <i class="fa fa-users fa-fw" aria-hidden="true"></i>
      </button>

      <strong class="text-success animated infinite pulse slow" title="Users Online">
        {{ onlineuserslist.length }}  
      </strong>
      
      <hr class="mb-0">
    </div>

    <div id="contacts">
      <div v-if="allusers.length > 0">
        <ul class="contactlists">
          <!-- v-for loop starts -->
          <li v-for="(contact, index) in orderedUsersLists"
              :key="index" class="contact"
              :class="{ 'active' : selected.room_id == contact.room_id }"
              @click="selectContact(contact)">
              <div class="wrap">
                <span class="contact-status" :class="[checkIfOnline(contact.user_id) ? 'online' : 'busy']"></span>
                <span class="sender-avatar-icon float-right">{{ contact.roomname.toUpperCase().charAt(0) }}</span>
                <div class="meta">
                  <p class="name">{{ processName(contact.roomname).name }}</p>

                  <div class="ticontainer indicator" v-if="contact.typing">
                    <div class="tiblock">
                      <div class="tidot"></div>
                      <div class="tidot"></div>
                      <div class="tidot"></div>
                    </div>
                  </div>
                  <div v-else class="indicator">
                    <p class="m-0 info-text">{{ processName(contact.roomname).designation }}</p>
                  </div>

                  <span class="last_messge_time">{{ contact.displaylastmessagetime }}</span>
                </div>
                
              </div>
            <span class="badge badge-light" v-show="contact.unreadcount > 0">
              {{ contact.unreadcount }}
            </span>
          </li> 
          <!-- v-for loop ends -->
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
  import Event from '../event.js';
  export default {
    props: ['mobileView', 'user', 'allusers', 'group_permission', 'onlineuserslist'],
      // mobileView:{
      //  type:Boolean,
      // },
      // user: {
      //   type: Object,
      //   required: true
      // },
      // allusers: {
      //   type: Array,
      //   default: []
      // },
      // group_permission: {
      //   type: Number,
      //   default: 0
      // },
      // onlineuserslist: {
      //   type: Array,
      //   default: []
      // }
    data() {
      return {
        // openMenu:false,
        contacts: [],
        // room_id: "",
        selected: {},
        usercontactlist: [],
        search: "",
        profile_pre: "/custom/alphabets/",
        // usersOnline: []
      };
    },
    created(){
      document.addEventListener("visibilitychange", function() {
        let room_id1 =0
         this.usercontactlist={}
        axios.get("direct_delete/" + room_id1).then(response => {

             console.log(_.orderBy(response.data, 'lastmessagetime','desc'));         
                         
          });
       
      }, false);

      Event.$on('Incoming',(e)=>{          
        //this.contact.unreadcount = e
        
        if(this.selected.room_id == e){
            axios.get("direct_delete/" + e).then(response => {
              this.usercontactlist = response.data;       
            });
        }
        //  $('.preunread_'+e).hide();
            
      })
      // this.usersOnline = this.onlineuserslist;
      // axios.get('get_unreadcounts').then(response => {
      //   console.log('unread_messages', response.data);
      // }).catch(error => console.log(error.response.data));
    },
    methods: {

      checkIfOnline(uid){
        return this.onlineuserslist.find(e => e.id === uid);
      },
      removetagsfromcontactlist(message){
        return message.replace(/<\/?[^>]+(>|$)/g, "");
      },

      selectContact(contact) {
        //console.log(contact)
        contact.unreadcount = 0;
        this.selected = contact;
        axios.get("direct_delete/" + contact.room_id).then(response => {
            this.usercontactlist = response.data
          });
        this.$emit("selected", contact);
        // ###r
        this.$emit("menuWidth", "0px");
        $('#textarea1').focus();
      },

      processName(str) 
      {
        if(str.indexOf("(") > 0 && str.indexOf(")") > 0)
        {
          let pattern = /\((.*?)\)/;
          let test = str.match(pattern)[0];

          let output = {
            'name': str.split(test).join('') ,
            'designation': str.match(pattern)[1]
          }
          return output;
        } 

        return {'name' : str, 'designation' : ''}
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
        if(this.search != ''){
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
        contacts = _.orderBy(contacts, 'lastmessagetime', 'desc')
        this.usercontactlist = contacts;
        this.room_id = contacts[0].room_id;
        // First code
        // contacts = _.orderBy(contacts, 'lastmessagetime', 'desc')
        // this.usercontactlist = contacts;
        // this.$emit("switchUser", contacts[0]);
       // this.selected = contacts[0];
      }
    }
  };
</script>

<style scoped>
  .add_btn {
    border-radius: 40px;
  }

  .info-text {
    font-weight: 400;
  }

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
      width: 35px;
      text-align: center;
      color: white;
      background-color:#71e5ec;
      float: left;
      border-radius: 50%;
      display: inline-block;
      margin: 10px;
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
    background-color: #ff5252;
    border-radius: 50%;
    width: 20px;
    font-size: .75rem;
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

  /* typing indicator */
  .indicator {
    position: absolute;
    left: 50px;
    top: 18px;
  }

  .tiblock {
    align-items: center;
    display: flex;
    height: 20px;
    width: 80%;
    margin: 0 auto;
  }

  .ticontainer .tidot {
      background-color: #90949c;
  }

  .tidot {
      -webkit-animation: mercuryTypingAnimation 1.5s infinite ease-in-out;
      border-radius: 4px;
      display: inline-block;
      height: 8px;
      margin-right: 4px;
      width: 8px;
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
