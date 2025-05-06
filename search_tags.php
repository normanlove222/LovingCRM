<?php
require_once 'init.php';

$search = $_POST['search'] ?? '';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$records_per_page = isset($_POST['records_per_page']) ? (int)$_POST['records_per_page'] : 25;

try {
    $total_records = getTagsCount($pdo, $search);
    $pagination_vars = getPaginationVars($total_records, $records_per_page, $page);
    $tags = getTagsData($pdo, $pagination_vars, $search);

    // Build rows HTML
    $rows = '';
    foreach ($tags as $tag) {
        $rows .= '<tr>';
        $rows .= '<td><input type="checkbox" class="form-check-input tag-checkbox" data-tag-id="' . $tag['tag_id'] . '"></td>';
        $rows .= '<td>' . htmlspecialchars($tag['tag_id']) . '</td>';
        $rows .= '<td>' . htmlspecialchars($tag['name']) . '</td>';
        $rows .= '<td><button class="show-number-btn">Show Number</button></td>';
        $rows .= '<td><button class="show-contacts-btn">contacts</button></td>';
        $rows .= '</tr>';
    }

    // Build pagination HTML
    $pagination = '';
    if ($pagination_vars['total_pages'] > 1) {
        if ($page > 1) {
            $pagination .= '<li class="page-item"><a class="page-link" href="?page=' . ($page - 1) . '">Previous</a></li>';
        }

        for ($i = 1; $i <= $pagination_vars['total_pages']; $i++) {
            $active = ($page == $i) ? 'active' : '';
            $pagination .= '<li class="page-item ' . $active . '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
        }

        if ($page < $pagination_vars['total_pages']) {
            $pagination .= '<li class="page-item"><a class="page-link" href="?page=' . ($page + 1) . '">Next</a></li>';
        }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'rows' => $rows,
        'pagination' => $pagination,
        'total_records' => $total_records
    ]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}