# DigitalPay

**Digital Payment Management System — Demo / Academic Payment Environment**

DigitalPay is one coherent full-stack laboratory project that grows from semantic HTML into a responsive fintech interface, event-driven JavaScript, browser storage, PHP/MySQL CRUD, a guarded website analyzer, and a jQuery/AJAX transaction center. It is a portfolio-quality simulation: it does not move money and must never receive real payment credentials.

> Replace `[Your Name]`, `[Your Register Number]`, and `[Your Department]` in the HTML files before academic submission.

## Features

- Premium, responsive dashboard with live counters, date/time, theme persistence, notifications and activity charts.
- Advanced accessible registration with native HTML5 constraints, content editing and safe profile-image upload.
- Drag-and-drop payment queue with keyboard support, `localStorage` and `sessionStorage`.
- Event-driven simulated payments, beneficiary search, category filters, favorites and delegated controls.
- jQuery transaction selection, group highlighting, compound filters, dynamic changes and AJAX JSON loading/retry.
- PHP/MySQL user CRUD with PDO prepared statements, CSRF protection, server validation and duplicate checks.
- SSRF-aware PHP website analyzer with time/size limits, redirect checks and graceful errors.
- SQL schema with keys, constraints, relationships, indexes and clearly fictional sample data.
- Accessible labels, focus states, keyboard controls, reduced-motion support and semantic landmarks.
- Print-friendly reports and documented static/full-stack deployment paths.

## Technology

HTML5, CSS3, JavaScript, CSS Grid, Flexbox, Web Storage, Drag and Drop, jQuery 3.7.1, AJAX, JSON, PHP 8.1+, PDO, MySQL 8, Git and GitHub.

## Project structure

```text
DigitalPay/
├── index.html                   # Experiments 1, 2 and 5 landing/dashboard site
├── dashboard.html               # Interactive dashboard
├── register.html                # Advanced HTML5 registration
├── wallet.html                  # Wallet, drag/drop and Web Storage
├── payments.html                # Event-driven beneficiaries and payment form
├── transactions.html            # jQuery transaction center
├── reports.html                 # Responsive payment reports
├── analyzer.php                 # Integrated analyzer UI
├── css/
│   ├── style.css                # Design system and components
│   ├── responsive.css           # Desktop/tablet/mobile breakpoints
│   └── animations.css           # Meaningful animation and reduced motion
├── js/
│   ├── main.js                  # Navigation, theme, counters, validation, slider
│   ├── dashboard.js             # Dashboard actions
│   ├── storage.js               # Drag/drop, localStorage, sessionStorage
│   ├── events.js                # Event-driven payments and beneficiaries
│   ├── transactions.js          # Shared transaction formatting
│   ├── jquery-app.js            # jQuery selectors, filtering and AJAX
│   └── admin.js                 # Safe delete confirmation
├── data/transactions.json       # Eight fictional AJAX records
├── php/
│   ├── config.php               # Environment-based PDO connection/helpers
│   ├── register.php             # Validated INSERT and safe image upload
│   ├── users.php                # SELECT and admin table
│   ├── update_user.php          # View and prepared UPDATE
│   ├── delete_user.php          # CSRF-protected prepared DELETE
│   ├── transactions.php         # JSON API backed by MySQL
│   ├── analyzer.php             # Guarded fetch and DOM extraction engine
│   └── uploads/.gitkeep         # Upload directory; contents are ignored
├── database/digitalpay.sql      # Schema, constraints and demo records
├── images/                      # Original SVG brand/illustration assets
├── screenshots/README.md        # Required capture checklist
├── tests/                       # Dependency-free smoke test and preview server
├── .env.example                 # Safe configuration reference
├── .gitignore
├── package.json
└── vercel.json                  # Static frontend headers/settings
```

## Installation and database setup

### Requirements

- PHP 8.1 or newer with `pdo_mysql`, `curl`, `dom`, `mbstring` and `fileinfo` enabled.
- MySQL 8.0 or newer.
- Apache/Nginx, XAMPP, WAMP, MAMP, or PHP's development server.
- Node.js 18+ only for the optional smoke test/static preview.

### 1. Create the database

From MySQL Workbench, phpMyAdmin, or the command line, import:

