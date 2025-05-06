<?php
session_start();
require_once('c1.php');
require_once 'functions.php'; // Assuming you have sanitization and redirect functions

include 'header.php';
include 'logout_menu.php';
?>
<div class="container mt-4">
    <h2 class="text-center">Our Contact Info</h2>
    <div class="row">
        <div class="col-md-6">
            <h5>Email Info</h5>
            <p><strong>Sales:</strong> love@lovingCRM.com</p>
            <p><strong>Customer Support:</strong> love@lovingCRM.com</p>
        </div>
        <div class="col-md-6">
            <h5>Our Address</h5>
            <p><strong>Main Office:</strong></p>
            <p>498 Walker Ave. Unit 1<br>Ashland, OR 97520</p>

        </div>
    </div>
    <form id="contactForm">
        <div class="mb-3">
            <label for="name" class="form-label">Name*</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email*</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone*</label>
            <input type="tel" class="form-control" id="phone" name="phone" required>
        </div>

        <div class="mb-3">
            <label for="comments" class="form-label">Comments</label>
            <textarea class="form-control" id="comments" name="comments" rows="4"></textarea>
        </div>
        <div class="mb-3">

            <div class="mb-2"><img src="images/captcha.jpg" alt="Captcha"></div>
            <label for="captcha" class="form-label">Security text: copy the 7 letters & numbers in the image above, to the field below:</label>
            <input type="text" class="form-control" id="captcha" name="captcha" required>
        </div>
        <div id="statusMessage" class="text-center mt-3"></div>
        <button type="reset" class="btn btn-secondary">Reset</button>
        <button type="submit" id="submitBtn" class="btn btn-primary">Submit</button>
        <div id="spinner" class="spinner-border text-primary mt-3" role="status" style="display: none;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </form>
    <div id="successMessage"></div>
</div>

<?php
include 'footer.php';
include 'scripts.php';

?>
<script>
    document.getElementById("contactForm").addEventListener("submit", function(event) {
        event.preventDefault();

        // Show spinner and disable submit button
        const submitBtn = document.getElementById("submitBtn");
        const spinner = document.getElementById("spinner");
        const statusMessage = document.getElementById("statusMessage");

        submitBtn.disabled = true;
        spinner.style.display = "inline-block";
        statusMessage.textContent = ""; // Clear any previous message

        // Prepare form data
        const formData = new FormData(this);

        // Send data to process_contact.php
        fetch("process_contact_us.php", {
                method: "POST",
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fade out the form
                    $("#contactForm").fadeOut(500, function() {
                        // After form fades out, display the success message in the separate div
                        $("#successMessage").html("<div class='alert alert-success'>Message sent successfully!</div>").fadeIn(500);
                    }); 
                } else {
                    statusMessage.textContent = "Failed to send message. Please try again.";
                    statusMessage.classList.add("text-danger");
                    statusMessage.classList.remove("text-success");
                }
            })
            .catch(error => {
                console.error("Error details:", error); // Add this line
                statusMessage.textContent = "An error occurred. Please try again1.";
                statusMessage.classList.add("text-danger");
                statusMessage.classList.remove("text-success");
            })
            .finally(() => {
                // Hide spinner and enable submit button
                spinner.style.display = "none";
                submitBtn.disabled = false;
            });
    });
</script>
</body>

</html>