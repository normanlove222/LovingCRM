// modules/contacts/list.php
<?php
require_once('init.php');

$stmt = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC");
$contacts = $stmt->fetchAll();

include 'header.php';
include 'sidebar.php';
?>

<div class="content">
    <h2>Contacts</h2>
    <a href="add.php" class="btn">Add New Contact</a>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Tags</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contacts as $contact): ?>
                <tr>
                    <td><?= htmlspecialchars($contact['name']) ?></td>
                    <td><?= htmlspecialchars($contact['email']) ?></td>
                    <td><?= htmlspecialchars($contact['phone']) ?></td>
                    <td><?= htmlspecialchars($contact['tags']) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $contact['contact_id'] ?>">Edit</a>
                        <a href="delete.php?id=<?= $contact['contact_id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>