```bash
mysql -u root -p < database/digitalpay.sql
```

For a least-privilege local account, run commands similar to these as a MySQL administrator and choose your own password:

```sql
CREATE USER 'digitalpay_user'@'localhost' IDENTIFIED BY 'choose-a-local-password';
GRANT SELECT, INSERT, UPDATE, DELETE ON digitalpay.* TO 'digitalpay_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Configure the environment

Use `.env.example` as a list of variable names. `config.php` reads server environment variables and does not load `.env` files itself. In PowerShell for the current terminal:

```powershell
$env:DIGITALPAY_DB_HOST = '127.0.0.1'
$env:DIGITALPAY_DB_PORT = '3306'
$env:DIGITALPAY_DB_NAME = 'digitalpay'
$env:DIGITALPAY_DB_USER = 'digitalpay_user'
$env:DIGITALPAY_DB_PASSWORD = 'your-local-password'
```

XAMPP's default local `root` account with an empty password also works with the development defaults, but it is not suitable for deployment.

### 3. Run the full application

From the project directory:

```bash
php -S 127.0.0.1:8000
```

Open `http://127.0.0.1:8000`. Do not open the HTML with `file://`; browsers block the AJAX JSON request in that mode.

For XAMPP/WAMP, place the project under the web root and open the corresponding `http://localhost/...` URL.

### 4. Verify

```bash
npm test
```

The dependency-free smoke test checks required files, JSON records, storage APIs, drag/drop, event delegation, AJAX, SQL relationships and prepared-statement usage. PHP syntax checks can be run where PHP is installed:

```bash
php -l php/config.php
php -l php/register.php
php -l php/users.php
php -l php/update_user.php
php -l php/delete_user.php
php -l php/transactions.php
php -l php/analyzer.php
php -l analyzer.php
```

## How to demonstrate the application

1. Open Home and show the semantic sections, statistics, lists, form and module table.
2. Toggle the theme, open notifications, wait for the slider, test validation and scroll to reveal the top button.
3. Open Profile, show native date/time/number/tel/file fields, constraints, checkboxes and `contenteditable`.
4. Drag three services into the Wallet queue; Save, reload, then Retrieve. Type a temporary note and explain session storage.
5. On Payments, hover services, search/filter beneficiaries, favorite one, double-click a name, add a beneficiary and submit a fictional payment.
6. Import the SQL, register a user, then View/Edit/Delete it in User Management.
7. Analyze a small public site such as `https://example.com`; then demonstrate invalid and unreachable URLs.
8. In Transactions, click a record, highlight a category, combine filters, add/remove an item, and load JSON with AJAX.
9. Resize at 768, 390 and 360 pixels and show the menu, cards, forms and transaction layout.

# Laboratory Experiment Mapping

| Experiment | DigitalPay Module | Exact concepts demonstrated |
| --- | --- | --- |
| 1 | Professional Dashboard | `index.html` uses semantic header/nav/main/section/footer, welcome/vision/mission, eight statistics, unordered features, ordered services, updates, registration, module table, contact and academic footer. |
| 2 | Interactive Dashboard | CSS variables, Grid/Flexbox, responsive cards, five-plus animations, dynamic counters, live date/time, persisted theme, notification panel, banner slider, validation and scroll-to-top. |
| 3 | Advanced Registration Form | `register.html` uses email/tel/date/time/number/file inputs, min/max/pattern/required, radio/checkbox/textarea, semantic elements, `contenteditable` and `spellcheck`. |
| 4 | Drag & Drop Payment Queue + Web Storage | `wallet.html` + `storage.js` implement `dragstart`, `dragover`, `drop`, keyboard alternatives, persistent `localStorage`, temporary `sessionStorage`, and Save/Retrieve/Clear. |
| 5 | Professional Responsive Website | The shared design system covers Home, About, Services, Wallet, Payments, Transactions, Offers, Security and Contact with variables, Grid, Flexbox, pseudo-elements, transforms, transitions, image hover, positioning, progress bars and CSS tooltips. |
| 6 | JavaScript Event-Driven Payment System | `events.js` uses `addEventListener` for click, mouseover/mouseout, input, change, submit, blur, keydown and dblclick; it uses `preventDefault` and delegation for dynamic beneficiaries. |
| 7 | PHP + MySQL User Management | `digitalpay.sql` plus the PHP endpoints implement related tables, server validation, duplicate checks, safe uploads and prepared INSERT/SELECT/UPDATE/DELETE operations. |
| 8 | PHP Website Analyzer | `analyzer.php` and `php/analyzer.php` fetch public HTML, parse all requested metadata/resources and report invalid URL, DNS, HTTP, timeout, content and size failures without breaking the app. |
| 9 | jQuery Transaction Center + AJAX JSON | Every generated record has a class, unique id and data attributes; jQuery performs individual/group selection, compound filtering, dynamic add/remove and `$.ajax` JSON loading with a working Retry control. |

