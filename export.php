<?php
require_once('init.php');

include 'header.php';
include 'menu.php';
?>
<div class="container mt-4">

    <h1>Export All Contacts to .csv file</h1>
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .sidebar {
            min-width: 200px;
            max-width: 250px;
        }

        .content {
            flex-grow: 1;
        }

        .navbar-brand {
            font-weight: bold;
        }
    </style>
    </head>

    <body>

        <?php include "messaging.php"; ?>
        <!-- Main layout -->
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <!-- <nav class="col-md-2 d-none d-md-block bg-light sidebar py-3">
                    <div class="position-sticky">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span data-feather="home"></span>
                                    Contacts
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <span data-feather="file"></span>
                                    Tags
                                </a>
                            </li> -->
                <!-- Add more sidebar items as needed -->
                <!-- </ul>
                    </div>
                </nav> -->

                <!-- Main content -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">

                        <div class="btn-toolbar mb-2 mb-md-0">
                            <a href="#" class="btn btn-primary" onclick="exportContacts(); return false;">
                                Export Contacts
                            </a>
                        </div>
                    </div>

                    <!-- Content goes here -->
                    <div class="table-responsive">
                        <!-- You can include a table of contacts here -->
                    </div>
                </main>
            </div>
        </div>


        <?php
        include 'footer.php';
        include 'scripts.php';
        ?>

        <script src="https://unpkg.com/feather-icons"></script>
        <script>
            // Initialize Feather Icons
            feather.replace();

            function exportContacts() {
                fetch('process_export.php')
                    .then(response => {
                        if (response.ok) {
                            return response.blob();
                        }
                        throw new Error('Network response was not ok.');
                    })
                    .then(blob => {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = url;
                        a.download = 'export.csv';
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        alert('Export successful!');
                        window.location.href = 'export.php?export=success';
                    })
                    .catch(error => {
                        console.error('There was a problem with the fetch operation:', error);
                    });
            }
        </script>
    </body>

    </html>