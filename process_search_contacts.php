<?php
require_once 'init.php'; // Contains DB connection $pdo, functions like console_log, getPaginationVars etc.
include 'header.php';   // Includes HTML head, meta tags, CSS links
include 'menu.php';     // Includes the navigation menu

// --- PHP Search Logic & Pagination Setup ---

// Pagination setup
$records_per_page = 200; // Or your preferred number
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1; // Ensure current page is at least 1

// Initialize arrays to build the query
$conditions = [];
$params = [];
$joins = [];
$group_by = [];
$having = [];

// --- PHP Functions for Processing Search Criteria (Keep As Is) ---
function process_text_field($field_name, $condition_name, $post_array, &$conditions, &$params)
{
    // Input validation and condition handling... (Your existing logic here)
    if (!empty($post_array[$condition_name])) {
        $condition = $post_array[$condition_name];
        $value = isset($post_array[$field_name]) ? trim($post_array[$field_name]) : '';

        if ($value === '' && !in_array($condition, ['is empty', 'is filled'])) {
            return; // Skip if value needed but not provided
        }

        $param_key = ":{$field_name}_" . bin2hex(random_bytes(3)); // Unique param key

        switch ($condition) {
            case 'contains':
                $conditions[] = "contacts.$field_name LIKE {$param_key}";
                $params[$param_key] = '%' . $value . '%';
                break;
            case 'does not contain':
                $conditions[] = "contacts.$field_name NOT LIKE {$param_key}";
                $params[$param_key] = '%' . $value . '%';
                break;
            case 'not equals':
            case 'does_not_equal':
                $conditions[] = "contacts.$field_name != {$param_key}";
                $params[$param_key] = $value;
                break;
            case 'ends with':
                $conditions[] = "contacts.$field_name LIKE {$param_key}";
                $params[$param_key] = '%' . $value;
                break;
            case 'starts with':
                $conditions[] = "contacts.$field_name LIKE {$param_key}";
                $params[$param_key] = $value . '%';
                break;
            case 'equals':
                $conditions[] = "contacts.$field_name = {$param_key}";
                $params[$param_key] = $value;
                break;
            case 'is empty':
                $conditions[] = "(contacts.$field_name IS NULL OR contacts.$field_name = '')";
                break;
            case 'is filled':
                $conditions[] = "(contacts.$field_name IS NOT NULL AND contacts.$field_name != '')";
                break;
        }
    }
}

function process_tags($tags_input_name, $condition_input_name, $post_array, &$conditions, &$params, &$joins, &$group_by, &$having)
{
    // Input validation and tag condition handling... (Your existing logic here)
    if (!empty($post_array[$condition_input_name])) {
        $condition = $post_array[$condition_input_name];
        $value = isset($post_array[$tags_input_name]) ? trim($post_array[$tags_input_name]) : '';

        if ($value === '') {
            return;
        }

        $tags = array_filter(array_map('trim', preg_split('/[,;]+/', $value))); // Split by comma or semicolon
        if (empty($tags)) {
            return;
        }

        $unique_tag_key = $tags_input_name . '_' . substr(md5(implode(',', $tags)), 0, 8);
        $placeholders = [];
        foreach ($tags as $index => $tag) {
            $placeholder = ":tag_{$unique_tag_key}_$index";
            $placeholders[] = $placeholder;
            $params[$placeholder] = $tag;
        }

        $join_alias_ct = "ct_" . $unique_tag_key;
        $join_alias_t = "t_" . $unique_tag_key;
        $joins[$join_alias_ct] = "LEFT JOIN contact_tags AS $join_alias_ct ON contacts.contact_id = $join_alias_ct.contact_id"; // Use LEFT JOIN for without_any/all initially
        $joins[$join_alias_t] = "LEFT JOIN tags AS $join_alias_t ON $join_alias_ct.tag_id = $join_alias_t.tag_id";

        switch ($condition) {
            case 'with_any':
                // Need INNER JOIN for 'with' conditions
                $joins[$join_alias_ct] = "INNER JOIN contact_tags AS $join_alias_ct ON contacts.contact_id = $join_alias_ct.contact_id";
                $joins[$join_alias_t] = "INNER JOIN tags AS $join_alias_t ON $join_alias_ct.tag_id = $join_alias_t.tag_id";
                $conditions[] = "$join_alias_t.name IN (" . implode(',', $placeholders) . ")";
                $group_by['contacts.contact_id'] = "contacts.contact_id"; // Group to ensure distinct contacts
                break;

            case 'with_all':
                $joins[$join_alias_ct] = "INNER JOIN contact_tags AS $join_alias_ct ON contacts.contact_id = $join_alias_ct.contact_id";
                $joins[$join_alias_t] = "INNER JOIN tags AS $join_alias_t ON $join_alias_ct.tag_id = $join_alias_t.tag_id";
                $conditions[] = "$join_alias_t.name IN (" . implode(',', $placeholders) . ")";
                $group_by['contacts.contact_id'] = "contacts.contact_id";
                $having[] = "COUNT(DISTINCT $join_alias_t.name) = " . count($tags);
                break;

            case 'without_any':
                // Using LEFT JOIN and checking for NULL tag name works if a contact has other tags
                // A more robust way is often NOT EXISTS or filtering out IDs later
                $group_by['contacts.contact_id'] = "contacts.contact_id";
                $sub_placeholders = [];
                foreach ($tags as $index => $tag) {
                    $sub_placeholder = ":sub_tag_{$unique_tag_key}_any_$index";
                    $sub_placeholders[] = $sub_placeholder;
                    $params[$sub_placeholder] = $tag;
                }
                // Select contacts where the count of matching forbidden tags is 0
                $having[] = "SUM(CASE WHEN $join_alias_t.name IN (" . implode(',', $sub_placeholders) . ") THEN 1 ELSE 0 END) = 0";
                break;


            case 'without_all':
                // Similar to without_any, but check the count of matching forbidden tags
                $group_by['contacts.contact_id'] = "contacts.contact_id";
                $sub_placeholders_all = [];
                foreach ($tags as $index => $tag) {
                    $sub_placeholder = ":sub_tag_{$unique_tag_key}_all_$index";
                    $sub_placeholders_all[] = $sub_placeholder;
                    $params[$sub_placeholder] = $tag;
                }
                // Select contacts where the count of matching forbidden tags is less than the total number of forbidden tags
                $having[] = "SUM(CASE WHEN $join_alias_t.name IN (" . implode(',', $sub_placeholders_all) . ") THEN 1 ELSE 0 END) < " . count($tags);
                break;

            default:
                // Unknown condition, remove joins if added unnecessarily
                unset($joins[$join_alias_ct]);
                unset($joins[$join_alias_t]);
                break;
        }
    }
}
// --- End PHP Functions ---