## Testing checklist

### Frontend

- [ ] Submit empty registration and payment forms.
- [ ] Test malformed email, non-numeric/short phone and amount values of 0, negative and over ₹1,00,000.
- [ ] Test password shorter than eight characters and Reset.
- [ ] Verify 1920, 1440, 1366, 768, 390 and 360 pixel widths with no horizontal page overflow.
- [ ] Navigate all controls by keyboard and confirm visible focus.
- [ ] Enable reduced motion at operating-system level.

### JavaScript and HTML5 APIs

- [ ] Confirm counters, clock, theme persistence, notification panel, slider and scroll-to-top.
- [ ] Drag/drop with a pointer and add with Enter/Space.
- [ ] Save/Retrieve/Clear the queue; refresh to confirm both storage lifetimes.
- [ ] Exercise every documented event, including double-click editing and delegated buttons.
- [ ] Search and combine type/status/amount filters.

### PHP/MySQL

- [ ] Import from a clean MySQL database and load User Management.
- [ ] Register valid data and try a duplicate email.
- [ ] Reject invalid fields and an invalid/oversized upload.
- [ ] View, edit and delete a sample user; cancel one confirmation.
- [ ] Stop MySQL and confirm a friendly database error is shown.

### Analyzer and AJAX

- [ ] Analyzer: valid page, malformed URL, unresolved host, blocked private address, non-HTML response, timeout/large response.
- [ ] AJAX: load JSON over HTTP and confirm eight cards are created.
- [ ] Temporarily rename `data/transactions.json`, confirm the error state, restore it and use Retry.
- [ ] Temporarily make JSON invalid and confirm the rest of the page still works.

## Security and limitations

- DigitalPay is an academic simulation, not a payment processor. It has no real authentication, settlement, card vault, UPI integration or bank connection.
- Never collect or store real card numbers, CVV, UPI PIN, OTP, banking passwords or authentication secrets.
- PDO emulated prepares are disabled; database inputs are bound to prepared statements.
- Registration repeats validation server-side, hashes passwords and checks duplicate email addresses.
- Uploads are limited by MIME type and size, receive random names, and are excluded from Git.
- CRUD mutations use POST and user deletion/update use CSRF tokens.
- The analyzer permits only HTTP(S), resolves every target/redirect, blocks private/reserved addresses, limits time and body size, verifies TLS, and does not execute fetched scripts.
- For production, add authentication/authorization, rate limiting, audit logging, a Content Security Policy, malware scanning, storage outside the public web root, migrations and automated integration tests.

## Deployment

### Static frontend on Vercel

Vercel can host the HTML/CSS/JavaScript/images/JSON experience. It **does not provide the traditional PHP/MySQL runtime this project requires**. Deploy only the static asset set, exclude `.php`, `php/`, `database/` and local configuration, and serve the site over HTTP(S) so AJAX can load JSON. The included `vercel.json` adds basic response headers.

For a polished split deployment, replace the static links to `analyzer.php` and `php/users.php` with the HTTPS URLs of the PHP host, and configure that backend's allowed origins if an API is called cross-origin.

### PHP/MySQL backend

Use a PHP-capable platform such as a managed LAMP host, Railway-style container, Render/Docker, or a VPS:

1. Provision PHP 8.1+ with the required extensions and MySQL 8.
2. Import `database/digitalpay.sql`.
3. Set the five `DIGITALPAY_DB_*` environment variables.
4. Point the web root at this project and ensure `php/uploads` is writable but does not execute scripts.
5. Enforce HTTPS, add production security headers and disable detailed PHP errors.
6. Test registration, CRUD and analyzer outbound networking from the deployed host.

