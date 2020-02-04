<template>
	<div class="col s12 m6 l6" :class="{right: right}" @contextmenu.prevent="$refs.menu.open">
		<div class="m-2" v-bind:style="[right ? {'text-align':'right'} : '']"><span class="display_msg_time">
			<span class="show_name" v-show="sender_name">{{sender_name}},</span>
		 {{created_at}}</span></div>
    <div class="card small" style="border:0">
      <div class="card-image">
      	<a  :href="`/media/${file_id}`">
           <!-- <img  src="https://static-global-s-msn-com.akamaized.net/img-resizer/tenant/amp/entityid/AAFAoWI.img?h=75&w=100&m=6&q=60&u=t&o=t&l=f&f=jpg" class="file_thumbnail1 msg_img"/> -->
           <img  :src="fileurl" class="msg_img" />
            <vue-context ref="menu" class="context-menu" :class="[right ? 'context-menu-right' : 'context-menu-left']">
								<template slot-scope="child">
							    <li>
						        <a href="#" @click.prevent="deleteMsg()" v-show="content.sender_id== user.id"><i class="fa fa-trash"></i>Delete</a>
							    </li>
						    </template>
						</vue-context>
         </a>
        <span class="card-title"></span>
      </div>
    </div>
  </div>
</template>
<script>
	import {VueContext} from 'vue-context';
	export default {
		props: ['right', 'content','user','file_id', 'index','fileurl','created_at','sender_name'],
			components: {
			VueContext
		},
		data (){
			return{
				
			}
		},
		methods:{
			deleteMsg() {
				axios.post(`deleteMessage`,{message_id:this.content.message_id, index:this.index}).then((response) => {
           this.$emit('deleted', this.content.message_id);
        }).catch(error => console.log(error.response));
			}
		}
	}
</script>
<style>
	.msg_img{
		max-width: 300px!important;
		max-height: 220px!important;
		width:auto!important;
    border-radius: 0
	}
	@import '~vue-context/dist/css/vue-context.css';
</style>