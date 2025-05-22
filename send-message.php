<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

// Basic sanitization
function clean_input($data) {
  return htmlspecialchars(stripslashes(trim($data)));
}

// Check honeypot (should be empty)
if (!empty($_POST['website'])) {
  exit("Spam detected.");
}

// Get and sanitize fields
$name = clean_input($_POST['name'] ?? '');
$email = clean_input($_POST['email'] ?? '');
$message = clean_input($_POST['message'] ?? '');

// Validate input
if (!$name || !$email || !$message || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  exit("Invalid input.");
}

// === Send to Site Owner === //
$mail = new PHPMailer(true);

try {
    // MailHog SMTP setup
    $mail->isSMTP();
    $mail->Host = 'localhost';
    $mail->Port = 1025;
    $mail->SMTPAuth = false;

    // Email to you
    $mail->setFrom($email, $name);
    $mail->addAddress('ekloss20@gmail.com');
    $mail->addAddress('selectgreensutah@gmail.com');

    $mail->Subject = 'New Message from Website Contact Form';
    $mail->Body = "Name: $name\nEmail: $email\n\nMessage:\n$message";

    $mail->send();
} catch (Exception $e) {
    exit("Mailer Error (admin): " . $mail->ErrorInfo);
}

// === Auto-Reply to User === //
$autoReply = new PHPMailer(true);

try {
    $autoReply->isSMTP();
    $autoReply->Host = 'localhost';
    $autoReply->Port = 1025;
    $autoReply->SMTPAuth = false;

    $autoReply->setFrom('no-reply@yourdomain.com', 'Red Rock Group Inc.');
    $autoReply->addAddress($email, $name);

    $autoReply->Subject = 'Thanks for contacting Red Rock Group!';
    $autoReply->Body = "Hi $name,\n\nThanks for reaching out to us! We've received your message and will get back to you shortly.\n\n– The Red Rock Group Team";

    $autoReply->send();
} catch (Exception $e) {
    error_log("Auto-reply failed: " . $autoReply->ErrorInfo);
    // But don’t exit — the main message already went through.
}

echo "Message sent successfully.";
?>
