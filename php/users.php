<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$users = [];
$databaseError = null;
try {
    $statement = db()->prepare('SELECT id, name, email, phone, gender, course_or_department, profile_pic, created_at FROM users ORDER BY id DESC');
    $statement->execute();
    $users = $statement->fetchAll();
} catch (PDOException $exception) {
    error_log('DigitalPay users error: ' . $exception->getMessage());
    $databaseError = 'The user database is unavailable. Import database/digitalpay.sql and verify your environment settings.';
}
$token = csrf_token();
?>
<!doctype html>
<html lang="en" data-theme="light">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="description" content="DigitalPay PHP and MySQL user management"><title>User Management — DigitalPay</title><link rel="icon" href="../images/logo-mark.svg" type="image/svg+xml"><link rel="stylesheet" href="../css/style.css"><link rel="stylesheet" href="../css/responsive.css"><link rel="stylesheet" href="../css/animations.css"></head>
<body data-page="users">
  <a class="skip-link" href="#main-content">Skip to main content</a><div class="demo-banner"><strong>Demo / Academic Payment Environment</strong> — Manage fictional users only.</div>
  <header class="site-header"><div class="container header-inner"><a class="brand" href="../index.html"><img src="../images/logo-mark.svg" alt=""><span>Digital<span>Pay</span></span></a><nav class="site-nav" data-site-nav aria-label="Primary navigation"><ul class="nav-list"><li><a class="nav-link" href="../index.html">Home</a></li><li><a class="nav-link" href="../dashboard.html">Dashboard</a></li><li><a class="nav-link" href="../wallet.html">Wallet</a></li><li><a class="nav-link" href="../payments.html">Payments</a></li><li><a class="nav-link" href="../transactions.html">Transactions</a></li><li><a class="nav-link active" href="users.php" aria-current="page">User Management</a></li><li><a class="nav-link" href="../analyzer.php">Analyzer</a></li></ul></nav><div class="nav-actions"><a class="btn btn-small btn-primary" href="../register.html">＋ Add User</a><button class="icon-btn nav-toggle" type="button" data-nav-toggle aria-label="Open navigation" aria-expanded="false">☰</button></div></div></header>
  <main id="main-content">
    <section class="page-hero"><div class="container page-title-row"><div><span class="eyebrow">Experiment 7 · PHP + MySQL CRUD</span><h1 style="font-size:clamp(2.2rem,5vw,3.8rem)">User Management.</h1><p>Records are read from MySQL and all changes use validated prepared statements.</p></div><span class="status status-active"><?= count($users) ?> records</span></div></section>
    <section class="section-sm"><div class="container">
      <?php if (isset($_GET['updated'])): ?><div class="alert alert-success" style="margin-bottom:1rem">✓ User updated successfully.</div><?php endif; ?>
      <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success" style="margin-bottom:1rem">✓ User deleted successfully.</div><?php endif; ?>
      <?php if ($databaseError): ?><div class="alert alert-error"><?= e($databaseError) ?></div>
      <?php elseif (!$users): ?><div class="card empty-state"><div><div style="font-size:2rem">◎</div><h2>No users yet</h2><p>Register a fictional profile to populate the database.</p><a class="btn btn-primary" href="../register.html">Create demo user</a></div></div>
      <?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Gender</th><th>Course</th><th>Profile</th><th>Actions</th></tr></thead><tbody>
      <?php foreach ($users as $user): ?><tr><td><?= (int)$user['id'] ?></td><td><strong><?= e($user['name']) ?></strong><small class="muted" style="display:block">Joined <?= e(substr($user['created_at'], 0, 10)) ?></small></td><td><?= e($user['email']) ?></td><td><?= e($user['phone']) ?></td><td><?= e($user['gender']) ?></td><td><?= e($user['course_or_department']) ?></td><td><?php if ($user['profile_pic']): ?><img class="profile-preview" src="<?= e($user['profile_pic']) ?>" alt="Profile of <?= e($user['name']) ?>"><?php else: ?><span class="avatar"><?= e(strtoupper(substr($user['name'], 0, 2))) ?></span><?php endif; ?></td><td><div class="cluster" style="gap:.35rem"><a class="btn btn-small btn-secondary" href="update_user.php?id=<?= (int)$user['id'] ?>&amp;view=1">View</a><a class="btn btn-small btn-secondary" href="update_user.php?id=<?= (int)$user['id'] ?>">Edit</a><form method="post" action="delete_user.php" data-delete-user><input type="hidden" name="id" value="<?= (int)$user['id'] ?>"><input type="hidden" name="csrf_token" value="<?= e($token) ?>"><button class="btn btn-small btn-danger" type="submit">Delete</button></form></div></td></tr><?php endforeach; ?>
      </tbody></table></div><?php endif; ?>
      <div class="card" style="margin-top:1rem"><h3>CRUD coverage</h3><p>INSERT is handled by <code>register.php</code>, SELECT here, UPDATE in <code>update_user.php</code>, and DELETE in <code>delete_user.php</code>. Destructive requests use POST plus a session CSRF token.</p></div>
    </div></section>
  </main>
  <footer class="footer"><div class="container"><div class="footer-bottom" style="margin-top:0"><span>© <?= date('Y') ?> DigitalPay. Academic demonstration only.</span><span>Experiment 7 · PHP + MySQL</span></div></div></footer>
  <script src="../js/main.js"></script><script src="../js/admin.js"></script>
</body></html>
