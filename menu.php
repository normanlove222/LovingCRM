    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Loving CRM</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="add_contact.php">+</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Contacts
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="contacts.php">Contacts</a></li>
                            <li><a class="dropdown-item" href="add_contact.php">Add New Contact</a></li>
                            <li><a class="dropdown-item" href="search_contacts.php">Search Contacts</a></li>

                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Tags</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="tags.php">Tags</a></li>
                            <li><a class="dropdown-item" href="#">Create Saved Lists</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Emails</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="emails.php">List Emails</a></li>
                            <li><a class="dropdown-item" href="new_email.php">Build New email</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Lists</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="view_tag_lists.php">View Tag based Lists</a></li>
                            <li><a class="dropdown-item" href="view_contacts_lists.php">View Contacts Based Lists</a></li>
                            <li><a class="dropdown-item" href="">----------------------------</a></li>
                            <li><a class="dropdown-item" href="create_tag_based_list.php">Create Tag Based List</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Tools</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="export.php">Export Contacts</a></li>
                            <li><a class="dropdown-item" href="import">Import Contacts</a></li>
                            
                            <?php
                            // Assuming ENVIRONMENT is already defined somewhere in your application

                            if (defined('ENVIRONMENT') && ENVIRONMENT != 'demo') {
                                echo '<li><a class="dropdown-item" href="db_manage.php">Manage DB</a></li>';
                                echo '<li><a class="dropdown-item" href="./run_tests.php">Run Tests</a></li>';
                            }
                            ?>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>