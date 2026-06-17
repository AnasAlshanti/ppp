<?php
/** contact_process.php — validate + store a contact message, then redirect back. */
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('contact.php');
}
verify_csrf();

$name    = trim((string) ($_POST['name'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

// Server-side validation.
$errors = [];
if ($name === '')                                   { $errors[] = 'Name is required.'; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL))     { $errors[] = 'A valid email is required.'; }
if (!in_array($subject, ['Inquiry', 'Complaint', 'Suggestion'], true)) { $errors[] = 'Please choose a subject.'; }
if ($message === '')                                { $errors[] = 'Message cannot be empty.'; }

if ($errors) {
    $_SESSION['contact_errors'] = $errors;
    $_SESSION['contact_old']    = ['name' => $name, 'email' => $email, 'subject' => $subject, 'message' => $message];
    redirect('contact.php');
}

$stmt = $pdo->prepare(
    'INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)'
);
$stmt->execute([$name, $email, $subject, $message]);

set_flash('success', 'Thanks for reaching out! Your message has been sent.');
redirect('contact.php');
