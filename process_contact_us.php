<?php
error_log("Contact form submission started");
require_once('c1.php');
require_once 'functions.php';
require 'vendor/autoload.php'; // Make sure to have SendGrid library installed

use SendGrid\Mail\Mail;

// Replace with your SendGrid API Key
$apiKey = SG_API_KEY;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Form data received: " . json_encode($_POST));
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $comments = $_POST['comments'];
    $captcha = $_POST['captcha'];

    // Validate captcha (this is a placeholder; implement captcha validation)
    if (strtoupper($captcha) !== CAPTCHA) {
        echo json_encode(['success' => false, 'message' => 'Invalid captcha']);
        error_log('Incorrect Captcha entered: ' . $captcha);
        exit;
    }

    // Prepare email content
    $message = new Mail();
    $message->setFrom(SITE_EMAIL, EMAIL_NAME);
    $message->setSubject("New Contact Form Submission");
    $message->addTo(ADMIN_EMAIL, "LovingCRM Contact Form");
    $message->addContent(
        "text/plain",
        "Name: $name\nEmail: $email\nPhone: $phone\nComments: $comments"
    );
    
    error_log("Attempting to send email via SendGrid");
    $sendgrid = new \SendGrid($apiKey);

    try {
        $response = $sendgrid->send($message);
        error_log("SendGrid response status: " . $response->statusCode());
        error_log("SendGrid response body: " . $response->body());
        
        if ($response->statusCode() == 202) {   
            echo json_encode(['success' => true]);
        } else {
            error_log("SendGrid error response: " . print_r($response, true));
            echo json_encode(['success' => false, 'message' => 'Failed to send email']);
        }
    } catch (Exception $e) {
        error_log("Error in process_contact_us.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}