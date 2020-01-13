<template>
	<div class="row" @contextmenu.prevent="$refs.menu.open">
    <div class="col-12" :class="{right: right}">
    	<div class="col-12 pr-0 pl-0 display_file_time">
    		<span class="show_name" v-show="sender_name">{{sender_name}},</span>
    		 {{created_at}}</div>
    	<a :href="`/media/${link}`">
				<div class="card custom_card text-white" style="">
          <span class="card-body">
          	<p class='m-0 p-0 p-r-2 p-l-2'><i class="fa fa-file-text"></i> {{ filename }}</p>
          	<p class='m-0 p-0 p-r-2 p-l-2'>{{ filesize }}</p>
          	 <vue-context ref="menu" class="context-menu" :class="[right ? 'context-menu-right' : 'context-menu-left']">
								<template slot-scope="child">
							    <li>
						        <a href="#" @click.prevent="deleteMsg()"><i class="fa fa-trash"></i>Delete</a>
							    </li>
						    </template>
						</vue-context>
          </span>
	      </div>
      </a>
		</div>
		
	</div>
</template>

<script>
	import {VueContext} from 'vue-context';

	export default {
		props: ['right', 'link', 'filename', 'filesize','index','created_at','sender_name','user','content'],
		components: {
			VueContext
		},
		data (){
			return{

			}
		},
		methods:{
			deleteMsg() {
				axios.post(`deleteMessage`,{message_id:this.content.message_id,index:this.index}).then((response) => {
           this.$emit('deleted', this.content.message_id);
        }).catch(error => console.log(error.response));
			}
		}
	}
</script>
<style scoped="">
	.custom_card{
    width: 173px;
    background: -webkit-linear-gradient(45deg, #ff5252, #f48fb1) !important;
    box-shadow: 0 6px 20px 0 rgba(244, 143, 177, .5) !important;
	}
	.right .custom_card{
		float:right;
	}
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