// --- Process POST Data ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process text fields based on your form input names
    process_text_field('first_name', 'first_name_condition', $_POST, $conditions, $params);
    process_text_field('last_name', 'last_name_condition', $_POST, $conditions, $params);
    process_text_field('company', 'company_condition', $_POST, $conditions, $params);
    process_text_field('email', 'email_condition', $_POST, $conditions, $params);
    process_text_field('address', 'address_condition', $_POST, $conditions, $params);
    process_text_field('city', 'city_condition', $_POST, $conditions, $params);
    process_text_field('state', 'state_condition', $_POST, $conditions, $params);
    process_text_field('zip_code', 'postalcode_condition', $_POST, $conditions, $params); // Assuming form uses 'postalcode_condition' for zip_code field
    process_text_field('phone', 'phone_condition', $_POST, $conditions, $params);
    // Add other text fields as needed...

    // Process tags fields
    process_tags('tags', 'tags_condition', $_POST, $conditions, $params, $joins, $group_by, $having);
    // process_tags('tags2', 'tags2_condition', $_POST, $conditions, $params, $joins, $group_by, $having); // If you have a second tag input
} else {
    // Handle GET request - maybe show all contacts or a default view?
    // For now, assume search is only triggered by POST, otherwise $conditions will be empty.
}
// --- End Process POST Data ---


// --- Build the SQL Queries ---
// Base selection - include all columns needed for display and modals
$select_data = "SELECT contacts.contact_id AS user_id, contacts.first_name, contacts.last_name, contacts.email, contacts.phone, contacts.address, contacts.city, contacts.state, contacts.zip_code, contacts.company, contacts.notes";

$from = " FROM contacts";
$join_clause = !empty($joins) ? ' ' . implode(' ', array_unique($joins)) : ''; // Use array_unique for joins
$where_clause = !empty($conditions) ? ' WHERE ' . implode(' AND ', $conditions) : '';
$group_by_clause = !empty($group_by) ? ' GROUP BY ' . implode(', ', array_keys($group_by)) : ''; // Use keys for GROUP BY
$having_clause = !empty($having) ? ' HAVING ' . implode(' AND ', $having) : '';
$order_by_clause = " ORDER BY contacts.first_name, contacts.last_name"; // Or your preferred default order

// --- Count Query Construction ---
$count_query_base = $from . $join_clause . $where_clause;
$count_query = "";
if (!empty($group_by)) {
    // Count distinct IDs from the grouped result set
    $count_query = "SELECT COUNT(*) FROM (SELECT DISTINCT contacts.contact_id " . $count_query_base . $group_by_clause . $having_clause . ") AS subquery_count";
} else {
    // Simple count if no grouping/having specific to tags logic
    $count_query = "SELECT COUNT(DISTINCT contacts.contact_id) " . $count_query_base; // No group/having needed here if simple WHERE
}

// --- Data Query Construction ---
$limit_clause = " LIMIT " . ($current_page - 1) * $records_per_page . ", " . $records_per_page;
$query = $select_data . $from . $join_clause . $where_clause . $group_by_clause . $having_clause . $order_by_clause . $limit_clause;

// --- Execute Queries ---
$total_records = 0;
$results = [];
$pagination_vars = null; // Initialize

try {
    // Get total count first
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $total_records = (int) $count_stmt->fetchColumn(); // Cast to int

    // Fetch data only if records exist
    if ($total_records > 0) {
        // Assuming getPaginationVars function is defined (e.g., in init.php or functions.php)
        if (function_exists('getPaginationVars')) {
            $pagination_vars = getPaginationVars($total_records, $records_per_page, $current_page);
        } else {
            // Basic fallback pagination calculation
            $total_pages = ceil($total_records / $records_per_page);
            $pagination_vars = [
                'total_records' => $total_records,
                'records_per_page' => $records_per_page,
                'current_page' => $current_page,
                'total_pages' => $total_pages
            ];
        }

        // Fetch the actual data for the current page
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $results = []; // Ensure results is an empty array if no records
    }
} catch (PDOException $e) {
    error_log("Database Error in process_search_contacts.php: " . $e->getMessage() . " | Query: " . $query . " | Count Query: " . $count_query . " | Params: " . print_r($params, true));
    echo '<div class="container mt-3"><div class="alert alert-danger">An error occurred while processing your search. Please check the logs or contact support.</div></div>';
    // Avoid further processing if DB error occurred
    $results = [];
    $total_records = 0;
}
// --- End Execute Queries ---

?>

