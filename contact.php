<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // WhatsApp API settings
    $apiUrl = "https://api-jgdt.onrender.com/send-message"; // Replace with your actual API URL
    $apiKey = "04d131d0ece4d99379fe9ad95bf4927e"; // Replace with your generated API key
    $phoneNumber = "923483469617"; // Your WhatsApp number

    // Get form data
    $name = $_POST["name"] ?? "Unknown";
    $email = $_POST["email"] ?? "No email provided";
    $message = $_POST["message"] ?? "No message";

    // Format the WhatsApp message
    $whatsappMessage = "📩 *New Contact Form Submission* 📩\n\n"
                     . "👤 *Name:* $name\n"
                     . "📧 *Email:* $email\n"
                     . "📝 *Message:* $message\n\n"
                     . "📲 Sent via WhatsApp API.";

    // Prepare API request data
    $data = [
        "apiKey" => $apiKey,
        "phoneNumber" => $phoneNumber,
        "message" => $whatsappMessage
    ];

    // Send request
    $options = [
        "http" => [
            "header"  => "Content-Type: application/json",
            "method"  => "POST",
            "content" => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($apiUrl, false, $context);

    if ($response === FALSE) {
        echo "Error sending message.";
    } else {
        echo "Message sent successfully!";
    }
    exit; // Stop further execution
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Contact Us</h2>
    <div class="card p-4">
        <form id="contactForm">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Send Message</button>
        </form>
        <div id="responseMessage" class="mt-3"></div>
    </div>
</div>

<script>
$(document).ready(function () {
    $("#contactForm").submit(function (event) {
        event.preventDefault();
        let formData = $(this).serialize();

        $.ajax({
            type: "POST",
            url: "", // Same file
            data: formData,
            success: function (response) {
                $("#responseMessage").html('<div class="alert alert-success">' + response + '</div>');
            },
            error: function () {
                $("#responseMessage").html('<div class="alert alert-danger">Error sending message.</div>');
            }
        });
    });
});
</script>

</body>
</html>
