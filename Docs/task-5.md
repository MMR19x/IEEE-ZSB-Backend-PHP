# Web Security & Hacking Protection Guide

## 1. Core Principles of Security
* **The Golden Rule:** Don't Trust User Data. Always validate and sanitize inputs.
* **The Reality of Security:** There is no such thing as 100% security. It is a game of risk mitigation.
* **User Experience:** Don't INCONVENIENCE users.
* **The Paradox:** The only truly secure system is the broken one.
---

## 2. Why Web Security is Critical?
1.  **Public Exposure:** Once online, a website is a public space. Without "guards" (security protocols), anyone can enter and take what they want.
2.  **Data Privacy:** Users entrust you with emails, passwords, and payment info. Preventing leaks is a legal and ethical obligation.
3.  **Proactive Defense:** Understanding threats and virus types is the only way to build effective defenses.
4.  **Ongoing Evolution:** Security is a process, not a destination. Hackers evolve, so your defenses must evolve alongside them.

---

## 3. Understanding Hackers
A hacker is simply anyone who uses a system in a way it was not intended to be used.

### Categories of Hackers:
1. **White Hat:** Ethical hackers who find bugs to help companies fix them.

2. **Black Hat:** Malicious actors seeking profit or damage.

* **Curious User:** Explores vulnerabilities out of boredom or a desire to solve puzzles.
* **Script Kiddie:** Uses pre-made tools/scripts without understanding how they work.
* **Thrill Seekers:** Motivated by the adrenaline of the "break-in."
* **Hacktivist:** Hacks to promote political or social agendas.
* **Trophy Hunters:** Seek high-profile targets for prestige.
* **Professionals:** Hired guns who hack for money, often using "botnets" or compromised systems.

---

## 4. Social Engineering
Social engineering manipulates **human psychology** rather than technical vulnerabilities.

### Common Tactics:
* **Physical Exposure:** Writing passwords on sticky notes or desks.
* **Trash:** Finding sensitive info (bills, memos) in physical trash.
* **Key Loggers:** Software/hardware that records every keystroke and sends them to a third party.
* **Public Info Mining:** Using social media info to guess "Security Questions." (Mitigation: Use **MFA/2FA**).
* **Phishing:** A deceptive cyberattack where attackers masquerade as trusted entities (via fake emails, websites, or texts) to steal sensitive information like login credentials and credit card numbers.

---

## 5. Technical Protection Strategies (PHP Focus)

### A. Folder Structure (Public vs. Private)
Keep your logic separated from your landing page.
* **Private Folder:** Store all functions, classes, and sensitive logic here.
* **Public Folder:** Only store your `index.php`, CSS, JS, and images here.
* **Why?** The server can access the private folder (e.g., `include '../private/file.php'`), but a browser cannot "go back" past the public root.

### B. Preventing Directory Listings
If a folder lacks an `index.php`, the server might list every file inside (Directory Browsing).
* **The Fix:** Place an empty `index.php` in every directory (especially `/images`, `/uploads`, `/css`).
* **The Alternative:** Use a `.htaccess` file with the command: `Options -Indexes`. This returns a **403 Forbidden** error to attackers.

### C. Extension Security
Never store sensitive data (like DB credentials) in `.txt` or `.json` files.
* **The Risk:** Servers serve these as plain text. Anyone with the URL can read them.
* **The Fix:** Store configuration data inside a `.php` file. The server will execute the code and display nothing to the browser, keeping the contents secret.
---

## 6. Securing File Includes (PHP Injection Defense)

### The Danger of `include()` and `require()`
If you use URL parameters like `index.php?page=home` to load files, hackers can exploit this:
* **Directory Traversal:** Using `../` to access system files like Apache logs.
* **Executing Non-PHP Files:** The `include` function will execute PHP code hidden inside `.txt` files or even **JPEG images**.
* **Malicious Uploads:** Hackers can hide PHP scripts inside an image's metadata.

