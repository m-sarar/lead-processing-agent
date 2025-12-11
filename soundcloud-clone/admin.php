<?php
require_once 'includes/functions.php';
require_once 'includes/database.php';

requireLogin();

$auth = new Auth();
$user = $auth->getCurrentUser();
$db = new Database();

// Check if user is admin (simplified - in production, use proper role system)
$isAdmin = true; // TODO: Implement proper admin check

if (!$isAdmin) {
    die("Access denied. Admin privileges required.");
}

$error = '';
$success = '';

// Handle actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'delete_user' && isset($_GET['user_id'])) {
        $userId = $_GET['user_id'];

        // Delete user and their tracks
        $stmt = $db->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $userId);

        if ($stmt->execute()) {
            $success = "User deleted successfully";
        } else {
            $error = "Failed to delete user: " . $stmt->error;
        }
    }

    if ($action === 'delete_track' && isset($_GET['track_id'])) {
        $trackId = $_GET['track_id'];

        // Delete track
        $stmt = $db->conn->prepare("DELETE FROM tracks WHERE id = ?");
        $stmt->bind_param('i', $trackId);

        if ($stmt->execute()) {
            $success = "Track deleted successfully";
        } else {
            $error = "Failed to delete track: " . $stmt->error;
        }
    }

    if ($action === 'approve_comment' && isset($_GET['comment_id'])) {
        $commentId = $_GET['comment_id'];

        // Approve comment
        $stmt = $db->conn->prepare("UPDATE comments SET is_approved = TRUE WHERE id = ?");
        $stmt->bind_param('i', $commentId);

        if ($stmt->execute()) {
            $success = "Comment approved successfully";
        } else {
            $error = "Failed to approve comment: " . $stmt->error;
        }
    }
}

// Get stats
$totalUsers = 0;
$totalTracks = 0;
$totalComments = 0;

$result = $db->conn->query("SELECT COUNT(*) as count FROM users");
if ($result) $totalUsers = $result->fetch_assoc()['count'];

$result = $db->conn->query("SELECT COUNT(*) as count FROM tracks");
if ($result) $totalTracks = $result->fetch_assoc()['count'];

$result = $db->conn->query("SELECT COUNT(*) as count FROM comments");
if ($result) $totalComments = $result->fetch_assoc()['count'];

// Get recent users
$recentUsers = [];
$result = $db->conn->query("SELECT id, username, email, full_name, created_at FROM users ORDER BY created_at DESC LIMIT 10");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentUsers[] = $row;
    }
}

// Get pending comments
$pendingComments = [];
$stmt = $db->conn->prepare("SELECT c.*, u.username, t.title FROM comments c LEFT JOIN users u ON c.user_id = u.id LEFT JOIN tracks t ON c.track_id = t.id WHERE c.is_approved = FALSE ORDER BY c.created_at DESC LIMIT 10");
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pendingComments[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Phonetics Platform</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <div class="logo">🎵 Phonetics Platform Admin</div>
                <div class="nav-links">
                    <a href="index.php">Home</a>
                    <a href="browse.php">Browse</a>
                </div>
                <div class="auth-buttons">
                    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </nav>
        </header>

        <main class="admin-page">
            <div class="admin-header">
                <h1>Admin Dashboard</h1>
                <p>Manage your phonetics platform</p>
            </div>

            <div class="admin-stats">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-value"><?php echo $totalUsers; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-music"></i></div>
                    <div class="stat-value"><?php echo $totalTracks; ?></div>
                    <div class="stat-label">Total Tracks</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-comments"></i></div>
                    <div class="stat-value"><?php echo $totalComments; ?></div>
                    <div class="stat-label">Total Comments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="stat-value"><?php echo count($pendingComments); ?></div>
                    <div class="stat-label">Pending Comments</div>
                </div>
            </div>

            <div class="admin-sections">
                <section class="admin-users">
                    <h2>Recent Users</h2>

                    <?php if ($error): ?>
                        <div class="alert error"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <div class="users-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Name</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentUsers)): ?>
                                    <tr>
                                        <td colspan="6" class="no-data">No users found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <a href="profile.php?id=<?php echo $user['id']; ?>" class="btn-small"><i class="fas fa-eye"></i> View</a>
                                                <a href="admin.php?action=delete_user&user_id=<?php echo $user['id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this user?')"><i class="fas fa-trash"></i> Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="admin-comments">
                    <h2>Pending Comments</h2>

                    <?php if (empty($pendingComments)): ?>
                        <div class="no-data">
                            <i class="fas fa-check-circle fa-3x"></i>
                            <p>No pending comments</p>
                        </div>
                    <?php else: ?>
                        <div class="comments-list">
                            <?php foreach ($pendingComments as $comment): ?>
                                <div class="comment-admin">
                                    <div class="comment-header">
                                        <div class="comment-info">
                                            <strong><?php echo htmlspecialchars($comment['username'] ?? 'Unknown'); ?></strong>
                                            <span class="comment-track">on <?php echo htmlspecialchars($comment['title'] ?? 'Unknown Track'); ?></span>
                                            <span class="comment-time"><?php echo date('M j, Y \a\t g:i A', strtotime($comment['created_at'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="comment-content">
                                        <p><?php echo htmlspecialchars($comment['content']); ?></p>
                                    </div>
                                    <div class="comment-actions">
                                        <a href="admin.php?action=approve_comment&comment_id=<?php echo $comment['id']; ?>" class="btn-success"><i class="fas fa-check"></i> Approve</a>
                                        <a href="#" class="btn-danger"><i class="fas fa-times"></i> Reject</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="admin-tools">
                    <h2>Admin Tools</h2>
                    <div class="tools-grid">
                        <div class="tool-card">
                            <i class="fas fa-database fa-3x"></i>
                            <h3>Database Backup</h3>
                            <p>Create a backup of your database</p>
                            <button class="btn-secondary" onclick="alert('Database backup feature would be implemented here')">Backup Now</button>
                        </div>
                        <div class="tool-card">
                            <i class="fas fa-sync fa-3x"></i>
                            <h3>Rebuild Cache</h3>
                            <p>Clear and rebuild system cache</p>
                            <button class="btn-secondary" onclick="alert('Cache rebuild feature would be implemented here')">Rebuild Cache</button>
                        </div>
                        <div class="tool-card">
                            <i class="fas fa-chart-bar fa-3x"></i>
                            <h3>View Analytics</h3>
                            <p>Detailed platform statistics</p>
                            <button class="btn-secondary" onclick="alert('Analytics feature would be implemented here')">View Analytics</button>
                        </div>
                        <div class="tool-card">
                            <i class="fas fa-cog fa-3x"></i>
                            <h3>System Settings</h3>
                            <p>Configure platform settings</p>
                            <button class="btn-secondary" onclick="alert('System settings feature would be implemented here')">Configure</button>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <footer>
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Phonetics Platform Admin</h4>
                    <p>Administrative control panel for managing the platform</p>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Phonetics Platform. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>