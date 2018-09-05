/*Vue.component('vu-friend-component', {
    props: ['friend'],
    filters: {
        fullName(value) {
            return `${value.lastName} ${value.firstName}`
        },
        inOneYEarAge(age) {
            return age + 1
        }
    },
    methods: {
        incrementAge(friend) {
            friend.age++;
        },
        decrementAge(friend) {
            friend.age--;
        }
    },
    template: `
        <div>
            <h4>{{ friend|fullName }}</h4>
            <h5>Age: {{ friend.age|inOneYEarAge }}</h5>
            <button @click="incrementAge(friend)">+</button>
            <button @click="decrementAge(friend)">-</button>
            <input type="text" v-model="friend.firstName">
            <input type="text" v-model="friend.lastName">
        </div>
    `
});*/

/*
const app = new Vue({
    el: '#vu_app',
    data: {
        editFriend: null,
        friends: []
    },
    computed: {

    },
    filters: {

    },
    methods: {
        deleteFriend(friend, index) {
            fetch('/friend/' + friend.id, {
                    method: 'DELETE'
                })
                .then(() => {
                    this.friends.splice(index, 1);
                });
        },
        updateFriend(friend) {
            fetch('/friend/' + friend.id, {
                    method: 'PUT',
                    headers: {'Content-type': 'application/json'},
                    body: JSON.stringify(friend)
                })
                .then(() => {
                    this.editFriend = null;
                });
        }
    },
    mounted() {
        fetch('/friends')
            .then(response => response.json())
            .then((data) => {
                this.friends = data;
            });
    },
    template: `
        <div>
            <ul>
                <li v-for="friend, index in friends">
                    <div v-if="editFriend === friend.id">
                        <input type="text" v-model="friend.name" @keyup.13="updateFriend(friend)">
                        <button @click="updateFriend(friend)">Save</button>
                    </div>
                    <div v-else>
                        {{friend.name}}
                        <button @click="editFriend = friend.id">edit</button>
                        <button @click="deleteFriend(friend, index)">x</button>
                    </div>
                </li>
            </ul>
        </div>
    `
});*/


