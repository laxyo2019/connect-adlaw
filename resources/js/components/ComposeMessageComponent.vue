<template>
  
  <div>
  	 <div class="message-input shadow-lg">
          <div class="row bg-white" style="margin:0">
            <div class="input-field col-sm-1 col-2 text-center " style="padding: 0">
              <button class="btn custom_btn m-l-26 " @click.prevent="uploadfileboxopen">
                <i class='text-white' :class="(!openfileuploadingbox) ? 'fa fa-paperclip fa-lg attachment' : 'fa fa-arrow-down fa-lg'"
                    aria-hidden="true"
                ></i>
              </button>
            </div>
            <div class="input-field col-sm-10 col-8 bg-white" >
              <at-ta :members="members" name-key="user_name">
                <textarea id="textarea1"
                	@keyup="auto_grow(this)"
                  class="materialize-textarea"
                  v-model="message"
                  @keydown.enter.exact.prevent="sendMessage"
                  @keydown="sendTypingEvent"
                  placeholder="Type your message here" autofocus></textarea>
                <!-- <label for="textarea1">Your Message... </label> -->
              </at-ta>
            </div>
            <div class="input-field col-sm-1 col-2 text-center" style="padding: 0">
              <button class="btn custom_btn" @click.prevent="sendMessage">
                <i class="fa fa-paper-plane text-white" aria-hidden="true"></i>
              </button>
            </div>
          </div>
          <vue2Dropzone class="dropzoneUpPosition"
            :useCustomSlot=true v-show="openfileuploadingbox"
            ref="myVueDropzone" id="dropzone"
            :options="dropzoneOptions"
            v-on:vdropzone-sending="sendingEvent"
            @vdropzone-queue-complete="vqueueComplete">

           <div class="dropzone-custom-content">
    	        <h6 v-if='mobileView' class="dropzone-custom-title">Drag and drop to upload content!</h6>
    	        <h3 v-else class="dropzone-custom-title">Drag and drop to upload content!</h3>
    	        <div class="subtitle">
    	        	<span v-if="mobileView" style='font-size:12px'>...or click to select a file from your computer</span>
    	        	<span v-else>...or click to select a file from your computer</span>
    	        </div>
    	      </div>
          </vue2Dropzone>
      </div>
  </div>

</template>
<script>
import AtTa from "vue-at/dist/vue-at-textarea";
import At from "vue-at";

import vue2Dropzone from "vue2-dropzone";
import "vue2-dropzone/dist/vue2Dropzone.min.css";
// import { Picker } from 'emoji-mart-vue';
// import EmojiPicker from 'vue-emoji-picker'
export default {
  components: { AtTa, At, vue2Dropzone},
  props: {
  	mobileView:{
  		type: Boolean
  	},
    selectedContact: {
      type: Object,
      default: null
    },
    user: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
    	dropzoneOptions: {
        url: 'https://httpbin.org/post',
        thumbnailWidth: 200,
        addRemoveLinks: true
      },
      search: '',
      attachmentIcon: "fa fa-paperclip attachment fa-lg",
      openfileuploadingbox: false,
      message: "",
      members: [],
      // typing: {
      //   user: null,
      //   chatroom_id: null
      // },
      // showemojibox:false,
      queueComplete: false,
      dropzoneOptions: {
        url: "/uploadfile",
        thumbnailWidth: 150,
        maxFilesize: 50,
        addRemoveLinks: true,
        autoProcessQueue: false,
        headers: {
          "Access-Control-Allow-Origin": "*",
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        },
        parallelUploads: 1
      }
    };
  },
  directives: {
    focus: {
      inserted(el) {
        el.focus()
      },
    },
  },
  mounted(){
    window.addEventListener("dragenter",function(e){
      e = e || event;
      e.preventDefault();
      this.openfileuploadingbox = true;
      $('#dropzone').show();
    },false);
      
  },
  methods: {
  	auto_grow(element){
  		document.getElementById('textarea1').style.height = "2.5rem";
  		let height = document.getElementById('textarea1').scrollHeight;
  		if(height > 200){
 					document.getElementById('textarea1').style.height = '200px';
  		}else{
					document.getElementById('textarea1').style.height = height+'px';
  		}

  	},
    sendMessage() {
      this.message = $.trim(this.message);
      
      if (this.message === "") {
        this.$refs.myVueDropzone.processQueue();
        return;
      }

      this.$emit("send", this.message);

      this.message = "";

      var audio = new Audio('audio/sendmessage.mp3');
      audio.play();

      document.getElementById('textarea1').style.height = "2.5rem";
    },
    uploadfileboxopen() {
      this.openfileuploadingbox = !this.openfileuploadingbox;
    },
    // openemojobox() {
    //   this.showemojibox = !this.showemojibox;
    // },
    sendingEvent(file, xhr, formData) {
      formData.append("chatroom_id", this.selectedContact.room_id);
    },
    vqueueComplete(file, xhr, formData) {
      this.openfileuploadingbox = !this.openfileuploadingbox;
      this.queueComplete = true;
      setTimeout(function() {
        $(".dz-preview").remove();
        $(".dz-message").show();
      }, 1000);
    },

    sendTypingEvent() {
      Echo.private(`newMessage.${this.selectedContact.room_id}`)
        .whisper('typing', {
          'name': this.user,
          'chatroom_id': this.selectedContact.room_id
        });

      // Echo.join('chat')
      // .whisper('typing', this.user);
    }
  },
  watch: {
    selectedContact(room) {
      this.members = [];
      if (room.room_type != "private") {
        axios.get("getGroupMembers/" + room.room_id).then(response => {
          this.members = response.data;
          let i = this.members.map(item => item.id).indexOf(this.user.id) // find index of your object
          this.members.splice(i, 1)
        });
      }
    }
  }
};
</script>