Do not put database passwords or API keys in GitHub or `config.php`.

## GitHub upload instructions

After reviewing the files and replacing academic identity placeholders:

```bash
git init
git add .
git commit -m "build DigitalPay full-stack laboratory platform"
git branch -M main
git remote add origin https://github.com/YOUR-USERNAME/digitalpay.git
git push -u origin main
```

Before pushing, run `git status`, inspect `git diff --staged`, and confirm `.env`, uploaded images and credentials are absent.

## Screenshots

Follow [screenshots/README.md](screenshots/README.md). The checklist covers desktop, mobile, validation, drag/drop, dynamic success, jQuery, CRUD and analyzer evidence. Add the captured PNG files to that folder and embed selected images here for the final portfolio submission.

## Viva guide

1. **HTML dashboard:** Explain semantic elements, lists, forms and tables as the accessible structure shared by later experiments.
2. **CSS/JavaScript:** Point to custom properties, responsive Grid/Flexbox, restrained keyframes, event listeners, theme storage and Intersection Observer counters/reveals.
3. **Advanced HTML5:** Explain how input types and constraint attributes improve UX, why server validation is still mandatory, and how `contenteditable` differs from a submitted field.
4. **Drag/drop and storage:** Describe `dataTransfer`, `dragover.preventDefault()`, drop parsing, local persistence versus tab-scoped session data, and the keyboard fallback.
5. **Advanced CSS:** Demonstrate pseudo-elements, `translate`, `rotate`, `scale`, `skew`, sticky/fixed/absolute positioning, transitions, progress animation and CSS-only tooltips.
6. **Events:** Name the eight event types, explain `preventDefault()`, and show why delegated listeners handle newly rendered buttons.
7. **PHP/MySQL:** Trace registration from `$_POST`, `trim`, `filter_var`, validation and password hashing through a prepared INSERT; describe PK/FK/UNIQUE/CHECK constraints and cascading deletes.
8. **Analyzer:** Explain cURL retrieval, DOM/XPath extraction, absolute URL resolution, timeout/error handling and SSRF protection.
9. **jQuery/AJAX:** Show class/id/data selectors, single/group selection, `.each()`, `.toggle()`, delegated `.on()`, `$.ajax()`, JSON rendering and retry behavior.

## Future enhancements

- Authenticated roles and an audit trail for administrators.
- Database migrations, PHPUnit integration tests and browser end-to-end tests.
- Accessible chart library with server-generated exports.
- Configurable mock API and offline-first service worker.
- Docker Compose for a reproducible PHP/MySQL development environment.
- Localization and richer assistive-technology testing.

# Requirement Coverage Matrix

