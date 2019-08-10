<template>
	<div class="col s12 m6 l6" :class="{right: right}">
		<div class="m-2" v-bind:style="[right ? {'text-align':'right'} : '']"><span class="display_msg_time">
			<span class="show_name" v-show="sender_name">{{sender_name}},</span>
		 {{created_at}}</span></div>
    <div class="card small" style="border:0">
      <div class="card-image">
      	<a  :href="`/media/${file_id}`">
           <!-- <img  src="https://static-global-s-msn-com.akamaized.net/img-resizer/tenant/amp/entityid/AAFAoWI.img?h=75&w=100&m=6&q=60&u=t&o=t&l=f&f=jpg" class="file_thumbnail1 msg_img"/> -->
           <img  :src="fileurl" class="file_thumbnail1 msg_img"/>
         </a>
        <span class="card-title"></span>
      </div>
    </div>
  </div>
</template>
<script>
	export default {
		props: ['right', 'file_id', 'fileurl','created_at','sender_name']
	}
</script>
<style>
	.msg_img{
		width: 100%;
    border-radius: 0
	}
</style>