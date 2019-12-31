<template>
	<div class="chat-text" @contextmenu.prevent="$refs.menu.open">
		<div class="display_msg_time_p"><span class="text-right display_msg_time">
			<span class="show_name" v-show="sender_name">{{sender_name}},</span>
			{{content.created_at}}</span></div>
		<div style='position:relative' class="text context-parent_div">

			<p class="after_msg" v-if="checkIsQuote(content.msg_props,content.message)"
				:style="{maxWidth : content.message.length > 150 ? '80%' : '' }" 
				v-linkified:options="{ className: 'font-italic text-info' }">
				<span class="quote_msg_div mb-3 pb-3">
					<i class="fa fa-quote-left fa-1x"></i>
					</br>
					<span class="quote_msg">{{ JSON.parse(content.msg_props)['msg'] }}</span>
					</br>
					<span class="text-muted">~ {{ JSON.parse(content.msg_props)['sender_name'] }}</span>
					</br> 
				</span>{{content.message}}
			</p>
			<p v-else class="after_msg" v-text="content.message"
				v-linkified:options="{ className: 'font-italic text-info' }"
				:style="{maxWidth : content.message.length > 50 ? '736px' : '' }">
			</p>
			<p class="msg_edited_icon" v-if="checkIsEdited(content.msg_props,content.message)"><i class="fa fa-pencil"></i></p>
				<vue-context ref="menu" class="context-menu" :class="[right ? 'context-menu-right' : 'context-menu-left']">
					<template slot-scope="child">
				    <li>
				    	<a href="#" @click.prevent="doCopy()">
			        	<i class="fa fa-files-o"></i>Copy
			        </a>
				    	<a href="#" @click.prevent="$emit('forwardMessageModal',content,index)">
				    		<i class="fa fa-share"></i>Forward
				    	</a>
				    	<a href="#" @click.prevent="$emit('quote',content,index)">
				    		<i class="fa fa-quote-left"></i>Quote
				    	</a>
				    	<a href="#" @click.prevent="$emit('edit',content,index)" v-show="content.sender_id== user.id">
				    		<i class="fa fa-edit"></i>Edit
				    	</a>
			        <a href="#" @click.prevent="deleteMsg()">
			        	<i class="fa fa-trash"></i>Delete
			        </a>
			        <!-- v-show="content.sender_id== user.id" -->
				    </li>
			    </template>
				</vue-context>
		</div>
	</div>
</template>

<script>
	import {VueContext} from 'vue-context';
	import linkify from 'vue-linkify'
	import VueClipboard from 'vue-clipboard2'
	import Toasted from 'vue-toasted';
  Vue.use(Toasted)
	Vue.use(VueClipboard)
	Vue.directive('linkified', linkify)
	export default {
		props: ['right', 'content','index','user','sender_name'],
		components: {
			VueContext
		},
		data(){
			return{
					menuOpen:false,
			}
		},
		methods: {
			doCopy() {
				let quoted_msg = JSON.parse(this.content.msg_props)['msg'];
				let main_msg = this.content.message;
				let final_msg = (quoted_msg.length > 0) ? quoted_msg + '\n' + main_msg : main_msg;
        this.$copyText(final_msg).then(function (e) {
        	console.log(e)
        	Vue.toasted.success('Message Copied!', { duration: 2000 });
        }, function (e) {
          console.log(e)
        })
      },
			checkIsQuote(msgProps,message){
    		let quote = JSON.parse(msgProps);
    		if(quote.parent_id!=""){
					return true;
    		}
    		return false;
    	},
  		checkIsEdited(msgProps,message){
      		let msgProp = JSON.parse(msgProps);
      		if(msgProp.edited!=""){
						return true;
      		}
      		return false;
      	},
			forwardMsg() {

			},
			quoteMsg() {

			},
			editMsg() {

			},
			deleteMsg() {
				axios.post(`deleteMessage`,{message_id:this.content.message_id,index:this.index}).then((response) => {
           this.$emit('deleted', this.content.message_id);
        }).catch(error => console.log(error.response.data));
			}
		}
	}
</script>

<style>
	.after_msg .text-muted{
	  color: #fff !important;
	}

	.context-parent_div {
		position:relative;
	}

	.context-menu {
		top: 25px !important;
		position: absolute;
		padding: 0 !important;
	}

	.context-menu-right {
    left: auto !important;
		right: 0 !important;
	}

	.context-menu-left {
    left: 0 !important;
	}
</style>