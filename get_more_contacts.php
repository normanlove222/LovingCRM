<?php
require_once('init.php');

$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 25;

$contacts = getContacts($limit, $offset);

foreach ($contacts as $contact):
?>
    <tr class="contact-row" data-id="<?= $contact['contact_id'] ?>" data-href="edit_contact.php?id=<?= $contact['contact_id'] ?>">
        <td>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="<?= $contact['contact_id'] ?>">
            </div>
        </td>
        <td>
            <div class="fw-bold fs-5"><?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?></div>
            <div class="text-muted"><?= htmlspecialchars($contact['email']) ?></div>
        </td>
        <td class="fs-5">
            <?= htmlspecialchars($contact['phone']) ?>
        </td>
    </tr>
<?php endforeach; ?>