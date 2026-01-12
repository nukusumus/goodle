function getUserId() {
    let userId = localStorage.getItem('user_id');
    if (!userId) {
        userId = Math.random().toString(36).substr(2, 9);
        localStorage.setItem('user_id', userId);
    }
    return userId;
}

const API_BASE = 'backend/api/';