<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-[60] flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-2xl max-w-sm w-full mx-4 transform transition-all">
        <div class="bg-red-50 px-6 py-5 border-b border-red-200">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="text-red-600 text-2xl"><i class="fas fa-exclamation-circle"></i></span>
                Clear All Notifications?
            </h3>
            <p class="text-sm text-gray-600 mt-2">This action cannot be undone. All notifications will be permanently deleted.</p>
        </div>
        <div class="px-6 py-4 flex gap-3">
            <button onclick="closeConfirmationModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 transition">
                Cancel
            </button>
            <button onclick="confirmDeleteAll()" class="flex-1 px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition flex items-center justify-center gap-2">
                <i class="fas fa-trash"></i>Delete All
            </button>
        </div>
    </div>
</div>

<!-- Notifications Modal -->
<div id="notificationsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-end md:items-center justify-end md:justify-center">
    <div class="bg-white rounded-t-lg md:rounded-lg shadow-2xl max-w-2xl w-full md:mx-4 md:max-h-[600px] max-h-[80vh] flex flex-col transform transition-all duration-300">
        <!-- Header - Stylish -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-5 flex justify-between items-center rounded-t-lg">
            <div>
                <h3 class="text-xl font-bold text-white">Notifications</h3>
                <p class="text-blue-100 text-sm mt-1"><i class="fas fa-bell mr-2"></i><span id="notifCountText">Loading...</span></p>
            </div>
            <button onclick="closeNotificationsModal()" class="text-white hover:bg-blue-500 p-2 rounded-full transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Notifications List -->
        <div id="notificationsContainer" class="flex-1 overflow-y-auto divide-y divide-gray-200">
            <div id="notificationsLoading" class="px-6 py-8 text-center text-gray-500">
                <i class="fas fa-spinner fa-spin mr-2 text-2xl"></i>
                <p class="mt-2">Loading notifications...</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex gap-3 justify-between rounded-b-lg">
            <div class="flex gap-2">
                <button onclick="markAllAsRead()" class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                    <i class="fas fa-check-double mr-2"></i>Mark all read
                </button>
                <button onclick="deleteAllNotifications()" class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">
                    <i class="fas fa-trash mr-2"></i>Clear all
                </button>
            </div>
            @if(in_array(auth()->user()->role, ['admin', 'hr']))
            <a href="{{ route('notifications.create') }}" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition flex items-center">
                <i class="fas fa-plus mr-2"></i>Create
            </a>
            @endif
        </div>
    </div>
</div>

