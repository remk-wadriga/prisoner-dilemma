import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

var index = 0;

export default new Vuex.Store({
    state: {
        app: {
            pageTitle: ''
        },
        logger: {
            messages: [
                /*{id: 'msg_1', type: 'danger', text: 'Danger text'},
                {id: 'msg_2', type: 'info', text: 'Info text'},
                {id: 'msg_3', type: 'success', text: 'Success text'}*/
            ]
        }
    },
    mutations: {
        setPageTitle(state, title) {
            state.app.pageTitle = title
        },
        addLogMessage(state, msg, liveTime = 7000) {
            if (msg.id === undefined) {
                index++;
                msg.id = 'logger_message_' + index;
            }
            state.logger.messages.push(msg)
            setTimeout(() => {
                this.commit('deleteLogMessage', msg.id)
            }, liveTime)
        },
        deleteLogMessage(state, id) {
            state.logger.messages.forEach((msg, i) => {
                if (msg.id === id) {
                    state.logger.messages.splice(i, 1)
                }
            })
        }
    }
})