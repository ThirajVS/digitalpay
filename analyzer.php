<?php
declare(strict_types=1);
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/analyzer.php';

$analysis = null;
$error = null;
$submittedUrl = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedUrl = trim((string)($_POST['website_url'] ?? ''));
    try {
        $analysis = analyze_website($submittedUrl);
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
        error_log('DigitalPay analyzer: ' . $exception->getMessage());
    }
}

function analyzer_list(array $items, string $empty = 'None found'): void
{
    if (!$items) {
        echo '<li>' . e($empty) . '</li>';
        return;
    }
    foreach (array_slice($items, 0, 100) as $item) {
        echo '<li>' . e((string)$item) . '</li>';
    }
}
?>
<!doctype html>
<html lang="en" data-theme="light">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="description" content="DigitalPay PHP website analyzer for public webpages"><title>Website Analyzer — DigitalPay</title><link rel="icon" href="images/logo-mark.svg" type="image/svg+xml"><link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/responsive.css"><link rel="stylesheet" href="css/animations.css"></head>
<body data-page="analyzer">
  <a class="skip-link" href="#main-content">Skip to main content</a><div class="demo-banner"><strong>DigitalPay Developer Tool</strong> — Only public HTTP(S) webpages can be analyzed.</div>
  <header class="site-header"><div class="container header-inner"><a class="brand" href="index.html"><img src="images/logo-mark.svg" alt=""><span>Digital<span>Pay</span></span></a><nav class="site-nav" data-site-nav aria-label="Primary navigation"><ul class="nav-list"><li><a class="nav-link" href="index.html">Home</a></li><li><a class="nav-link" href="dashboard.html">Dashboard</a></li><li><a class="nav-link" href="wallet.html">Wallet</a></li><li><a class="nav-link" href="payments.html">Payments</a></li><li><a class="nav-link" href="transactions.html">Transactions</a></li><li><a class="nav-link" href="reports.html">Reports</a></li><li><a class="nav-link active" href="analyzer.php" aria-current="page">Website Analyzer</a></li><li><a class="nav-link" href="php/users.php">User Management</a></li></ul></nav><div class="nav-actions"><button class="icon-btn tooltip" type="button" data-theme-toggle data-tooltip="Change theme">☾</button><button class="icon-btn nav-toggle" type="button" data-nav-toggle aria-label="Open navigation" aria-expanded="false">☰</button></div></div></header>
  <main id="main-content">
    <section class="page-hero"><div class="container split"><div><span class="eyebrow">Experiment 8 · PHP DOM analysis</span><h1 style="font-size:clamp(2.2rem,5vw,3.8rem)">DigitalPay Website Analyzer.</h1><p>Inspect metadata, structure, links, media, social tags and resources from a public webpage.</p></div><aside class="alert alert-info"><strong>Safe by default</strong><span>Private, local and reserved network addresses are rejected. Responses are limited to 2 MB and 12 seconds.</span></aside></div></section>
    <section class="section-sm"><div class="container"><form class="form-card" method="post" action="analyzer.php"><div class="field"><label class="required" for="website-url">Website URL</label><input class="input" id="website-url" name="website_url" type="url" value="<?= e($submittedUrl) ?>" placeholder="https://example.com" required></div><div class="form-actions"><button class="btn btn-primary" type="submit">Analyze Website</button></div></form>
    <?php if ($error): ?><div class="alert alert-error" style="max-width:920px;margin:1rem auto 0"><strong>Analysis failed</strong><span><?= e($error) ?> The rest of DigitalPay remains available.</span></div><?php endif; ?>
    <?php if ($analysis): ?><div class="analyzer-result" aria-live="polite">
      <div class="alert alert-success">✓ Analysis completed for <?= e($analysis['url']) ?></div>
      <section><div class="section-heading"><span class="eyebrow">Basic information</span><h2>Page summary</h2></div><div class="result-grid"><article class="card"><span class="stat-label">Page Title</span><div class="result-value"><?= e($analysis['title']) ?></div></article><article class="card"><span class="stat-label">Language / Charset</span><div class="result-value"><?= e($analysis['language']) ?> · <?= e($analysis['charset']) ?></div></article><article class="card"><span class="stat-label">Word Count</span><div class="result-value"><?= number_format((int)$analysis['word_count']) ?></div></article><article class="card"><span class="stat-label">Page Size</span><div class="result-value"><?= number_format($analysis['page_size'] / 1024, 1) ?> KB</div></article><article class="card"><span class="stat-label">Meta Description</span><div class="result-value" style="font-size:.9rem"><?= e($analysis['description']) ?></div></article><article class="card"><span class="stat-label">Meta Keywords</span><div class="result-value" style="font-size:.9rem"><?= e($analysis['keywords']) ?></div></article></div></section>
      <section class="card-grid"><article class="card"><h3>H1 headings (<?= count($analysis['h1']) ?>)</h3><ul class="code-list"><?php analyzer_list($analysis['h1']); ?></ul></article><article class="card"><h3>H2 headings (<?= count($analysis['h2']) ?>)</h3><ul class="code-list"><?php analyzer_list($analysis['h2']); ?></ul></article><article class="card"><h3>H3 headings (<?= count($analysis['h3']) ?>)</h3><ul class="code-list"><?php analyzer_list($analysis['h3']); ?></ul></article></section>
      <section class="card-grid"><article class="card"><h3>All hyperlinks (<?= count($analysis['links']) ?>)</h3><ul class="code-list"><?php analyzer_list($analysis['links']); ?></ul></article><article class="card"><h3>Internal links (<?= count($analysis['internal_links']) ?>)</h3><ul class="code-list"><?php analyzer_list($analysis['internal_links']); ?></ul></article><article class="card"><h3>External links (<?= count($analysis['external_links']) ?>)</h3><ul class="code-list"><?php analyzer_list($analysis['external_links']); ?></ul></article></section>
      <section class="split"><article class="card"><h3>Media</h3><div class="detail-list"><div class="detail-row"><span>Favicon</span><strong style="overflow-wrap:anywhere"><?= e($analysis['favicon']) ?></strong></div><div class="detail-row"><span>Images</span><strong><?= count($analysis['images']) ?></strong></div></div><ul class="code-list"><?php analyzer_list($analysis['images'], 'No images found'); ?></ul></article><article class="card"><h3>Social & SEO metadata</h3><div class="detail-list"><div class="detail-row"><span>og:title</span><strong><?= e($analysis['og_title']) ?></strong></div><div class="detail-row"><span>og:description</span><strong><?= e($analysis['og_description']) ?></strong></div><div class="detail-row"><span>og:image</span><strong style="overflow-wrap:anywhere"><?= e($analysis['og_image']) ?></strong></div><div class="detail-row"><span>Canonical</span><strong style="overflow-wrap:anywhere"><?= e($analysis['canonical']) ?></strong></div></div></article></section>
      <section class="split"><article class="card"><h3>JavaScript references (<?= count($analysis['scripts']) ?>)</h3><ul class="code-list"><?php analyzer_list($analysis['scripts']); ?></ul></article><article class="card"><h3>Stylesheet references (<?= count($analysis['stylesheets']) ?>)</h3><ul class="code-list"><?php analyzer_list($analysis['stylesheets']); ?></ul></article></section>
    </div><?php endif; ?>
    </div></section>
  </main>
  <footer class="footer"><div class="container"><div class="footer-bottom" style="margin-top:0"><span>© <?= date('Y') ?> DigitalPay. Academic demonstration only.</span><span>Experiment 8 · PHP Website Analyzer</span></div></div></footer>
  <button class="scroll-top" type="button" data-scroll-top aria-label="Back to top">↑</button><script src="js/main.js"></script>
</body></html>
