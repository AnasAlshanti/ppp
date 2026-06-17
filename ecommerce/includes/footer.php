<?php
/** includes/footer.php — closes the page opened in header.php. */
$BASE = $BASE ?? '';
?>
</main>

<footer class="site-footer">
  <div class="container footer-grid">
    <div>
      <div class="brand"><span class="brand-mark">◆</span> <?= e(APP_NAME) ?></div>
      <p class="muted">Your everyday tech, delivered.</p>
    </div>
    <div>
      <h4>Shop</h4>
      <a href="<?= e($BASE) ?>products.php">All products</a>
      <a href="<?= e($BASE) ?>index.php">Featured</a>
    </div>
    <div>
      <h4>Company</h4>
      <a href="<?= e($BASE) ?>contact.php">Contact</a>
      <a href="<?= e($BASE) ?>register.php">Create account</a>
    </div>
    <div>
      <h4>Get in touch</h4>
      <p class="muted">support@shopsphere.test<br>+962 7 9000 0000<br>Amman, Jordan</p>
    </div>
  </div>
  <div class="container footer-bottom">
    <span>© <?= date('Y') ?> <?= e(APP_NAME) ?>. All rights reserved.</span>
  </div>
</footer>

<script src="<?= e($BASE) ?>js/main.js"></script>
</body>
</html>
