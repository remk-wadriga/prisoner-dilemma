import Vue from 'vue'
import Router from 'vue-router'
import Login from '@/components/index/Login'
import Home from '@/components/index/Home'
import Register from '@/components/index/Register'
import Account from '@/components/user/Account'
import StrategyCreate from '@/components/strategy/Create'
import StrategyView from '@/components/strategy/View'
import StrategyUpdate from '@/components/strategy/Update'
import Game from '@/components/game/Game'
import GamesList from '@/components/game/List'
import TotalStatistics from '@/components/statistics/total/TotalStatistics'
import StrategyStatistics from '@/components/statistics/strategy/StrategyStatistics'
import GameStatistics from '@/components/statistics/game/GameStatistics'
import Tournament from '@/components/tournament/Tournament'

Vue.use(Router)

export default new Router({
    routes: [
        {
            path: '/',
            name: 'app_homepage',
            component: Home
        },
        {
            path: '/login',
            name: 'app_login',
            component: Login
        },
        {
            path: '/register',
            name: 'app_register',
            component: Register
        },
        {
            path: '/account',
            name: 'user_account',
            component: Account
        },
        {
            path: '/strategy',
            name: 'strategy_create',
            component: StrategyCreate
        },
        {
            path: '/strategy/:id',
            name: 'strategy_view',
            component: StrategyView
        },
        {
            path: '/strategy/:id/update',
            name: 'strategy_update',
            component: StrategyUpdate
        },
        {
            path: '/game/:id',
            name: 'game_view',
            component: Game
        },
        {
            path: '/game',
            name: 'game_start',
            component: Game
        },
        {
            path: '/games',
            name: 'games_list',
            component: GamesList
        },
        {
            path: '/total-statistics',
            name: 'total_statistics',
            component: TotalStatistics
        },
        {
            path: '/strategy-statistics/:id',
            name: 'strategy_statistics',
            component: StrategyStatistics
        },
        {
            path: '/game-statistics/:id',
            name: 'game_statistics',
            component: GameStatistics
        },
        {
            path: '/tournament',
            name: 'tournament_start',
            component: Tournament
        }
    ]
})
