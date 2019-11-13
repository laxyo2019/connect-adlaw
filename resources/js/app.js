require('./bootstrap');

window.Vue = require('vue');
import multiselect from 'vue-multiselect'
import VueChatScroll from 'vue-chat-scroll'
import vue2Dropzone from 'vue2-dropzone'
import At from 'vue-at'
import AtTa from 'vue-at/dist/vue-at-textarea'
import 'vue2-dropzone/dist/vue2Dropzone.min.css'
Vue.use(VueChatScroll)
Vue.use(vue2Dropzone)
Vue.use(multiselect)
Vue.use(At)
Vue.use(AtTa)

import linkify from 'vue-linkify'
 
Vue.directive('linkified', linkify)

Vue.component('chat-component', require('./components/ChatComponent.vue').default);

const app = new Vue({
	el: '#app'
});

var filter = function(text, length, clamp){
    clamp = clamp || '...';
    var node = document.createElement('div');
    node.innerHTML = text;
    var content = node.textContent;
    return content.length > length ? content.slice(0, length) + clamp : content;
};

Vue.filter('truncate', filter);