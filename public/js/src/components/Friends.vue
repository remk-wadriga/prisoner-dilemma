<template>
    <div class="hello">
        <h1>Friends</h1>
        <ul>
            <li v-for="friend in FriendsStore.friends">{{ friend.name }}</li>
        </ul>
        <input type="text" v-model="newFriend" @keyup.13="addFriend(newFriend)">
    </div>
</template>

<script>
import FriendsStore from '../stores/FriendsStore'

export default {
    name: 'Friends',
    data() {
        return {
            newFriend: null,
            FriendsStore: FriendsStore.data
        }
    },
    methods: {
        addFriend(name) {
            FriendsStore.methods.addFriend(name);
            this.newFriend = null;
        }
    },
    mounted() {
        FriendsStore.methods.getFriendsList();
    }
}
</script>

<style scoped>
</style>
