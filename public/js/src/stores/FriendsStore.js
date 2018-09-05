const FriendsStore = {
    data: {
        friends: []
    },
    methods: {
        getFriendsList() {
            fetch('http://strategy.local/friends')
                .then(response => response.json())
                .then((data) => {
                    FriendsStore.data.friends = data;
                })
        },
        addFriend(name) {
            fetch('http://strategy.local/friend', {
                    headers: {'Content-Type': 'application/json'},
                    method: 'POST',
                    body: JSON.stringify({name})
                })
                .then(response => response.json())
                .then((data) => {
                    FriendsStore.data.friends.push(data)
                })
            //FriendsStore.data.friends.push(name);
        }
    }
};

export default FriendsStore;