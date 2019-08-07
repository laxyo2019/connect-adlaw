<template>
	<div class="col s12 m6 l6" :class="{right: right}">
    <div class="card small">
      <div class="card-image">
      	<a v-if="message.is_image" :href="`/media/${message.file_id}`">
           <img  :src="message.fileurl" class="file_thumbnail1 msg_img"/>
         </a>
        <span class="card-title"></span>
      </div>
    </div>
  </div>
</template>
<script>
	export default {
		props: ['right', 'link', 'src']
	}
</script>
<style>
	.msg_img{
		width: 100%;
    border-radius: 0
	}
</style>