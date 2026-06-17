<?php
/** admin/messages.php — view, mark-read and delete contact messages (admin only). */
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $msgId  = (int) ($_POST['message_id'] ?? 0);

    if ($action === 'mark_read') {
        $pdo->prepare('UPDATE contacts SET is_read = 1 WHERE message_id = ?')->execute([$msgId]);
        set_flash('success', 'Message marked as read.');
    } elseif ($action === 'mark_unread') {
        $pdo->prepare('UPDATE contacts SET is_read = 0 WHERE message_id = ?')->execute([$msgId]);
        set_flash('success', 'Message marked as unread.');
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM contacts WHERE message_id = ?')->execute([$msgId]);
        set_flash('success', 'Message deleted.');
    }
    redirect('messages.php');
}

$messages = $pdo->query(
    'SELECT message_id, name, email, subject, message, is_read, submitted_at
       FROM contacts ORDER BY is_read ASC, submitted_at DESC, message_id DESC'
)->fetchAll();

$BASE = '../';
$page_title = 'Admin · Messages';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/admin_tabs.php';
?>

<div class="section-head"><h2>Messages (<?= count($messages) ?>)</h2></div>

<?php if (!$messages): ?>
  <div class="empty">No messages yet.</div>
<?php else: ?>
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>From</th><th>Subject</th><th>Message</th><th>Date</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($messages as $m): ?>
          <tr>
            <td><?= e($m['name']) ?><br><span class="muted" style="font-size:.82rem;"><?= e($m['email']) ?></span></td>
            <td><?= e($m['subject']) ?></td>
            <td style="max-width:340px;"><?= nl2br(e($m['message'])) ?></td>
            <td><?= e($m['submitted_at']) ?></td>
            <td>
              <?php if ((int) $m['is_read'] === 1): ?>
                <span class="pill pill-read">Read</span>
              <?php else: ?>
                <span class="pill pill-unread">Unread</span>
              <?php endif; ?>
            </td>
            <td style="white-space:nowrap;">
              <form class="inline-form" method="post" action="messages.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="<?= (int) $m['is_read'] === 1 ? 'mark_unread' : 'mark_read' ?>">
                <input type="hidden" name="message_id" value="<?= (int) $m['message_id'] ?>">
                <button class="btn btn-ghost btn-sm" type="submit"><?= (int) $m['is_read'] === 1 ? 'Mark unread' : 'Mark read' ?></button>
              </form>
              <form class="inline-form" method="post" action="messages.php" data-confirm="Delete this message?">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="message_id" value="<?= (int) $m['message_id'] ?>">
                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
