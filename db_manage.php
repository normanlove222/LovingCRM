<?php
require_once('init.php');
?>

<!DOCTYPE html>
<html lang="en">

<?php
include "header.php";
include 'menu.php';
?>

<?php include "messaging.php"; ?>
<!-- Main layout -->
<div class="container-fluid">
    <h1 class="mb-4 text-center">Manage Contacts Database</h1>
    <p>This page is just for Develoment TESTING purposes. Neve click in a live Production enviornment, as you will loose data. You shouldnt even be seeing this if you have real data.</p>

    <section class="mb-3">

        <div class="">
            <p>clear certain tables, like contacts, tags and contact_tags.</p>
            <button id="clearRecords" class="btn btn-danger">Clear All Records</button>
        </div>
    </section>


    <section class="mb-3">
        <form id="importForm" enctype="multipart/form-data" class="mb-3">
            <div class="">
                <input type="file" name="sqlFile" id="sqlFile" accept=".sql" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Import SQL File</button>
        </form>
    </section>


    <section class="mb-3">
        <p>The button below is to backup DB to an .sql file, so testing can be done and easily restored. </p>
        <div class="">
            <button id="exportData" class="btn btn-success">Export All Data</button>
        </div>
    </section>

</div>


<?php
include 'footer.php';
include 'scripts.php';
?>
<script>
    $(document).ready(function() {
        $('#clearRecords').click(function() {
            $.ajax({
                url: 'manage.php',
                type: 'POST',
                data: {
                    action: 'clear'
                },
                success: function(response) {
                    alert(response);
                }
            });
        });

        $('#importForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('action', 'import');

            $.ajax({
                url: 'manage.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    alert(response);
                }
            });
        });

        $('#exportData').click(function() {
            window.location.href = 'export_all_data.php';
        });
    });
</script>