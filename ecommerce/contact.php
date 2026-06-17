<?php
/** contact.php — contact form plus static contact details. */
require_once __DIR__ . '/includes/functions.php';

// Re-populate the form after a validation error (stored by contact_process.php).
$old    = $_SESSION['contact_old'] ?? [];
$errors = $_SESSION['contact_errors'] ?? [];
unset($_SESSION['contact_old'], $_SESSION['contact_errors']);

$page_title = 'Contact us';
require __DIR__ . '/includes/header.php';
?>

<div class="section-head"><h2>Contact us</h2></div>

<div class="grid-2">
  <div class="card">
    <p class="muted">Have a question or feedback? Send us a message and we'll get back within one business day.</p>

    <?php if ($errors): ?>
      <div class="flash flash-error" style="margin:12px 0;">
        Please fix the following: <?= e(implode(' ', $errors)) ?>
      </div>
    <?php endif; ?>

    <form class="form-grid" action="contact_process.php" method="post">
      <?= csrf_field() ?>
      <div>
        <label for="name">Name</label>
        <input type="text" id="name" name="name" required maxlength="100" value="<?= e($old['name'] ?? '') ?>">
      </div>
      <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required maxlength="100" value="<?= e($old['email'] ?? '') ?>">
      </div>
      <div>
        <label for="subject">Subject</label>
        <select id="subject" name="subject" required>
          <?php
          $subjects = ['Inquiry', 'Complaint', 'Suggestion'];
          $chosen = $old['subject'] ?? '';
          foreach ($subjects as $s):
          ?>
            <option value="<?= e($s) ?>" <?= $chosen === $s ? 'selected' : '' ?>><?= e($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="message">Message</label>
        <textarea id="message" name="message" required maxlength="2000"><?= e($old['message'] ?? '') ?></textarea>
      </div>
      <button class="btn" type="submit">Send message</button>
    </form>
  </div>

  <aside class="card">
    <h3>Reach us directly</h3>
    <p><strong>Address</strong><br><span class="muted">22 King Hussein St, Amman, Jordan</span></p>
    <p><strong>Phone</strong><br><span class="muted">+962 7 9000 0000</span></p>
    <p><strong>Email</strong><br><span class="muted">support@shopsphere.test</span></p>
    <p><strong>Working hours</strong><br><span class="muted">Sun–Thu, 9:00 – 18:00</span></p>
  </aside>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
