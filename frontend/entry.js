import Vue from 'vue'
import App from './app.vue'
import router from './router'
import store from './store'
import Helpers from './utils/helpers'
import Element from 'element-ui'
import Modal from 'component/modal'
import VCharts from 'v-charts'
import 'lodash'

Vue.use(VCharts)
Vue.use(Element)
Vue.use(Helpers)
Vue.component(Modal.name, Modal)

export const app = new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')
