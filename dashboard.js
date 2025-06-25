
let currentSection = 'dashboard';
let charts = {};


document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    fetchDashboardStats();
    fetchUsers(); 
    fetchChats();
});

function setupEventListeners() {

    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.getAttribute('data-section');
            showSection(section);
         
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });


    document.getElementById('mobileToggle').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('mobile-visible');
    });

    function viewUser(userId) {
        fetch(`users.php?id=${userId}`)
            .then(res => res.json())
            .then(data => {
                const user = data.user;

                const userDetailsHTML = `
                    <p><strong>ID:</strong> ${user.id}</p>
                    <p><strong>Full Name:</strong> ${user.full_name}</p>
                    <p><strong>Email:</strong> ${user.email}</p>
                `;
                document.getElementById('userDetails').innerHTML = userDetailsHTML;
                document.getElementById('userModal').style.display = 'block';
            })
            .catch(err => {
                console.error('Error fetching user:', err);
                alert('Failed to load user details.');
            });
    }

    function closeUserModal() {
        document.getElementById('userModal').style.display = 'none';
    }


    document.getElementById('userSearch')?.addEventListener('input', function() {
        filterUsers(this.value);
    });
    document.getElementById('chatSearch')?.addEventListener('input', function() {
        filterChats(this.value);
    });

    document.getElementById('maintenanceToggle')?.addEventListener('change', function() {
        if (this.checked) {
            alert('Maintenance mode enabled. All users will see a maintenance message.');
        } else {
            alert('Maintenance mode disabled. System is back online.');
        }
    });
}

function showSection(sectionId) {

    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });

    document.getElementById(sectionId).classList.add('active');
    currentSection = sectionId;

    if (sectionId === 'analytics') {
        setTimeout(() => {
            initializeAnalyticsCharts();
        }, 100);
    }
}

function fetchDashboardStats() {
    fetch('stats.php')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const stats = data.stats;

                const statValues = [
                    stats.total_users,
                    stats.active_sessions,
                    stats.ai_conversations,
                    stats.api_calls_today,
                    stats.All_Time_API_Call,
                    stats.system_health
                ];

                document.querySelectorAll('.stat-value').forEach((el, i) => {
                    el.textContent = statValues[i];
                });
            }
        });
}


function fetchUsers() {
    fetch('users.php')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const tbody = document.getElementById('usersTableBody');
                tbody.innerHTML = '';
                data.users.forEach(user => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>#${user.id}</td>
                        <td>${user.full_name}</td>
                        <td>${user.email}</td>
                        <td>
                            <button class="btn btn-secondary btn-sm" onclick="editUser(${user.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id}, this)">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="fetchUserConversations(${user.id})">
                                <i class="fas fa-comments"></i> 
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        });
}

function filterUsers(searchTerm) {
    const tbody = document.getElementById('usersTableBody');
    const rows = tbody.querySelectorAll('tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm.toLowerCase()) ? '' : 'none';
    });
}

function filterChats(searchTerm) {
    const chatLogs = document.querySelectorAll('.chat-log');
    chatLogs.forEach(log => {
        const text = log.textContent.toLowerCase();
        log.style.display = text.includes(searchTerm.toLowerCase()) ? '' : 'none';
    });
}

function toggleChat(chatId) {
    const chatMessages = document.getElementById(chatId);
    const isExpanded = chatMessages.classList.contains('expanded');
    
    document.querySelectorAll('.chat-messages').forEach(chat => {
        chat.classList.remove('expanded');
    });
    
    if (!isExpanded) {
        chatMessages.classList.add('expanded');
    }
}

function editUser(userId) {
    
}

function deleteUser(userId, btn) {
    if (!confirm('Are you sure you want to delete this user?')) return;
    fetch('delete_user.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({user_id: userId})
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            // Remove the user row from the table
            const row = btn.closest('tr');
            if (row) row.remove();
            alert('User deleted successfully.');
        } else {
            alert('Failed to delete user: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(() => alert('Failed to delete user.'));
}

setInterval(() => {

    const activeSessionsElement = document.querySelector('.stat-card:nth-child(2) .stat-value');
    if (activeSessionsElement) {
        const currentValue = parseInt(activeSessionsElement.textContent.replace(',', ''));
        const newValue = currentValue + Math.floor(Math.random() * 10) - 5;
        activeSessionsElement.textContent = newValue.toLocaleString();
    }
}, 5000);

function testShaktiAPI() {
    const input = document.getElementById('aiTestInput').value.trim();
    if (!input) {
        alert('Please enter a message.');
        return;
    }

    document.getElementById('aiTestResponse').style.display = 'block';
    document.getElementById('aiTestResponseText').innerHTML = '<em>Loading...</em>';

    fetch('mychat.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({message: input})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.response) {
            document.getElementById('aiTestResponseText').textContent = data.response;
        } else {
            document.getElementById('aiTestResponseText').textContent = data.error || 'No response from AI.';
        }
    })
    .catch(err => {
        document.getElementById('aiTestResponseText').textContent = 'API call failed: ' + err;
    });
}

function fetchUserConversations(userId) {
    fetch('save_conversation.php?user_id=' + userId)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const convModal = document.getElementById('conversationModal');
                const convBody = document.getElementById('conversationBody');
                convBody.innerHTML = '';
                data.conversations.forEach(conv => {
                    const msgDiv = document.createElement('div');
                    msgDiv.className = 'message ' + (conv.role === 'user' ? 'user' : 'ai');
                    msgDiv.innerHTML = `
                        <div class="message-meta">${conv.role === 'user' ? 'User' : 'Shakti AI'} • ${conv.timestamp}</div>
                        <div>${conv.message}</div>
                    `;
                    convBody.appendChild(msgDiv);
                });
                convModal.style.display = 'block';
            } else {
                alert('No conversation history found.');
            }
        })
        .catch(err => {
            alert('Failed to load conversation history.');
        });
}

function closeConversationModal() {
    document.getElementById('conversationModal').style.display = 'none';
}

