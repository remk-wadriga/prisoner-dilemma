import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

var index = 0;

export default new Vuex.Store({
    state: {
        app: {
            pageTitle: '',
            pageTopButtons: [],
            breadcrumbs: [],
            contentTitle: '',
            closeModalCallback: null,
            leftMenuItems: [
                {
                    name: 'Item 1',
                    url: '/url-somewhere-1',
                },
                {
                    name: 'Item 2',
                    url: '/url-somewhere-2',
                },
                {
                    name: 'Item 3',
                    url: '/url-somewhere-3',
                }
            ]
        },
        logger: {
            messages: [
                /*{id: 'msg_1', type: 'danger', text: 'Danger text'},
                {id: 'msg_2', type: 'info', text: 'Info text'},
                {id: 'msg_3', type: 'success', text: 'Success text'}*/
            ]
        },
        strategy: {
            selectedId: null,
            decisions: []
        }
    },
    mutations: {
        setPageTitle(state, title) {
            state.app.pageTitle = title
        },
        setPageTopButtons(state, buttons) {
            buttons.forEach(btn => {
                btn.click = JSON.stringify(btn.click)
            })
            state.app.pageTopButtons = buttons
        },
        setBreadcrumbs(state, breadcrumbs) {
            state.app.breadcrumbs = breadcrumbs
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
        },
        setContentTitle(state, title) {
            state.app.contentTitle = title
        },
        setCloseModalCallback(state, callback) {
            state.app.closeModalCallback = callback
        },
        selectedStrategyId(state, strategyId) {
            state.strategy.selectedId = strategyId
        },
        setStrategyDecisions(state, decisions) {
            state.strategy.decisions = decisions
        }
    }
})