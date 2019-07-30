import Vue from 'vue';
import App from './App.vue'
import Mixin from './Mixin'

Vue.config.productionTip = false

Vue.mixin( Mixin );

new Vue({
    el: '#wcrw-form-builder',
    render: h => h(App),
    created() {
        this.setLocaleData( wcrwForms.i18n['wc-return-warranty'] )
    }
});
