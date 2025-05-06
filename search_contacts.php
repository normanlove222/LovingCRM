<?php
session_start();
require_once('c1.php');

require_once 'functions.php';
require_once('require_login.php');
include 'header.php';
include 'menu.php';
?>
<div class="container mt-5">
    <h1>Contacts</h1>

    <ul class="nav nav-tabs" id="searchTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts" type="button" role="tab" aria-controls="contacts" aria-selected="true">Advanced Search for Contacts/Tags</button>
        </li>
        <!-- <li class="nav-item" role="presentation">
            <button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address" type="button" role="tab" aria-controls="address" aria-selected="false">Address</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="phone-email-tab" data-bs-toggle="tab" data-bs-target="#phoneEmail" type="button" role="tab" aria-controls="phoneEmail" aria-selected="false">Phone/Email</button>
        </li> -->
    </ul>
    <div class="tab-content mt-3" id="tabContent">
        <!-- Contacts Tab -->
        <div class="tab-pane fade show active" id="contacts" role="tabpanel" aria-labelledby="contacts-tab">
            <form action="process_search_contacts.php" method="POST">
                <p><b>Search Criteria</b></p>
                <div class="mb-1 row">
                    <label for="firstName" class="col-sm-2 col-form-label">First Name</label>
                    <div class="col-sm-2">
                        <select class="form-select" id="firstNameFilter" name="first_name_condition">
                            <option value="contains">Contains</option>
                            <option value="does not contain">Doesn't Contain</option>
                            <option value="not equals">Not Equals</option>
                            <option value="ends with">Ends With</option>
                            <option value="starts with" selected>Starts With</option>
                            <option value="equals">equals</option>
                            <option value="is empty">Is Empty</option>
                            <option value="is filled">Is Filled</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="firstName" name="first_name">
                    </div>
                </div>

                <div class="mb-1 row">
                    <label for="lastName" class="col-sm-2 col-form-label">Last Name</label>
                    <div class="col-sm-2">
                        <select class="form-select" id="lastNameFilter" name="last_name_condition">
                            <option value="contains">Contains</option>
                            <option value="does not contain">Doesn't Contain</option>
                            <option value="not equals">Not Equals</option>
                            <option value="ends with">Ends With</option>
                            <option value="starts with" selected>Starts With</option>
                            <option value="equals">equals</option>
                            <option value="is empty">Is Empty</option>
                            <option value="is filled">Is Filled</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="lastName" name="last_name">
                    </div>
                </div>

                <div class="mb-1 row">
                    <label for="company" class="col-sm-2 col-form-label">Company</label>
                    <div class="col-sm-2">
                        <select class="form-select" id="companyFilter" name="company_condition">
                            <option value="contains">Contains</option>
                            <option value="does not contain">Doesn't Contain</option>
                            <option value="not equals">Not Equals</option>
                            <option value="ends with">Ends With</option>
                            <option value="starts with" selected>Starts With</option>
                            <option value="equals">equals</option>
                            <option value="is empty">Is Empty</option>
                            <option value="is filled">Is Filled</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="company" name="company">
                    </div>
                </div>

                <div class="mb-1 row">
                    <label for="email" class="col-sm-2 col-form-label">Email</label>
                    <div class="col-sm-2">
                        <select class="form-select" id="emailFilter" name="email_condition">
                            <option value="contains">Contains</option>
                            <option value="does not contain">Doesn't Contain</option>
                            <option value="not equals">Not Equals</option>
                            <option value="ends with">Ends With</option>
                            <option value="starts with" selected>Starts With</option>
                            <option value="equals">equals</option>
                            <option value="is empty">Is Empty</option>
                            <option value="is filled">Is Filled</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="email" name="email">
                    </div>
                </div>

                <div class="mb-1 row">
                    <label for="emailStatus" class="col-sm-2 col-form-label">Email Status</label>
                    <div class="col-sm-2">
                        <select class="form-select" id="emailStatusFilter" name="email_status_condition">
                            <option value="equals">Equals</option>
                            <option value="does_not_equal">Does not Equal</option>

                        </select>
                    </div>
                    <div class="col-sm-4">
                        <select class="form-select" id="emailStatus" name="email_status">
                            <option value="">Select Status</option>
                            <option value="Active">Active</option>
                            <option value="Pending">Pending</option>
                            <option value="Bounced">Bounced</option>
                            <option value="OptedOut">Opted Out</option>
                            <option value="Unsubscribed">Unsubscribed</option>
                            <option value="Invalid">Invalid</option>
                            <option value="DoNotContact">Do Not Contact</option>
                            <option value="Suppressed">Suppressed</option>
                            <option value="Blacklisted">Blacklisted</option>
                            <option value="Unknown">Unknown</option>
                        </select>
                    </div>
                </div>

                <div class="mb-1 row">
                    <label for="tags" class="col-sm-2 col-form-label">Tags</label>
                    <div class="col-sm-2">
                        <select class="form-select" id="tagsFilter" name="tags_condition">
                            <option value="with_any" selected>with ANY of these Tags</option>
                            <option value="with_all">with ALL of these Tags</option>
                            <option value="without_any">without ANY of these Tags</option>
                            <option value="without_all">without ALL of these Tags</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="tags" name="tags" placeholder="With ANY of these Tags">
                    </div>
                </div>

                <div class="mb-1 row">
                    <label for="tags2" class="col-sm-2 col-form-label">Tags 2</label>
                    <div class="col-sm-2">
                        <select class="form-select" id="tags2Filter" name="tags2_condition">
                            <option value="with_any">with ANY of these Tags</option>
                            <option value="with_all">with ALL of these Tags</option>
                            <option value="without_any" selected>without ANY of these Tags</option>
                            <option value="without_all">without ALL of these Tags</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="tags2" name="tags2" placeholder="Without ANY of these Tags">
                    </div>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <button type="reset" class="btn btn-secondary">Reset Filters</button>
                </div>
            </form>
        </div>

        <!-- Address Tab -->
        <!-- <div class="tab-pane fade" id="address" role="tabpanel" aria-labelledby="address-tab">
            <form action="process_search_contacts.php" method="POST">
                <p><b>Address Search Criteria</b></p>
                <div class="mb-1 row">
                    <label for="address" class="col-sm-2 col-form-label">Address</label>
                    <div class="col-sm-2">
                        <select class="form-select" id="addressFilter" name="address_condition">
                            <option value="contains" selected>Contains</option>
                            <option value="does not contain">Doesn't Contain</option>
                            <option value="not equals">Not Equals</option>
                            <option value="ends with">Ends With</option>
                            <option value="starts with">Starts With</option>
                            <option value="equals">equals</option>
                            <option value="is empty">Is Empty</option>
                            <option value="is filled">Is Filled</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="address" name="address">
                    </div>
                </div>

                <div class="mb-1 row">
                    <label for="city" class="col-sm-2 col-form-label">City</label>
                    <div class="col-sm-2">
                        <select class="form-select" id="cityFilter" name="city_condition">
                            <option value="contains">Contains</option>
                            <option value="does not contain">Doesn't Contain</option>
                            <option value="not equals">Not Equals</option>
                            <option value="ends with">Ends With</option>
                            <option value="starts with" selected>Starts With</option>
                            <option value="equals">equals</option>
                            <option value="is empty">Is Empty</option>
                            <option value="is filled">Is Filled</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="city" name="city">
                    </div>
                </div>

                <div class="mb-1 row">
                    <label for="state" class="col-sm-2 col-form-label">State</label>
                    <div class="col-sm-2">
                        <select class="form-select" id="stateFilter" name="state_condition">
                            <option value="contains">Contains</option>
                            <option value="does not contain">Doesn't Contain</option>
                            <option value="not equals">Not Equals</option>
                            <option value="ends with">Ends With</option>
                            <option value="starts with" selected>Starts With</option>
                            <option value="equals">equals</option>
                            <option value="is empty">Is Empty</option>
                            <option value="is filled">Is Filled</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="state" name="state">
                    </div>
                </div>

                <div class="mb-1 row">
                    <label for="postalcode" class="col-sm-2 col-form-label">Postal Code</label>
                    <div class="col-sm-2">
                        <select class="form-select" id="postalcodeFilter" name="postalcode_condition">
                            <option value="contains">Contains</option>
                            <option value="does not contain">Doesn't Contain</option>
                            <option value="not equals">Not Equals</option>
                            <option value="ends with">Ends With</option>
                            <option value="starts with" selected>Starts With</option>
                            <option value="equals">equals</option>
                            <option value="is empty">Is Empty</option>
                            <option value="is filled">Is Filled</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="postalcode" name="postalcode">
                    </div>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <button type="reset" class="btn btn-secondary">Reset Filters</button>
                </div>
            </form>
        </div> -->

        <!-- Phone/Email Tab -->
        <!-- <div class="tab-pane fade" id="phoneEmail" role="tabpanel" aria-labelledby="phone-email-tab">
            <form action="process_search_contacts.php" method="POST">
                <p><b>Phone/Email Search Criteria</b></p>
                <div class="mb-1 row">
                    <label for="phone" class="col-sm-2 col-form-label">Phone</label>
                    <div class="col-sm-2">
                        <select class="form-select" id="phoneFilter" name="phone_condition">
                            <option value="contains">Contains</option>
                            <option value="does not contain">Doesn't Contain</option>
                            <option value="not equals">Not Equals</option>
                            <option value="ends with">Ends With</option>
                            <option value="starts with" selected>Starts With</option>
                            <option value="equals">equals</option>
                            <option value="is empty">Is Empty</option>
                            <option value="is filled">Is Filled</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="phone" name="phone">
                    </div>
                </div>

                <div class="mb-1 row">
                    <label for="phone_email" class="col-sm-2 col-form-label">Email</label>
                    <div class="col-sm-2">
                        <select class="form-select" id="phoneEmailFilter" name="phone_email_condition">
                            <option value="contains">Contains</option>
                            <option value="does not contain">Doesn't Contain</option>
                            <option value="not equals">Not Equals</option>
                            <option value="ends with">Ends With</option>
                            <option value="starts with" selected>Starts With</option>
                            <option value="equals">equals</option>
                            <option value="is empty">Is Empty</option>
                            <option value="is filled">Is Filled</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="phone_email" name="phone_email">
                    </div>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <button type="reset" class="btn btn-secondary">Reset Filters</button>
                </div> -->
        </form>
    </div>
</div>
</div>

<?php
include 'footer.php';
include 'scripts.php';
?>

</body>

</html>