<template>
	<div class="row">
    <div class="col-12" :class="{right: right}">
    	<div class="col-12 pr-0 pl-0 display_file_time">
    		<span class="show_name" v-show="sender_name">{{sender_name}},</span>
    		 {{created_at}}</div>
    	<a :href="`/media/${link}`">
				<div class="card custom_card text-white" style="">
          <span class="card-body">
          	<p class='m-0 p-0 p-r-2 p-l-2'><i class="fa fa-file-text"></i> {{ filename }}</p>
          	<p class='m-0 p-0 p-r-2 p-l-2'>{{ filesize }}</p>
          </span>
	      </div>
      </a>
		</div>
	</div>
</template>

<script>
	export default {
		props: ['right', 'link', 'filename', 'filesize','created_at','sender_name']
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
</style>