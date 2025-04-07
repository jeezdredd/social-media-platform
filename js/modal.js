document.addEventListener('DOMContentLoaded', function () {
    const followersBtn = document.getElementById('followers-stat');
    const followingBtn = document.getElementById('following-stat');
    const userListModal = document.getElementById('userListModal');
    const userListTitle = document.getElementById('userListTitle');
    const userList = document.getElementById('userList');
    const closeUserListModal = document.getElementById('closeUserListModal');

    followersBtn.addEventListener('click', function () {
        loadUserList('followers', currentUserId);
    });

    followingBtn.addEventListener('click', function () {
        loadUserList('following', currentUserId);
    });

    closeUserListModal.addEventListener('click', function () {
        userListModal.style.display = 'none';
    });

    window.addEventListener('click', function (event) {
        if (event.target == userListModal) {
            userListModal.style.display = 'none';
        }
    });

    function loadUserList(type, userId) {
        userListTitle.textContent = type === 'followers' ? 'Followers' : 'Following';
        userList.innerHTML = '<p>Loading...</p>';
        userListModal.style.display = 'block';

        fetch(`api/get_users.php?type=${type}&user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.users.length === 0) {
                        userList.innerHTML = '<p>No users found</p>';
                    } else {
                        userList.innerHTML = '';
                        data.users.forEach(user => {
                            const item = document.createElement('div');
                            item.className = 'user-list-item';
                            const profilePic = user.profile_pic ? user.profile_pic : 'upload/default.jpg';

                            item.innerHTML = `
                                <img src="${profilePic}" alt="Profile picture">
                                <a href="${user.id === currentUserId ? 'dashboard.php' : 'profile/user_profile.php?id=' + user.id}">
                                    ${user.username}
                                </a>
                            `;
                            userList.appendChild(item);
                        });
                    }
                } else {
                    userList.innerHTML = `<p>Error: ${data.message}</p>`;
                }
            })
            .catch(error => {
                userList.innerHTML = `<p>Error: ${error.message}</p>`;
            });
    }
});