<script>
    // Open notifications modal
    function openNotificationsModal() {
        document.getElementById('notificationsModal').classList.remove('hidden');
        loadNotifications();
    }

    // Close notifications modal
    function closeNotificationsModal() {
        document.getElementById('notificationsModal').classList.add('hidden');
    }

    // Load notifications
    async function loadNotifications() {
        try {
            const response = await fetch('{{ route("notifications.unread") }}');
            const data = await response.json();
            
            const container = document.getElementById('notificationsContainer');
            const loading = document.getElementById('notificationsLoading');
            const countText = document.getElementById('notifCountText');
            
            loading.style.display = 'none';

            // Update count text
            countText.textContent = data.count + ' unread notification' + (data.count !== 1 ? 's' : '');

            if (data.notifications.length === 0) {
                container.innerHTML = '<div class="px-6 py-12 text-center text-gray-500"><i class="fas fa-inbox text-4xl mb-3 block opacity-30"></i><p>No notifications yet</p></div>';
                return;
            }

            container.innerHTML = data.notifications.map(notification => `
                <div class="px-6 py-4 hover:bg-blue-50 transition border-b-0 last:border-b-0 notification-item">
                    <div class="flex justify-between items-start gap-4">
                        <div class="flex-1 cursor-pointer" onclick="markAsRead(${notification.id})">
                            <div class="flex items-start gap-3">
                                <div class="mt-1">
                                    <span class="inline-block w-2 h-2 bg-blue-600 rounded-full"></span>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 text-sm">${escapeHtml(notification.title)}</h4>
                                    <p class="text-sm text-gray-600 mt-1">${escapeHtml(notification.message)}</p>
                                    <div class="flex justify-between items-center mt-3">
                                        <p class="text-xs text-gray-400"><i class="fas fa-clock mr-1"></i>${formatDate(notification.created_at)}</p>
                                        <button onclick="event.stopPropagation(); deleteNotification(${notification.id})" class="text-gray-300 hover:text-red-600 transition p-1">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    // Mark notification as read
    async function markNotificationAsRead(id) {
        try {
            await fetch(`{{ route("notifications.markAsRead", ":id") }}`.replace(':id', id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });
            loadNotifications();
            updateUnreadCount();
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    // Mark notification as read (when clicking notification)
    async function markAsRead(id) {
        console.log('Marking notification as read:', id);
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            console.log('CSRF Token:', csrfToken);
            
            const response = await fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }
            });
            
            console.log('Mark as read - HTTP Status:', response.status);
            
            if (response.ok) {
                console.log('Successfully marked as read');
                await new Promise(resolve => setTimeout(resolve, 500));
                loadNotifications();
                updateUnreadCount();
            } else {
                const errorText = await response.text();
                console.error('Mark as read failed:', response.status, errorText);
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    // Mark all as read
    async function markAllAsRead() {
        console.log('Marking all as read');
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            const response = await fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }
            });
            
            console.log('Mark all as read - HTTP Status:', response.status);
            
            if (response.ok) {
                console.log('Successfully marked all as read');
                await new Promise(resolve => setTimeout(resolve, 500));
                loadNotifications();
                updateUnreadCount();
            } else {
                const errorText = await response.text();
                console.error('Mark all as read failed:', response.status, errorText);
            }
        } catch (error) {
            console.error('Error marking all as read:', error);
        }
    }

    // Delete notification
    async function deleteNotification(id) {
        console.log('Deleting notification:', id);
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            const response = await fetch(`/notifications/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }
            });
            
            console.log('Delete notification - HTTP Status:', response.status);
            
            if (response.ok) {
                console.log('Successfully deleted notification');
                await new Promise(resolve => setTimeout(resolve, 500));
                loadNotifications();
                updateUnreadCount();
            } else {
                const errorText = await response.text();
                console.error('Delete failed:', response.status, errorText);
            }
        } catch (error) {
            console.error('Error deleting notification:', error);
        }
    }

    // Delete all notifications
    async function deleteAllNotifications() {
        // Show beautiful confirmation modal instead of browser confirm
        document.getElementById('confirmationModal').classList.remove('hidden');
    }

    // Close confirmation modal
    function closeConfirmationModal() {
        document.getElementById('confirmationModal').classList.add('hidden');
    }

    // Confirm delete all
    async function confirmDeleteAll() {
        console.log('Confirming delete all notifications');
        closeConfirmationModal();
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            console.log('CSRF Token:', csrfToken);
            
            const response = await fetch('/notifications', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }
            });
            
            console.log('Delete all - HTTP Status:', response.status);
            
            const responseText = await response.text();
            console.log('Delete all - Response body:', responseText);
            
            let data = {};
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.error('Failed to parse response as JSON');
            }
            
            if (response.ok) {
                console.log('Successfully cleared all notifications');
                await new Promise(resolve => setTimeout(resolve, 500));
                loadNotifications();
                updateUnreadCount();
            } else {
                const errorMsg = data.message || 'Failed to clear notifications';
                console.error('Delete all failed:', response.status, errorMsg);
                alert(errorMsg);
            }
        } catch (error) {
            console.error('Error deleting all notifications:', error);
            alert('Failed to delete notifications. Check console for details.');
        }
    }

    // Update unread count badge
    async function updateUnreadCount() {
        try {
            const response = await fetch('{{ route("notifications.unreadCount") }}');
            const data = await response.json();
            
            const badge = document.getElementById('notificationBadge');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Error updating unread count:', error);
        }
    }

    // Format date
    function formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        if (diffDays < 7) return `${diffDays}d ago`;
        
        return date.toLocaleDateString();
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Update unread count on page load
    document.addEventListener('DOMContentLoaded', updateUnreadCount);

    // Refresh notifications every 30 seconds
    setInterval(updateUnreadCount, 30000);
</script>