### Defensive Actions:
| Technique | Description |
| :--- | :--- |
| **Whitelisting** | Use `glob()` or a hardcoded array to only allow specific, "safe" filenames. |
| **Extension Hardcoding** | Force the script to append `.php` to any user-requested file. |
| **Image Processing** | Always **resize or crop** uploaded images to strip hidden metadata/scripts. |
| **file_get_contents** | If you don't need to execute code, use this function to read files as plain text only. |

---

## 7. Single Page Loading (Single Point of Entry)
Instead of having multiple public files (like `contact.php`, `about.php`), funnel everything through a single `index.php`.

### Key Benefits:
* **Reduced Attack Surface:** You only have one "door" to guard rather than many.
* **Centralized Security:** You can apply security checks, database connections, and session management once in the main file, and it applies to the whole site.
* **Easier Debugging:** Errors are centralized, making them easier to trace and fix.

### Implementation:
1. **Organize:** Move all functional pages (home, signup, login) into a private `includes` folder.
2. **Silence is Golden:** Put an empty `index.php` in that `includes` folder to prevent directory browsing.
3. **Dynamic Loading:** Use a URL parameter (e.g., `?page=contact`) to determine which file to include.
4. **Whitelist Verification:** Check that the requested page exists in a "safe list" before including it.

---

## 8. Clean URLs and Security in PHP Applications

### 1. Why Use Clean URLs for Security
- **Hiding Technology Details**: Removing `.php` extensions and query parameters like `?page=` prevents exposing server-side technologies.
- **Unified Entry Point**: Reinforces the single-entry-point architecture (`index.php`), allowing global sanitization of all incoming requests.

### 2. Implementing the .htaccess Rewrite
- **Purpose**: `.htaccess` instructs Apache to redirect all requests to `index.php` while preserving the requested path.
- **Ignore Real Files**: Requests for existing assets (images, CSS, JS) are excluded from redirection.
- **Rewrite Rule**: Captures the requested path (e.g., `/login`) and sends it to `index.php` as a GET variable (e.g., `index.php?url=login`).

### Example `.htaccess` Rule
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [L,QSA]
```
### 3. Refactoring Code for Clean URLs
- **Updating Links**: Internal links updated to clean format (e.g., `<a href="home">` instead of `<a href="home.php">`).
- **Centralized Header**: header.php contains navigation and is included across all pages for consistency.
- **Index Logic Update**: index.php checks for the url variable instead of page. Uses a whitelist (via `glob()`) to ensure only authorized files from the private folder are loaded.

### 4. Final Folder Structure for Security
- **Public Folder**: Contains only `index.php`, `.htaccess`, and public assets (CSS/JS/Images).
- **Private Folder**: Contains `includes/` with sensitive PHP files.
- **Accessing Private Files**: `index.php` in the public folder includes files from the private folder (e.g., `include '../private/includes/home.php'`).

```
project-root/
│
├── public/
│   ├── index.php
│   ├── .htaccess
│   ├── css/
│   ├── js/
│   └── images/
│
└── private/
    └── includes/
        ├── home.php
        ├── login.php
        └── header.php
```
---

## 9. Refactoring & Centralized Logic
Refactoring is the process of cleaning your code without changing its function. For security, this means moving critical logic into a single, protected location.

### A. The "Single Point of Truth"
* **The Problem:** Repeating database credentials or SQL logic across multiple files (like `home.php`, `login.php`) makes your site hard to secure. One mistake in one file exposes the whole site.
* **The Solution:** Centralize all sensitive logic into a `functions.php` file located in your **private** folder.

### B. Helper Functions (The Security Funnel)
By creating specific functions for database tasks, you ensure that every interaction with your data follows the same security rules:
* **`connect()`**: A single function to handle DB credentials.
* **`db_read()`**: A function that accepts a query and returns data. 
* **`db_write()`**: A function dedicated to changing data (Insert/Update/Delete).

### C. Maintenance Advantage
If you discover an SQL injection vulnerability, you only need to fix the logic **once** in your central `db_read()` function to protect every page on your entire website.

---