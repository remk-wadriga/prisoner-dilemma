import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

var index = 0;

export default new Vuex.Store({
    state: {
        logger: {
            messages: []
        }
    },
    mutations: {
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