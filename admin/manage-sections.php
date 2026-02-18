<?php
// Check authentication and role BEFORE including header
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (!hasRole('developer')) {
    header('Location: ../home.php');
    exit();
}

// Handle section actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'create' && isset($_POST['section_name']) && isset($_POST['description'])) {
            $section_name = trim($_POST['section_name']);
            $description = trim($_POST['description']);
            
            if (!empty($section_name)) {
                $stmt = $conn->prepare("INSERT INTO sections (section_name, description) VALUES (?, ?)");
                $stmt->bind_param("ss", $section_name, $description);
                if ($stmt->execute()) {
                    $success_message = "Section created successfully!";
                } else {
                    $error_message = "Failed to create section.";
                }
            } else {
                $error_message = "Section name is required.";
            }
        } elseif ($action === 'update' && isset($_POST['section_id'])) {
            $section_id = intval($_POST['section_id']);
            $section_name = trim($_POST['section_name']);
            $description = trim($_POST['description']);
            
            if (!empty($section_name)) {
                $stmt = $conn->prepare("UPDATE sections SET section_name = ?, description = ? WHERE section_id = ?");
                $stmt->bind_param("ssi", $section_name, $description, $section_id);
                if ($stmt->execute()) {
                    $success_message = "Section updated successfully!";
                } else {
                    $error_message = "Failed to update section.";
                }
            } else {
                $error_message = "Section name is required.";
            }
        } elseif ($action === 'delete' && isset($_POST['section_id'])) {
            $section_id = intval($_POST['section_id']);
            
            // Check if section has users
            $user_check = $conn->query("SELECT COUNT(*) as count FROM users WHERE section_id = $section_id")->fetch_assoc()['count'];
            
            if ($user_check > 0) {
                $error_message = "Cannot delete section with assigned users. Please reassign users first.";
            } else {
                $stmt = $conn->prepare("DELETE FROM sections WHERE section_id = ?");
                $stmt->bind_param("i", $section_id);
                if ($stmt->execute()) {
                    $success_message = "Section deleted successfully!";
                } else {
                    $error_message = "Failed to delete section.";
                }
            }
        }
    }
}

$pageTitle = "Manage Sections - Admin Dashboard";
include '../includes/header.php';

// Get all sections with member counts
$sections = $conn->query("
    SELECT s.*, COUNT(u.user_id) as member_count 
    FROM sections s 
    LEFT JOIN users u ON s.section_id = u.section_id 
    GROUP BY s.section_id 
    ORDER BY s.section_name
");

$total_sections = $conn->query("SELECT COUNT(*) as count FROM sections")->fetch_assoc()['count'];
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>🏢 Manage Sections</h2>
            <p>Create and manage fellowship sections</p>
        </div>

        <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 25px 0;">
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-gold), #f4d03f);">
                <i class="fas fa-layer-group"></i>
                <h3><?php echo $total_sections; ?></h3>
                <p>Total Sections</p>
            </div>
        </div>

        <!-- Create New Section -->
        <div class="card" style="margin-bottom: 25px;">
            <div class="section-header">
                <h3>Create New Section</h3>
            </div>
            <form method="POST" style="padding: 20px;">
                <input type="hidden" name="action" value="create">
                <div style="display: grid; grid-template-columns: 1fr 2fr auto; gap: 15px; align-items: end;">
                    <div class="form-group">
                        <label>Section Name</label>
                        <input type="text" name="section_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Brief description of the section">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Section
                    </button>
                </div>
            </form>
        </div>

        <!-- Sections Table -->
        <div class="card">
            <div class="section-header">
                <h3>All Sections</h3>
            </div>

            <?php if ($sections->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table class="spiritual-table">
                    <thead>
                        <tr>
                            <th>Section Name</th>
                            <th>Description</th>
                            <th>Members</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($section = $sections->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($section['section_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($section['description'] ?? 'No description'); ?></td>
                            <td>
                                <span class="status-badge" style="background: var(--spiritual-blue); color: white;">
                                    <?php echo $section['member_count']; ?> members
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($section['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="editSection(<?php echo $section['section_id']; ?>, '<?php echo htmlspecialchars($section['section_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($section['description'] ?? '', ENT_QUOTES); ?>')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <?php if ($section['member_count'] == 0): ?>
                                <form method="POST" style="display: inline; margin-left: 5px;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="section_id" value="<?php echo $section['section_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this section?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                                <?php else: ?>
                                <button class="btn btn-secondary btn-sm" disabled title="Cannot delete section with members">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-layer-group"></i>
                <h3>No Sections Found</h3>
                <p>Create your first section above</p>
            </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</main>

<!-- Edit Section Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: var(--border-radius); width: 90%; max-width: 500px;">
        <h3 style="margin-bottom: 20px;">Edit Section</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="section_id" id="editSectionId">
            <div class="form-group">
                <label>Section Name</label>
                <input type="text" name="section_name" id="editSectionName" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" id="editSectionDescription" class="form-control">
            </div>
            <div style="display: flex; gap: 10px; justify-content: end; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Section</button>
            </div>
        </form>
    </div>
</div>

<script>
function editSection(id, name, description) {
    document.getElementById('editSectionId').value = id;
    document.getElementById('editSectionName').value = name;
    document.getElementById('editSectionDescription').value = description;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

<?php include '../includes/footer.php'; ?>