<!-- --- HTML Display Section --- -->
<div class="container mt-5">

    <h1>Search Results</h1>

    <!-- Status Message Areas -->
    <div id="saveListStatusMessages" class="mb-3"></div>
    <div id="exportStatusMessages" class="mb-3">
        <div id="noContactsSelectedAlert" class="alert alert-warning alert-dismissible fade show" role="alert" style="display:none;">
            Please select at least one contact.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div id="exportSuccessAlert" class="alert alert-success alert-dismissible fade show" role="alert" style="display:none;">
            Contact export initiated successfully. Your download should begin shortly.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="button-group mt-4 mb-3">
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" id="listActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false" <?php echo empty($results) ? 'disabled' : ''; ?>>
                List Actions
            </button>
            <ul class="dropdown-menu" aria-labelledby="listActionsDropdown">
                <li><button class="dropdown-item" onclick="alert('Delete Selected - Not Implemented Yet')" type="button">Delete Selected</button></li>
                <li><button class="dropdown-item" onclick="alert('Create Campaign - Not Implemented Yet')" type="button">Create A Campaign</button></li>
                <li><button class="dropdown-item" onclick="saveSelectedContactsToList()" type="button">Save to list</button></li>
                <li><button class="dropdown-item" onclick="alert('Print - Not Implemented Yet')" type="button">Print</button></li>
                <li><button class="dropdown-item" onclick="exportSelectedContacts()" type="button">Export Contacts</button></li>
            </ul>
        </div>
    </div>

    <!-- Results Area -->
    <div id="contactsList">
        <?php if (!empty($results)): ?>
            <p class="text-muted"><?php echo htmlspecialchars($total_records); ?> results found.</p>

            <!-- Table Header -->
            <div class="d-flex flex-wrap align-items-center p-2 border-bottom bg-light fw-bold">
                <div class="col-1"><input type="checkbox" id="select_all" title="Select/Deselect All on Page"></div>
                <div class="col-3">Name</div>
                <div class="col-4">Email</div>
                <div class="col-3">Phone</div>
                <div class="col-1">State</div>
            </div>

            <!-- Table Body -->
            <?php foreach ($results as $row):
                // Sanitize output using htmlspecialchars
                $userId = htmlspecialchars($row['user_id'] ?? '');
                $firstName = htmlspecialchars($row['first_name'] ?? '');
                $lastName = htmlspecialchars($row['last_name'] ?? '');
                $email = htmlspecialchars($row['email'] ?? '');
                $phone = htmlspecialchars($row['phone'] ?? '');
                $state = htmlspecialchars($row['state'] ?? '');
            ?>
                <div class="contact-row d-flex flex-wrap align-items-center p-2 border-bottom" data-id="<?php echo $userId; ?>" style="cursor: pointer;">
                    <div class="col-1"><input type="checkbox" class="contact-checkbox" name="selected_contacts[]" value="<?php echo $userId; ?>"></div>
                    <div class="col-3"><?php echo trim($firstName . ' ' . $lastName); ?></div>
                    <div class="col-4"><?php echo $email; ?></div>
                    <div class="col-3"><?php echo $phone; ?></div>
                    <div class="col-1"><?php echo $state; ?></div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <?php if ($pagination_vars && $pagination_vars['total_pages'] > 1):
                // Build base URL for pagination links, preserving search criteria (if sent via GET)
                // If search criteria are only POST, pagination might reset the search unless criteria are stored in session or passed differently.
                // Assuming basic GET parameter handling for 'page'.
                $baseUrl = strtok($_SERVER["REQUEST_URI"], '?');
                $queryParams = $_GET; // Use GET params for pagination links
                unset($queryParams['page']);
                $queryString = http_build_query($queryParams);
                $baseUrl .= '?' . $queryString . (empty($queryString) ? '' : '&');
            ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Previous Button -->
                        <li class="page-item <?php echo ($pagination_vars['current_page'] <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo $baseUrl . 'page=' . ($pagination_vars['current_page'] - 1); ?>">Previous</a>
                        </li>

                        <!-- Page Numbers (Simplified view) -->
                        <?php
                        $num_links = 5; // Number of page links around current
                        $start = max(1, $pagination_vars['current_page'] - floor($num_links / 2));
                        $end = min($pagination_vars['total_pages'], $start + $num_links - 1);
                        $start = max(1, $end - $num_links + 1); // Adjust start if end hit limit

                        if ($start > 1) {
                            echo '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=1">1</a></li>';
                            if ($start > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }

                        for ($i = $start; $i <= $end; $i++) {
                            $active = ($pagination_vars['current_page'] == $i) ? 'active' : '';
                            echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . $baseUrl . 'page=' . $i . '">' . $i . '</a></li>';
                        }

                        if ($end < $pagination_vars['total_pages']) {
                            if ($end < $pagination_vars['total_pages'] - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=' . $pagination_vars['total_pages'] . '">' . $pagination_vars['total_pages'] . '</a></li>';
                        }
                        ?>

                        <!-- Next Button -->
                        <li class="page-item <?php echo ($pagination_vars['current_page'] >= $pagination_vars['total_pages']) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo $baseUrl . 'page=' . ($pagination_vars['current_page'] + 1); ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?> <!-- End Pagination -->

        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <p>No results found matching your criteria.</p>
        <?php else: ?>
            <p>Perform a search to see results.</p> <?php // Message if page loaded without POST search 
                                                    ?>
        <?php endif; ?> <!-- End Results Check -->
    </div> <!-- End #contactsList -->

    <!-- --- Modals --- -->

    <!-- Contact Details Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactName">Contact Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Email:</strong> <span id="contactEmail"></span></p>
                            <p><strong>Phone:</strong> <span id="contactPhone"></span></p>
                            <p><strong>Company:</strong> <span id="contactCompany"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Address:</strong> <span id="contactAddress"></span></p>
                            <p><span id="contactLocation"></span></p> <!-- City, State Zip -->
                        </div>
                    </div>
                    <hr>
                    <!-- Action Buttons within Modal -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="btn-group flex-wrap" role="group" aria-label="Contact Actions">
                                <button type="button" class="btn btn-outline-primary mb-1" id="tagsBtn">
                                    <i class="bi bi-tag-fill"></i> Tags (<span id="tagCount">0</span>)
                                </button>
                                <button type="button" class="btn btn-outline-primary mb-1" id="notesBtn">
                                    <i class="bi bi-journal-text"></i> Notes
                                </button>
                                <button type="button" class="btn btn-outline-primary mb-1" id="addTagBtn">
                                    <i class="bi bi-plus-lg"></i> Add Tag
                                </button>
                                <button type="button" class="btn btn-outline-danger mb-1" id="deleteTagsBtn">
                                    <i class="bi bi-dash-circle"></i> Manage Tags
                                </button>
                                <button type="button" class="btn btn-outline-secondary mb-1" id="editContactBtn">
                                    <i class="bi bi-pencil"></i> Edit Contact
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Dynamic Content Sections -->
                    <div id="tagsContent" class="mt-3" style="display: none;">
                        <h6>Tags:</h6>
                        <div id="currentTagsList">
                            <p class="text-muted small mb-0">Loading tags...</p>
                        </div>
                    </div>
                    <div id="notesContent" class="mt-3" style="display: none;">
                        <h6>Notes:</h6>
                        <pre id="notesText" style="white-space: pre-wrap; word-wrap: break-word; max-height: 200px; overflow-y: auto; border: 1px solid #eee; padding: 10px; background-color: #f9f9f9;"></pre>
                        <a href="#" id="editNotesLink">Edit Notes</a>
                    </div>
                    <div id="addTagContent" class="mt-3" style="display: none;">
                        <h6>Add New Tag:</h6>
                        <form id="addTagForm" class="mb-0">
                            <div class="input-group">
                                <input type="text" class="form-control" id="newTagInput" placeholder="Enter new tag name" required>
                                <button class="btn btn-success" type="submit">Add</button>
                            </div>
                            <div id="addTagMessage" class="form-text mt-1"></div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="modalContactId"> <!-- Store contact ID for actions -->
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Notes Modal -->
    <div class="modal fade" id="editNotesModal" tabindex="-1" aria-labelledby="editNotesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editNotesModalLabel">Edit Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea id="editNotesTextarea" class="form-control" rows="5" placeholder="Enter notes here..."></textarea>
                    <div id="editNotesMessage" class="form-text mt-1"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveNotesBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete/Manage Tags Modal -->
    <div class="modal fade" id="deleteTagsModal" tabindex="-1" aria-labelledby="deleteTagsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteTagsModalLabel">Manage Tags</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Select tags to remove:</p>
                    <ul id="tagsList" class="list-group mb-3" style="max-height: 250px; overflow-y: auto;">
                        <li class="list-group-item text-muted small">Loading tags...</li>
                    </ul>
                    <div id="deleteTagsMessage" class="form-text mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteTagsBtn">Remove Selected</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Contact Modal -->
    <div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editContactModalLabel">Edit Contact</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editContactForm">
                        <input type="hidden" id="editContactId" name="contact_id">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editFirstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="editFirstName" name="first_name">
                            </div>
                            <div class="col-md-6">
                                <label for="editLastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="editLastName" name="last_name">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="editPhone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="editPhone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="editAddress" class="form-label">Address</label>
                            <input type="text" class="form-control" id="editAddress" name="address">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editCity" class="form-label">City</label>
                                <input type="text" class="form-control" id="editCity" name="city">
                            </div>
                            <div class="col-md-4">
                                <label for="editState" class="form-label">State</label>
                                <input type="text" class="form-control" id="editState" name="state">
                            </div>
                            <div class="col-md-2">
                                <label for="editZipCode" class="form-label">Zip Code</label>
                                <input type="text" class="form-control" id="editZipCode" name="zip_code">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editCompany" class="form-label">Company</label>
                            <input type="text" class="form-control" id="editCompany" name="company">
                        </div>
                        <div id="editContactMessage" class="form-text mt-1"></div>

                        <!-- Moved buttons inside form for proper submission -->
                        <div class="modal-footer mt-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form> <!-- Form tag closes here -->
                </div>
            </div>
        </div>
    </div>
    <!-- --- End Modals --- -->

</div><!-- /.container -->

<?php
include 'footer.php'; // Includes closing body/html tags, potentially JS library links
include 'scripts.php'; // Your custom script includes or blocks (ensure jQuery, Bootstrap JS, SweetAlert are loaded *before* the inline script below)
?>

<!-- --- JavaScript Section --- -->
<script>
    // Ensure jQuery, Bootstrap JS, and SweetAlert are loaded before this script runs

    // --- Global Scope Variables & Functions (Accessible Everywhere) ---
    const selectAllCheckbox = document.getElementById('select_all');
    const saveListStatusMessages = document.getElementById('saveListStatusMessages'); // For save list messages
    const modalContactIdInput = document.getElementById('modalContactId'); // Hidden input in main modal

    /**
     * Basic HTML entity escaping for display purposes.
     * @param {string} str The string to escape.
     * @returns {string} The escaped string.
     */

    function htmlspecialchars(str) {
        if (typeof str !== 'string') return str;
        const map = {
            '&': '&',
            '<': '<',
            '>': '>',
            '"': '"',
            "'": ''
        };
        return str.replace(/[&<>"']/g, m => map[m]);
    }
    /**
     * Shows a Bootstrap alert temporarily.
     * @param {HTMLElement} alertElement The DOM element of the alert.
     * @param {number} [duration=5000] How long to show the alert in ms.
     * @param {boolean} [isAlreadyHtml=false] If true, assumes alertElement is complete HTML added dynamically.
     */
    function showTemporaryAlert(alertElement, duration = 5000, isAlreadyHtml = false) {
        if (!alertElement) return;

        // Make sure it's visible and ready for transition
        alertElement.style.display = 'block';
        alertElement.classList.add('fade', 'show');

        const alertInstance = bootstrap.Alert.getOrCreateInstance(alertElement);

        setTimeout(() => {
            if (alertElement && alertElement.offsetParent !== null) { // Check if still visible
                if (alertInstance) {
                    alertInstance.close(); // Use Bootstrap's close method
                } else {
                    alertElement.style.display = 'none'; // Fallback hide
                }
            }
            // If dynamically added, attempt removal after fade (Bootstrap handles this via 'closed.bs.alert' event)
            if (isAlreadyHtml && alertElement) {
                alertElement.addEventListener('closed.bs.alert', () => {
                    if (alertElement.parentNode) { // Check if still attached before removing
                        alertElement.remove();
                    }
                }, {
                    once: true
                });
            }
        }, duration);
    }

    /**
     * Handles the export of selected contacts.
     */
    function exportSelectedContacts() {
        const noContactsAlertElem = document.getElementById('noContactsSelectedAlert');
        const exportSuccessAlertElem = document.getElementById('exportSuccessAlert');

        // Hide status messages first
        if (noContactsAlertElem) noContactsAlertElem.style.display = 'none';
        if (exportSuccessAlertElem) exportSuccessAlertElem.style.display = 'none';
        if (saveListStatusMessages) saveListStatusMessages.innerHTML = ''; // Clear other messages

        const selectedContacts = Array.from(document.querySelectorAll('.contact-checkbox:checked')).map(cb => cb.value);

        if (selectedContacts.length === 0) {
            if (noContactsAlertElem) showTemporaryAlert(noContactsAlertElem, 5000, false);
            return;
        }

        // Use a temporary form for POST submission to trigger download
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'exportContacts.php'; // Your export handler script
        form.style.display = 'none'; // Hidden form

        selectedContacts.forEach(contactId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_contact_ids[]'; // Send as an array
            input.value = contactId;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form); // Clean up form

        if (exportSuccessAlertElem) showTemporaryAlert(exportSuccessAlertElem, 5000, false);

        // Deselect checkboxes after action
        if (selectAllCheckbox) selectAllCheckbox.checked = false;
        document.querySelectorAll('.contact-checkbox').forEach(checkbox => checkbox.checked = false);
    }


    /**
     * Handles saving selected contacts to a new list.
     */
    function saveSelectedContactsToList() {
        const noContactsAlertElem = document.getElementById('noContactsSelectedAlert');

        // Clear previous messages
        if (noContactsAlertElem) noContactsAlertElem.style.display = 'none';
        const exportSuccessAlertElem = document.getElementById('exportSuccessAlert');
        if (exportSuccessAlertElem) exportSuccessAlertElem.style.display = 'none';
        if (saveListStatusMessages) saveListStatusMessages.innerHTML = '';

        const selectedContactIds = Array.from(document.querySelectorAll('.contact-checkbox:checked')).map(cb => cb.value);

        if (selectedContactIds.length === 0) {
            if (noContactsAlertElem) showTemporaryAlert(noContactsAlertElem, 5000, false);
            return;
        }

        // Check for dependencies
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert library not loaded.');
            alert('Error: Required library SweetAlert is missing.');
            return;
        }
        if (typeof $ === 'undefined') {
            console.error('jQuery library not loaded.');
            alert('Error: Required library jQuery is missing.');
            return;
        }

        // Prompt for list name using SweetAlert
        Swal.fire({
            title: 'Save New List',
            input: 'text',
            inputLabel: 'List Name',
            inputPlaceholder: 'Enter a name for this list',
            showCancelButton: true,
            confirmButtonText: 'Save List',
            inputValidator: (value) => {
                if (!value || value.trim() === '') {
                    return 'You need to enter a list name!';
                }
                return null; // Is valid
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const listName = result.value.trim();
                const sanitizedListName = htmlspecialchars(listName); // Sanitize for display

                // Show loading/processing indicator (optional)
                // Swal.showLoading(); // If using SweetAlert for loading

                // AJAX call using jQuery
                $.ajax({
                    url: 'ajax_save_list_from_selection.php', // Your backend handler
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        list_name: listName, // Send original name
                        selected_contact_ids: selectedContactIds
                    },
                    success: function(response) {
                        // Swal.close(); // Close loading indicator if shown
                        if (response.success) {
                            const successAlertHtml = `
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    List "<strong>${sanitizedListName}</strong>" saved successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>`;
                            if (saveListStatusMessages) {
                                saveListStatusMessages.innerHTML = successAlertHtml;
                                const alertElement = saveListStatusMessages.querySelector('.alert');
                                if (alertElement) {
                                    showTemporaryAlert(alertElement, 5000, true); // Mark as HTML
                                }
                            } else {
                                alert(`List "${listName}" saved successfully!`); // Fallback
                            }

                            // Deselect checkboxes
                            if (selectAllCheckbox) selectAllCheckbox.checked = false;
                            document.querySelectorAll('.contact-checkbox').forEach(checkbox => checkbox.checked = false);

                        } else {
                            // Show error using SweetAlert
                            Swal.fire({
                                icon: 'error',
                                title: 'Error Saving List',
                                text: response.message || 'An unknown error occurred on the server.'
                            });
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        // Swal.close(); // Close loading indicator
                        console.error("Save List AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Failed to communicate with the server. Please check your connection or console logs.'
                        });
                    }
                });
            }
        });
    }

    // --- DOMContentLoaded Event Listener ---
    // Code here runs after the HTML document is fully loaded and parsed.
    document.addEventListener('DOMContentLoaded', () => {

        // --- Get References to Modal Elements ---
        const contactModalElem = document.getElementById('contactModal');
        const editNotesModalElem = document.getElementById('editNotesModal');
        const deleteTagsModalElem = document.getElementById('deleteTagsModal');
        const editContactModalElem = document.getElementById('editContactModal');
        // Ensure modals are initialized if needed (Bootstrap 5 auto-initializes via data-bs attributes usually)

        // --- Checkbox Handling ---
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                document.querySelectorAll('.contact-checkbox').forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }

        function addCheckboxListeners() {
            document.querySelectorAll('.contact-checkbox').forEach(checkbox => {
                checkbox.removeEventListener('change', handleCheckboxChange); // Prevent duplicates
                checkbox.addEventListener('change', handleCheckboxChange);
            });
        }

        function handleCheckboxChange() {
            if (selectAllCheckbox) {
                if (!this.checked) {
                    selectAllCheckbox.checked = false; // Uncheck "select all" if any item is unchecked
                } else {
                    // Check if all visible checkboxes are now checked
                    const allVisibleCheckboxes = document.querySelectorAll('.contact-checkbox');
                    const allChecked = allVisibleCheckboxes.length > 0 && Array.from(allVisibleCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                }
            }
        }
        addCheckboxListeners(); // Add listeners to initially loaded checkboxes

        // --- Row Click Listener (Opens Contact Details Modal) ---
        function addRowClickListeners() {
            document.querySelectorAll('.contact-row').forEach(row => {
                row.removeEventListener('click', handleRowClick); // Prevent duplicates
                row.addEventListener('click', handleRowClick);
            });
        }

        function handleRowClick(event) {
            // Ignore clicks on the checkbox column/checkbox itself
            if (event.target.matches('.contact-checkbox') || event.target.closest('.col-1')?.contains(event.target)) {
                return; // Allow checkbox interaction without opening modal
            }
            const contactId = this.getAttribute('data-id');
            if (contactId) {
                fetchContactDetails(contactId); // Fetch and display details
            } else {
                console.error("Contact ID attribute (data-id) missing from clicked row:", this);
            }
        }
        addRowClickListeners(); // Add listeners to initially loaded rows

        // --- Helper Function to Reset Contact Modal Before Loading ---
        function resetContactModal() {
            // Clear dynamic content areas
            const fieldsToClear = ['contactName', 'contactEmail', 'contactPhone', 'contactAddress', 'contactLocation', 'contactCompany', 'notesText', 'currentTagsList', 'tagCount'];
            fieldsToClear.forEach(id => {
                const elem = document.getElementById(id);
                if (elem) {
                    if (id === 'tagCount') elem.textContent = '0';
                    else if (id === 'notesText') elem.textContent = 'Loading...'; // Use pre tag for notes
                    else if (id === 'currentTagsList') elem.innerHTML = '<p class="text-muted small mb-0">Loading tags...</p>';
                    else elem.textContent = 'Loading...';
                }
            });
            if (modalContactIdInput) modalContactIdInput.value = ''; // Clear stored ID

            // Hide dynamic sections
            const sectionsToHide = ['tagsContent', 'notesContent', 'addTagContent'];
            sectionsToHide.forEach(id => {
                const elem = document.getElementById(id);
                if (elem) elem.style.display = 'none';
            });

            // Clear Add Tag form
            const newTagInput = document.getElementById('newTagInput');
            const addTagMessage = document.getElementById('addTagMessage');
            if (newTagInput) newTagInput.value = '';
            if (addTagMessage) addTagMessage.textContent = '';
        }

        // --- Fetch Contact Details (AJAX) ---
        async function fetchContactDetails(contactId) {
            if (!contactId) {
                console.error("fetchContactDetails: Invalid contactId.");
                return;
            }

            resetContactModal(); // Clear previous data and show loading state

            try {
                const response = await fetch(`ajax_get_contact_details.php?contact_id=${contactId}`);
                if (!response.ok) throw new Error(`Server error (${response.status}) fetching contact details.`);
                const data = await response.json();
                if (data.error) throw new Error(data.error);
                if (!data.contact) throw new Error("Contact data not found in response.");

                const contact = data.contact;

                // Populate modal fields
                document.getElementById('contactName').textContent = `${contact.first_name || ''} ${contact.last_name || ''}`.trim() || '(No Name)';
                document.getElementById('contactEmail').textContent = contact.email || 'N/A';
                document.getElementById('contactPhone').textContent = contact.phone || 'N/A';
                document.getElementById('contactAddress').textContent = contact.address || 'N/A';
                const locationParts = [contact.city, contact.state, contact.zip_code].filter(Boolean);
                document.getElementById('contactLocation').textContent = locationParts.join(', ') || 'N/A';
                document.getElementById('contactCompany').textContent = contact.company || 'N/A';
                document.getElementById('notesText').textContent = contact.notes || 'No notes available.';
                if (modalContactIdInput) modalContactIdInput.value = contactId;

                // Populate tags
                populateTags(data.tags || []);
                const tagCountElem = document.getElementById('tagCount');
                if (tagCountElem) tagCountElem.textContent = (data.tags || []).length;

                // Show the modal
                if (contactModalElem) {
                    var contactModalInstance = bootstrap.Modal.getOrCreateInstance(contactModalElem);
                    contactModalInstance.show();
                }

            } catch (error) {
                console.error("Error in fetchContactDetails:", error);
                alert(`Failed to load contact details: ${error.message}`);
                // Optionally hide modal or show error state within it
            }
        }

        // --- Populate Tags in Modals ---
        function populateTags(tags) {
            const currentTagsList = document.getElementById('currentTagsList'); // In main modal
            const deleteTagsList = document.getElementById('tagsList'); // In manage tags modal

            if (currentTagsList) currentTagsList.innerHTML = ''; // Clear
            if (deleteTagsList) deleteTagsList.innerHTML = ''; // Clear

            if (tags && Array.isArray(tags) && tags.length > 0) {
                tags.forEach(tag => {
                    if (tag.name != null && tag.tag_id != null) { // Need both name and ID
                        // Main modal display
                        if (currentTagsList) {
                            const tagElement = document.createElement('span');
                            tagElement.className = 'badge bg-info text-dark me-1 mb-1'; // Use a distinct color
                            tagElement.textContent = htmlspecialchars(tag.name);
                            currentTagsList.appendChild(tagElement);
                        }
                        // Manage tags modal list with checkboxes
                        if (deleteTagsList) {
                            const listItem = document.createElement('li');
                            listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                            const uniqueId = `delete-tag-${tag.tag_id}`;
                            listItem.innerHTML = `
                                  <label class="form-check-label flex-grow-1 me-2" for="${uniqueId}">
                                      ${htmlspecialchars(tag.name)}
                                  </label>
                                  <input type="checkbox" class="form-check-input delete-tag-checkbox" value="${tag.tag_id}" id="${uniqueId}">
                              `;
                            deleteTagsList.appendChild(listItem);
                        }
                    }
                });
            } else {
                // Display messages if no tags
                if (currentTagsList) currentTagsList.innerHTML = '<p class="text-muted small mb-0">No tags assigned.</p>';
                if (deleteTagsList) deleteTagsList.innerHTML = '<li class="list-group-item text-muted small">No tags available to manage.</li>';
            }
        }

        // --- Modal Section Toggling Logic ---
        function setupModalSectionToggle() {
            const buttons = {
                tagsBtn: document.getElementById('tagsBtn'),
                notesBtn: document.getElementById('notesBtn'),
                addTagBtn: document.getElementById('addTagBtn')
            };
            const sections = {
                tagsContent: document.getElementById('tagsContent'),
                notesContent: document.getElementById('notesContent'),
                addTagContent: document.getElementById('addTagContent')
            };

            Object.entries(buttons).forEach(([btnId, btnElem]) => {
                if (btnElem) {
                    btnElem.addEventListener('click', () => {
                        const targetSectionId = btnId.replace('Btn', 'Content');
                        Object.entries(sections).forEach(([secId, secElem]) => {
                            if (secElem) {
                                secElem.style.display = (secId === targetSectionId) ? 'block' : 'none';
                            }
                        });
                        // Special handling for Add Tag section
                        if (targetSectionId === 'addTagContent') {
                            const newTagInput = document.getElementById('newTagInput');
                            const addTagMessage = document.getElementById('addTagMessage');
                            if (newTagInput) newTagInput.focus();
                            if (addTagMessage) addTagMessage.textContent = ''; // Clear message
                        }
                    });
                }
            });
        }
        setupModalSectionToggle();

        // --- Helper to display messages within modal message areas ---
        function displayMessage(element, message, type = 'info') { // type: info, success, warning, danger
            if (element) {
                element.textContent = message;
                element.className = element.className.replace(/text-(info|success|warning|danger)/g, '').trim(); // Clear previous type
                if (type) {
                    element.classList.add(`text-${type}`);
                }
            }
        }

        // --- Edit Notes Functionality ---
        const editNotesLink = document.getElementById('editNotesLink');
        const saveNotesBtn = document.getElementById('saveNotesBtn');
        const notesTextElem = document.getElementById('notesText');
        const editNotesTextarea = document.getElementById('editNotesTextarea');
        const editNotesMessage = document.getElementById('editNotesMessage');

        if (editNotesLink && editNotesModalElem) {
            editNotesLink.addEventListener('click', (e) => {
                e.preventDefault();
                const currentNotes = notesTextElem ? notesTextElem.textContent.trim() : '';
                if (editNotesTextarea) editNotesTextarea.value = (currentNotes === 'No notes available.') ? '' : currentNotes;
                if (editNotesMessage) displayMessage(editNotesMessage, '', 'info'); // Clear message
                bootstrap.Modal.getOrCreateInstance(editNotesModalElem).show();
            });
        }

        if (saveNotesBtn && editNotesTextarea && editNotesModalElem && notesTextElem) {
            saveNotesBtn.addEventListener('click', async () => {
                const contactId = modalContactIdInput ? modalContactIdInput.value : null;
                if (!contactId) {
                    displayMessage(editNotesMessage, 'Error: Contact ID missing.', 'danger');
                    return;
                }
                const newNotes = editNotesTextarea.value;
                displayMessage(editNotesMessage, 'Saving...', 'info');

                const formData = new FormData();
                formData.append('action', 'update_notes'); // Specify action for backend
                formData.append('contact_id', contactId);
                formData.append('notes', newNotes);

                try {
                    const response = await fetch('ajax_update_contact.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (!response.ok || !result.success) throw new Error(result.message || 'Failed to save notes.');

                    displayMessage(editNotesMessage, 'Notes saved successfully!', 'success');
                    notesTextElem.textContent = newNotes || 'No notes available.'; // Update main modal display
                    setTimeout(() => bootstrap.Modal.getInstance(editNotesModalElem)?.hide(), 1500);

                } catch (error) {
                    console.error("Save notes error:", error);
                    displayMessage(editNotesMessage, `Error: ${error.message}`, 'danger');
                }
            });
        }

        // --- Add Tag Functionality ---
        const addTagForm = document.getElementById('addTagForm');
        const newTagInput = document.getElementById('newTagInput');
        const addTagMessage = document.getElementById('addTagMessage');

        if (addTagForm && newTagInput && addTagMessage) {
            addTagForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const contactId = modalContactIdInput ? modalContactIdInput.value : null;
                if (!contactId) {
                    displayMessage(addTagMessage, 'Error: Contact ID missing.', 'danger');
                    return;
                }
                const newTagName = newTagInput.value.trim();
                if (!newTagName) {
                    displayMessage(addTagMessage, 'Please enter a tag name.', 'warning');
                    return;
                }
                displayMessage(addTagMessage, 'Adding tag...', 'info');

                try {
                    // Using ajax_manage_tags.php endpoint
                    const response = await fetch('ajax_manage_tags.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `action=add_tag&contact_id=${encodeURIComponent(contactId)}&tag_name=${encodeURIComponent(newTagName)}`
                    });
                    const result = await response.json();
                    if (!response.ok || !result.success) throw new Error(result.message || 'Failed to add tag.');

                    displayMessage(addTagMessage, 'Tag added successfully!', 'success');
                    newTagInput.value = ''; // Clear input
                    fetchContactDetails(contactId); // Refresh main modal to show new tag
                    // Optionally switch back to tags view automatically after delay
                    // setTimeout(() => document.getElementById('tagsBtn')?.click(), 1500);

                } catch (error) {
                    console.error("Add tag error:", error);
                    displayMessage(addTagMessage, `Error: ${error.message}`, 'danger');
                }
            });
        }

        // --- Delete/Manage Tags Functionality ---
        const deleteTagsBtn = document.getElementById('deleteTagsBtn'); // Button in main modal
        const confirmDeleteTagsBtn = document.getElementById('confirmDeleteTagsBtn'); // Button in delete modal
        const deleteTagsMessage = document.getElementById('deleteTagsMessage');

        if (deleteTagsBtn && deleteTagsModalElem) {
            deleteTagsBtn.addEventListener('click', () => {
                if (deleteTagsMessage) displayMessage(deleteTagsMessage, '', 'info'); // Clear message
                // Tags should be populated by fetchContactDetails before this point
                bootstrap.Modal.getOrCreateInstance(deleteTagsModalElem).show();
            });
        }

        if (confirmDeleteTagsBtn && deleteTagsModalElem && deleteTagsMessage) {
            confirmDeleteTagsBtn.addEventListener('click', async () => {
                const contactId = modalContactIdInput ? modalContactIdInput.value : null;
                if (!contactId) {
                    displayMessage(deleteTagsMessage, 'Error: Contact ID missing.', 'danger');
                    return;
                }

                const tagsToDelete = Array.from(
                    deleteTagsModalElem.querySelectorAll('.delete-tag-checkbox:checked')
                ).map(cb => cb.value);

                if (tagsToDelete.length === 0) {
                    displayMessage(deleteTagsMessage, 'Please select at least one tag to remove.', 'warning');
                    return;
                }
                displayMessage(deleteTagsMessage, 'Removing tags...', 'info');

                try {
                    // Using ajax_manage_tags.php endpoint
                    const response = await fetch('ajax_manage_tags.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'remove_tags',
                            contact_id: contactId,
                            tag_ids: tagsToDelete
                        })
                    });
                    const result = await response.json();
                    if (!response.ok || !result.success) throw new Error(result.message || 'Failed to remove tags.');

                    displayMessage(deleteTagsMessage, 'Tags removed successfully!', 'success');
                    fetchContactDetails(contactId); // Refresh main modal
                    setTimeout(() => bootstrap.Modal.getInstance(deleteTagsModalElem)?.hide(), 1500);

                } catch (error) {
                    console.error("Remove tags error:", error);
                    displayMessage(deleteTagsMessage, `Error: ${error.message}`, 'danger');
                }
            });
        }

        // --- Edit Contact Functionality ---
        const editContactBtn = document.getElementById('editContactBtn'); // Button in main modal
        const editContactForm = document.getElementById('editContactForm');
        const editContactMessage = document.getElementById('editContactMessage');

        if (editContactBtn && editContactModalElem && contactModalElem) {
            editContactBtn.addEventListener('click', async () => {
                const contactId = modalContactIdInput ? modalContactIdInput.value : null;
                if (!contactId) {
                    alert('Error: Contact ID missing. Please close and reopen details.');
                    return;
                }

                displayMessage(editContactMessage, 'Loading contact data...', 'info');
                bootstrap.Modal.getOrCreateInstance(editContactModalElem).show(); // Show modal first
                const mainModalInstance = bootstrap.Modal.getInstance(contactModalElem); // Hide main modal
                if (mainModalInstance) mainModalInstance.hide();


                try {
                    // Fetch details specifically for editing
                    const response = await fetch(`ajax_get_contact_details.php?contact_id=${contactId}`);
                    const data = await response.json();
                    if (!response.ok || data.error || !data.contact) {
                        throw new Error(data.error || 'Failed to fetch contact data for editing');
                    }
                    const contact = data.contact;

                    // Populate the edit form
                    const formFields = {
                        'editContactId': contactId,
                        'editFirstName': contact.first_name,
                        'editLastName': contact.last_name,
                        'editEmail': contact.email,
                        'editPhone': contact.phone,
                        'editAddress': contact.address,
                        'editCity': contact.city,
                        'editState': contact.state,
                        'editZipCode': contact.zip_code,
                        'editCompany': contact.company
                    };
                    for (const fieldId in formFields) {
                        const input = document.getElementById(fieldId);
                        if (input) input.value = formFields[fieldId] || '';
                    }
                    displayMessage(editContactMessage, '', 'info'); // Clear loading message

                } catch (error) {
                    console.error("Edit contact - load error:", error);
                    displayMessage(editContactMessage, `Error loading data: ${error.message}`, 'danger');
                    // Optionally hide the edit modal if loading fails severely
                    // bootstrap.Modal.getInstance(editContactModalElem)?.hide();
                }
            });
        }

        // Handle Edit Contact Form Submission
        if (editContactForm && editContactMessage && editContactModalElem) {
            editContactForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);
                formData.append('action', 'update_contact'); // Specify action
                const contactId = formData.get('contact_id');

                if (!contactId) {
                    displayMessage(editContactMessage, 'Error: Cannot identify contact.', 'danger');
                    return;
                }
                displayMessage(editContactMessage, 'Saving changes...', 'info');

                try {
                    const response = await fetch('ajax_update_contact.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (!response.ok || !result.success) throw new Error(result.message || 'Failed to update contact.');

                    displayMessage(editContactMessage, 'Contact updated successfully!', 'success');
                    updateTableRow(contactId, Object.fromEntries(formData.entries())); // Update display in main list
                    setTimeout(() => bootstrap.Modal.getInstance(editContactModalElem)?.hide(), 1500);

                } catch (error) {
                    console.error("Update contact error:", error);
                    displayMessage(editContactMessage, `Error: ${error.message}`, 'danger');
                }
            });
        }

        // --- Helper to Update Table Row Display After Edit ---
        function updateTableRow(contactId, data) {
            const row = document.querySelector(`.contact-row[data-id="${contactId}"]`);
            if (row) {
                // Select columns based on their content/order - adjust if structure changes
                const nameCol = row.querySelector('.col-3:nth-of-type(2)'); // Assumes Name is 2nd col-3
                const emailCol = row.querySelector('.col-4');
                const phoneCol = row.querySelector('.col-3:nth-of-type(3)'); // Assumes Phone is 3rd col-3
                const stateCol = row.querySelector('.col-1:last-child');

                if (nameCol) nameCol.textContent = `${data.first_name || ''} ${data.last_name || ''}`.trim();
                if (emailCol) emailCol.textContent = data.email || '';
                if (phoneCol) phoneCol.textContent = data.phone || '';
                if (stateCol) stateCol.textContent = data.state || '';
            } else {
                console.warn(`Could not find table row for contact ID ${contactId} to update.`);
                // Consider reloading the page or fetching results again if row not found
            }
        }

        // --- Add listeners to dynamically loaded content (if using infinite scroll/load more) ---
        // If you implement dynamic loading, you'll need to call addCheckboxListeners() and addRowClickListeners()
        // after new content is added to the DOM. For pagination, this is usually not needed as the page reloads.


    }); // --- End of DOMContentLoaded listener ---
</script>