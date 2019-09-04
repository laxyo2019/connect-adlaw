<template>
	<div class="chat-text" @contextmenu.prevent="$refs.menu.open">
		<div class="display_msg_time_p"><span class="text-right display_msg_time">
			<span class="show_name" v-show="sender_name">{{sender_name}},</span>
			{{content.created_at}}</span></div>
		<div style='position:relative' class="text context-parent_div">

			<p class="after_msg" v-if="checkIsQuote(content.msg_props,content.message)" v-bind:style="{maxWidth : content.message.length > 150 ? '80%' : '' }" >
				<span class="quote_msg_div" style="margin-bottom:10px;padding-bottom:10px; ">
					<i class="fa fa-quote-left fa-1x"></i>
					</br>
					<span class="quote_msg">{{JSON.parse(content.msg_props)['msg']}}</span>
					</br>
					<span>~ {{ JSON.parse(content.msg_props)['sender_name'] }}</span>
					</br> 
				</span>{{content.message}}
			</p>
			<p class="after_msg" v-else v-text="content.message" v-bind:style="{maxWidth : content.message.length > 150 ? '80%' : '' }" ></p>

			<p class="msg_edited_icon" v-if="checkIsEdited(content.msg_props,content.message)"><i class="fa fa-pencil"></i></p>
				<vue-context ref="menu" class="context-menu" :class="[right ? 'context-menu-right' : 'context-menu-left']">
					<template slot-scope="child">
				    <li>
				    	<a href="#" @click.prevent="$emit('forwardMessageModal',content,index)"><i class="fa fa-share"></i>Forward</a>
				    	<a href="#" @click.prevent="$emit('quote',content,index)"><i class="fa fa-quote-left"></i>Quote</a>
				    	<a href="#" @click.prevent="$emit('edit',content,index)" v-show="content.sender_id== user.id"><i class="fa fa-edit"></i>Edit</a>
			        <a href="#" @click.prevent="deleteMsg()" v-show="content.sender_id== user.id"><i class="fa fa-trash"></i>Delete</a>
				    </li>
			    </template>
				</vue-context>
		</div>
	</div>
</template>

<script>
	import {VueContext} from 'vue-context';
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
        }).catch(error => console.log(error.response));
			}
		}
	}
</script>

<style>
.after_msg .text-muted{
  color: #fff!important;
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