| Requirement | Experiment | Implementation file | Implemented? | How to demonstrate |
| --- | --- | --- | --- | --- |
| Semantic dashboard, welcome, vision and mission | 1 | `index.html` | Yes | Open Home and inspect the landmarks/About section. |
| Eight statistics | 1–2 | `index.html`, `js/main.js` | Yes | Scroll below the hero and watch all eight counters. |
| Feature/service lists, updates, form, modules, contact/footer | 1 | `index.html` | Yes | Follow the Home sections from Services to Contact. |
| CSS variables, Grid, Flexbox, cards and responsive media queries | 2, 5 | `css/style.css`, `css/responsive.css` | Yes | Inspect styles and resize at documented widths. |
| Five or more meaningful animations | 2 | `css/animations.css` | Yes | Observe fade, slide, zoom, pulse, typing, progress/bar growth and hover bounce. |
| Date/time, theme, notifications, slider, validation, scroll top | 2 | `js/main.js`, `dashboard.html` | Yes | Exercise each dashboard control. |
| Advanced HTML5 registration constraints | 3 | `register.html` | Yes | Submit invalid values and inspect input types/attributes. |
| Semantic elements, `contenteditable`, `spellcheck` | 3 | `register.html` | Yes | Edit the profile note and type a misspelled word. |
| Dragstart, dragover and drop | 4 | `js/storage.js`, `wallet.html` | Yes | Drag a service to Payment Queue. |
| Local and session storage with Save/Retrieve/Clear | 4 | `js/storage.js` | Yes | Save/reload/retrieve, then compare the temporary note. |
| Nine-section professional site | 5 | `index.html` and module pages | Yes | Navigate Home, About, Services, Wallet, Payments, Transactions, Offers, Security and Contact. |
| Pseudo-elements, transforms, transitions, image hover | 5 | `css/style.css` | Yes | Inspect hero, badges, slider art and card/image hover. |
| Relative, absolute, fixed and sticky positioning | 5 | `css/style.css` | Yes | Show header, notifications, scroll button and decorative elements. |
| Progress bars and CSS-only tooltips | 5 | `wallet.html`, `dashboard.html`, `css/style.css` | Yes | View goal progress and hover icon controls. |
| Eight JavaScript event types | 6 | `js/events.js` | Yes | Follow the interaction sequence documented above. |
| `preventDefault` and event delegation | 6 | `js/events.js` | Yes | Submit forms and use dynamic beneficiary controls. |
| MySQL tables and relationships | 7 | `database/digitalpay.sql` | Yes | Inspect DDL and foreign keys; import it. |
| `$_POST`, trim, escaping, filter validation, prepared SQL | 7 | `php/config.php`, `php/register.php` | Yes | Trace a registration request in code. |
| Duplicate email, numeric phone, required validation | 7 | `php/register.php` | Yes | Resubmit a sample email and malformed phone. |
| Validated profile upload | 7 | `php/register.php` | Yes | Upload a valid small PNG, then an invalid/large file. |
| User INSERT/SELECT/UPDATE/DELETE | 7 | `php/register.php`, `php/users.php`, `php/update_user.php`, `php/delete_user.php` | Yes | Complete a CRUD cycle in User Management. |
| Website analyzer basics/structure | 8 | `php/analyzer.php`, `analyzer.php` | Yes | Analyze a public HTML page and review summary/headings. |
| Links, media, social, SEO, JS and CSS resources | 8 | `php/analyzer.php`, `analyzer.php` | Yes | Review the result cards and scrollable lists. |
| Invalid URL, network, timeout and empty-response handling | 8 | `php/analyzer.php` | Yes | Test malformed/unreachable URLs; main navigation remains usable. |
| Common class, unique ID and data attributes | 9 | `js/jquery-app.js` | Yes | Load Transactions and inspect generated cards. |
| Individual and category selection | 9 | `js/jquery-app.js` | Yes | Select a card, then a category pill. |
| Search plus type/status/amount filters | 9 | `transactions.html`, `js/jquery-app.js` | Yes | Combine filter controls and watch the count. |
| Three or more dynamic operations | 9 | `js/jquery-app.js` | Yes | Select/highlight, show/hide, add, remove and enable/disable. |
| AJAX external JSON and loading UI | 9 | `data/transactions.json`, `js/jquery-app.js` | Yes | Click Load Latest Transactions. |
| AJAX error and real Retry | 9 | `js/jquery-app.js` | Yes | Rename JSON temporarily, fail, restore, then Retry. |
| Complete no-reload payment form/receipt | 6, 9 | `payments.html`, `js/events.js` | Yes | Submit fictional valid details and inspect the generated receipt. |
| Desktop/tablet/mobile support | All | `css/responsive.css` | Yes | Verify 1920/1440/1366/768/390/360 widths. |
| Accessibility | All | HTML pages, shared CSS/JS | Yes | Use keyboard, labels, alt text, focus and reduced motion. |
| Safe fictional demo data and warning | All | All pages, JSON, SQL | Yes | Observe the persistent demo banner and fictional identifiers. |
| GitHub/deployment/testing documentation | All | `README.md`, `.gitignore`, `vercel.json`, `tests/` | Yes | Follow the setup, test, GitHub and split-deployment sections. |

No item above is marked implemented unless executable source exists in the referenced file.

## Developer / team information

- Developer: **[Your Name]**
- Register number: **[Your Register Number]**
- Department: **[Your Department]**
- Project: **DigitalPay — Digital Payment Management System**
- Purpose: **Full Stack Development Laboratory / Academic Portfolio**
