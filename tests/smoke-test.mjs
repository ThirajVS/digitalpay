import { access, readFile } from 'node:fs/promises';
import path from 'node:path';

const root = path.resolve(import.meta.dirname, '..');
const requiredFiles = [
  'index.html', 'dashboard.html', 'register.html', 'wallet.html', 'payments.html',
  'transactions.html', 'reports.html', 'analyzer.php', 'css/style.css',
  'css/responsive.css', 'css/animations.css', 'js/main.js', 'js/storage.js',
  'js/events.js', 'js/jquery-app.js', 'data/transactions.json', 'php/config.php',
  'php/register.php', 'php/users.php', 'php/update_user.php', 'php/delete_user.php',
  'php/transactions.php', 'php/analyzer.php', 'database/digitalpay.sql', 'README.md'
];

const checks = [];
for (const file of requiredFiles) {
  await access(path.join(root, file));
  checks.push(`✓ ${file}`);
}

const json = JSON.parse(await readFile(path.join(root, 'data/transactions.json'), 'utf8'));
if (!Array.isArray(json) || json.length < 5) throw new Error('transactions.json must contain at least five records');
for (const [index, record] of json.entries()) {
  for (const key of ['id', 'type', 'recipient', 'amount', 'status', 'date']) {
    if (!(key in record)) throw new Error(`Transaction ${index} is missing ${key}`);
  }
}
checks.push(`✓ transactions.json contains ${json.length} complete records`);

const eventJs = await readFile(path.join(root, 'js/events.js'), 'utf8');
const storageJs = await readFile(path.join(root, 'js/storage.js'), 'utf8');
const jqueryJs = await readFile(path.join(root, 'js/jquery-app.js'), 'utf8');
const sql = await readFile(path.join(root, 'database/digitalpay.sql'), 'utf8');
const phpFiles = await Promise.all(['register.php', 'update_user.php', 'delete_user.php', 'transactions.php'].map((file) => readFile(path.join(root, 'php', file), 'utf8')));

const assertions = [
  [eventJs.includes('addEventListener'), 'event handlers use addEventListener'],
  [!eventJs.includes('onclick='), 'event code has no inline onclick handlers'],
  [storageJs.includes('localStorage') && storageJs.includes('sessionStorage'), 'both Web Storage APIs are implemented'],
  [storageJs.includes("addEventListener('drop'"), 'HTML5 drop handling is implemented'],
  [jqueryJs.includes('$.ajax'), 'jQuery AJAX is implemented'],
  [jqueryJs.includes("$list.on('click', '.transaction-item'"), 'jQuery event delegation is implemented'],
  [/CREATE TABLE IF NOT EXISTS users/i.test(sql) && /FOREIGN KEY/i.test(sql), 'SQL tables and relationships exist'],
  [phpFiles.every((source) => source.includes('prepare(')), 'all CRUD/API PHP operations use prepared statements']
];
for (const [condition, label] of assertions) {
  if (!condition) throw new Error(`Failed: ${label}`);
  checks.push(`✓ ${label}`);
}

console.log(`DigitalPay smoke tests passed (${checks.length} checks).`);
console.log(checks.join('\n'));
