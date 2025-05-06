<?php
require_once('init.php');

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $zip_code = $_POST['zip_code'] ?? '';
    $company = $_POST['company'] ?? '';

    try {
        $stmt = $pdo->prepare("INSERT INTO contacts (first_name, last_name, email, phone, address, city, state, zip_code, company) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $email, $phone, $address, $city, $state, $zip_code, $company]);

        header('Location: index.php?add=success');
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
include 'header.php';
include 'menu.php'; 
?>
<style>
    body {
        background: linear-gradient(120deg, #f8fafc 0%, #e0e7ff 100%);
        min-height: 100vh;
    }
    .card {
        border-radius: 1.25rem;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.10);
        border: none;
    }
    .form-floating > .form-control:focus ~ label {
        color: #0d6efd;
    }
    .form-floating .form-control {
        padding-left: 1rem;
    }
</style>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card p-4">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4 fw-bold text-primary">Add New Contact</h2>
                    <form action="" method="post">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" required>
                            <label for="first_name">First Name</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" required>
                            <label for="last_name">Last Name</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                            <label for="email">Email</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone">
                            <label for="phone">Phone</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="address" name="address" placeholder="Address">
                            <label for="address">Address</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="city" name="city" placeholder="City">
                            <label for="city">City</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="state" name="state" placeholder="State">
                            <label for="state">State</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="zip_code" name="zip_code" placeholder="Zip Code">
                            <label for="zip_code">Zip Code</label>
                        </div>
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="company" name="company" placeholder="Company">
                            <label for="company">Company</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Add Contact</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include 'footer.php';
include 'scripts.php';
?>
<!-- Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</body>

</html>