<style scoped>
  .dropzone-custom-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
  }

  .dropzone-custom-title {
    margin-top: 0;
    color: #00b782;
  }

  .subtitle {
    color: #314b5f;
  }

  #textarea1:focus {
    border-bottom: 1px solid #ff4081;
  }

  #textarea1{
		height:2.5rem;
		resize: none;
    overflow-y: auto;
    box-sizing: border-box;
    font-size: 1rem;
    width: 100%;
    margin: 0 0 8px 0;
    padding: 0;
    transition: box-shadow .3s, border .3s;
    border: none;
    border-bottom: 1px solid #9e9e9e;
    border-radius: 0;
    outline: none;
    background-color: transparent;
    box-shadow: none;
  }

  textarea[data-v-c48dbc66] {
    font-family: "proxima-nova", "Source Sans Pro", sans-serif;
    float: left;
    border: none;
    width: 100%;
    padding: 14px 32px 10px 8px;
    font-size: 1em;
    color: #32465a;
    resize: none;
    white-space: pre-wrap;
    height: 50px;
    border-radius: 10px;
  }

  table{
    margin-bottom: unset;
    background: #f5f5f5;
    width: 100%;
    margin: auto;
  }
  .table th, .table td {
    border: 1px solid #eeeeee;
    vertical-align: middle;
    text-align: center;
  }
  .fa-arrow-down {
    z-index: 4;
    font-size: 1.1em;
    color: #73777c;
    opacity: 0.5;
    cursor: pointer;
  }
  textarea {
    font-family: "proxima-nova", "Source Sans Pro", sans-serif;
    float: left;
    border: none;
    width: 100%;
    padding: 14px 32px 10px 8px;
    font-size: 1em;
    color: #32465a;
    resize: none;
    white-space: pre-wrap;
    height: 50px;
    border-radius: 10px;
  }

  .fa-paperclip{
    color:#73777c;
  }

  .dropzoneUpPosition{
    position: absolute; 
    bottom: 78px; 
    width:100%; 
  }

  /* .emoji-invoker {
    position: absolute;
    top: 39px;
    right: 115px;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.2s;
  }
  .emoji-invoker:hover {
    transform: scale(1.1);
  }
  .emoji-invoker > svg {
    fill: #b1c6d0;
  }

  .emoji-picker {
    position: relative;
    z-index: 1;
    font-family: Montserrat;
    border: 1px solid #ccc;
    width: 15rem;
    height: 20rem;
    overflow: scroll;
    padding: 1rem;
    box-sizing: border-box;
    border-radius: 0.5rem;
    background: #fff;
    box-shadow: 1px 1px 8px #c7dbe6;
  }
  .emoji-picker__search {
    display: flex;
  }
  .emoji-picker__search > input {
    flex: 1;
    border-radius: 10rem;
    border: 1px solid #ccc;
    padding: 0.5rem 1rem;
    outline: none;
  }
  .emoji-picker h5 {
    margin-bottom: 0;
    color: #b1b1b1;
    text-transform: uppercase;
    font-size: 0.5rem;
    cursor: default;
  }
  .emoji-picker .emojis {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
  }
  .emoji-picker .emojis:after {
    content: "";
    flex: auto;
  }
  .emoji-picker .emojis span {
    padding: 0.2rem;
    cursor: pointer;
    border-radius: 5px;
  }
  .emoji-picker .emojis span:hover {
    background: #ececec;
    cursor: pointer;
  } */

  .tabletd1 {
    width: 5%;
  }
  .tabletd3 {
    width: 6%;
  }
  .tabletd2 {
    width: 70%;
    padding: 27px;
  }
  @media screen and (max-width: 768px){
    table{
      width: 100%;
    }
    /* .emoji-invoker {
       right: 63px;
       top: 29px;
    } */
    .tabletd2 {
      padding: 0px;
    }
  }

  /*mohini*/
  .input-field {
      position: relative;
      margin-top: 1rem;
      margin-bottom: 1rem;
  }
  .custom_btn{
  	background-color: #ff5252 !important;
      height: 36px!important;
      border-radius: 4px;
  }
</style>
