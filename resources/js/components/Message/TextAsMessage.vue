<template>
	<div class="chat-text" @contextmenu.prevent="$refs.menu.open">
		<div class="display_msg_time_p"><span class="text-right display_msg_time">
			<span class="show_name" v-show="sender_name">{{sender_name}},</span>
			{{content.created_at}}</span></div>
		<div style='position:relative' class="text context-parent_div">
			<p class="after_msg" v-html="checkIsQuote(content.is_quote,content.message)" v-bind:style="{maxWidth : content.message.length > 150 ? '80%' : '' }"></p>
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
			checkIsQuote(isQuote,message){
	        		let quote = JSON.parse(isQuote);
	        		if(quote.parent_id!=""){
								return '<span class="quote_msg_div"><i class="fa fa-quote-left fa-1x"></i></br><span class="quote_msg">'+quote['msg']+'</span></br><span>~ '+quote['sender_name']+'</span></br></br></span></br>'+message;
	        		}
	        		return message;
	        	},
			forwardMsg() {

			},
			quoteMsg() {

			},
			editMsg() {

			},
			deleteMsg() {
				axios.post(`deleteMessage`,{message_id:this.content.message_id,index:this.index}).then((response) => {
          // this.$emit('deleted', this.content.message_id);
        }).catch(error => console.log(error.response.data));
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