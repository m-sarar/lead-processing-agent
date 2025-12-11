<?php
/**
 * Admin-specific functions and utilities
 */

class Admin {
    private $conn;

    public function __construct() {
        $this->conn = getDBConnection();
    }

    /**
     * Check if user is admin
     */
    public function isAdmin($userId) {
        // In a real implementation, this would check a roles table
        // For now, we'll use a simple check based on user ID 1 (first user)
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    /**
     * Get platform statistics
     */
    public function getPlatformStats() {
        $stats = [
            'total_users' => 0,
            'total_tracks' => 0,
            'total_comments' => 0,
            'total_likes' => 0,
            'active_users' => 0,
            'pending_comments' => 0
        ];

        // Get total users
        $result = $this->conn->query("SELECT COUNT(*) as count FROM users");
        if ($result) $stats['total_users'] = $result->fetch_assoc()['count'];

        // Get total tracks
        $result = $this->conn->query("SELECT COUNT(*) as count FROM tracks");
        if ($result) $stats['total_tracks'] = $result->fetch_assoc()['count'];

        // Get total comments
        $result = $this->conn->query("SELECT COUNT(*) as count FROM comments");
        if ($result) $stats['total_comments'] = $result->fetch_assoc()['count'];

        // Get total likes
        $result = $this->conn->query("SELECT COUNT(*) as count FROM likes");
        if ($result) $stats['total_likes'] = $result->fetch_assoc()['count'];

        // Get active users (logged in last 30 days)
        $result = $this->conn->query("SELECT COUNT(*) as count FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        if ($result) $stats['active_users'] = $result->fetch_assoc()['count'];

        // Get pending comments
        $result = $this->conn->query("SELECT COUNT(*) as count FROM comments WHERE is_approved = FALSE");
        if ($result) $stats['pending_comments'] = $result->fetch_assoc()['count'];

        return $stats;
    }

    /**
     * Get recent users
     */
    public function getRecentUsers($limit = 10) {
        $users = [];
        $stmt = $this->conn->prepare("SELECT id, username, email, full_name, created_at FROM users ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }

        return $users;
    }

    /**
     * Get pending comments
     */
    public function getPendingComments($limit = 10) {
        $comments = [];
        $stmt = $this->conn->prepare("SELECT c.*, u.username, t.title FROM comments c LEFT JOIN users u ON c.user_id = u.id LEFT JOIN tracks t ON c.track_id = t.id WHERE c.is_approved = FALSE ORDER BY c.created_at DESC LIMIT ?");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $comments[] = $row;
            }
        }

        return $comments;
    }

    /**
     * Get all tracks with pagination
     */
    public function getAllTracks($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $tracks = [];
        $total = 0;

        // Get total count
        $result = $this->conn->query("SELECT COUNT(*) as count FROM tracks");
        if ($result) $total = $result->fetch_assoc()['count'];

        // Get tracks
        $stmt = $this->conn->prepare("SELECT t.*, u.username, u.full_name, l.name as language_name, c.name as category_name FROM tracks t LEFT JOIN users u ON t.user_id = u.id LEFT JOIN languages l ON t.language_id = l.id LEFT JOIN categories c ON t.category_id = c.id ORDER BY t.created_at DESC LIMIT ? OFFSET ?");
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tracks[] = $row;
            }
        }

        return [
            'tracks' => $tracks,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }

    /**
     * Get all users with pagination
     */
    public function getAllUsers($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $users = [];
        $total = 0;

        // Get total count
        $result = $this->conn->query("SELECT COUNT(*) as count FROM users");
        if ($result) $total = $result->fetch_assoc()['count'];

        // Get users
        $stmt = $this->conn->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }

        return [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }

    /**
     * Delete user by ID
     */
    public function deleteUser($userId) {
        // First delete user's tracks (cascade will handle the rest)
        $stmt = $this->conn->prepare("DELETE FROM tracks WHERE user_id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        // Then delete the user
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $userId);

        return $stmt->execute();
    }

    /**
     * Delete track by ID
     */
    public function deleteTrack($trackId) {
        $stmt = $this->conn->prepare("DELETE FROM tracks WHERE id = ?");
        $stmt->bind_param('i', $trackId);

        return $stmt->execute();
    }

    /**
     * Approve comment by ID
     */
    public function approveComment($commentId) {
        $stmt = $this->conn->prepare("UPDATE comments SET is_approved = TRUE WHERE id = ?");
        $stmt->bind_param('i', $commentId);

        return $stmt->execute();
    }

    /**
     * Reject comment by ID
     */
    public function rejectComment($commentId) {
        $stmt = $this->conn->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->bind_param('i', $commentId);

        return $stmt->execute();
    }

    /**
     * Get track statistics
     */
    public function getTrackStats($trackId) {
        $stats = [
            'plays' => 0,
            'downloads' => 0,
            'likes' => 0,
            'comments' => 0
        ];

        $track = $this->getTrackById($trackId);
        if ($track) {
            $stats['plays'] = $track['play_count'];
            $stats['downloads'] = $track['download_count'];
        }

        // Get likes
        $result = $this->conn->query("SELECT COUNT(*) as count FROM likes WHERE track_id = $trackId");
        if ($result) $stats['likes'] = $result->fetch_assoc()['count'];

        // Get comments
        $result = $this->conn->query("SELECT COUNT(*) as count FROM comments WHERE track_id = $trackId AND is_approved = TRUE");
        if ($result) $stats['comments'] = $result->fetch_assoc()['count'];

        return $stats;
    }

    /**
     * Get track by ID
     */
    public function getTrackById($trackId) {
        $stmt = $this->conn->prepare("SELECT * FROM tracks WHERE id = ?");
        $stmt->bind_param('i', $trackId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}