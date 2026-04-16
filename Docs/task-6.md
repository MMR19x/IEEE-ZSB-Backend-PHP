
# Web Security & Hacking Protection Guide

---

## section: 1 OOP Refactoring

---

## 1. Refactoring with OOP (Object-Oriented Programming)
Moving from procedural functions to a class-based structure provides a robust security architecture.

### A. The Database Base Class
* **Inheritance:** Create a `Database` class to handle `connect()` and `db_read()`. 
* **`extends` keyword:** Use other classes (like `Posts` or `Users`) to extend this base class. This centralizes credentials and ensures every table-specific class uses the same secure connection logic.

### B. Access Control (Public vs. Private)
* **`private`**: Lock down functions like `connect()` so they can't be triggered externally.
* **`public`**: Only expose the necessary methods to the rest of your application.
* **`this` Keyword**: Use `$this->function_name()` to securely call internal class methods.

### C. Query Abstraction
* **The Problem:** Writing raw SQL queries in your `home.php` or `index.php` exposes your database structure and makes updates difficult.
* **The Solution:** Create table-specific methods like `get_all_posts()` inside your classes. 
* **Security Benefit:** If a vulnerability is found in a specific query, you only have to fix it in one place (the class method) to secure the entire site.

---

## 2. Implementation Checklist
1. **Organize:** Create a class for every database table.
2. **Instantiate:** Use `$obj = new ClassName();` in your controllers.
3. **Execute:** Call specific methods (e.g., `$obj->get_data();`) instead of writing raw SQL.
4. **Whitelist:** Continue using file-inclusion whitelisting to prevent LFI (Local File Inclusion).

---

## section: 2  Login error 

---

## 1. Secure Login Best Practices
A secure login system doesn't just check passwords; it protects information.

### A. Error Message Obfuscation
* **The Goal:** Prevent "Username Enumeration."
* **The Rule:** If a login fails, show a generic message: *"Wrong email or password."*
* **Security Benefit:** Attackers cannot use your login form to see if a specific email address is registered on your site.

### B. Session and Global Variable Safety
* **Centralize Sessions:** Place `session_start()` at the top of your `index.php` to manage user states site-wide.
* **Avoid Globals in Functions:** Pass data (like `$_POST`) into your methods as arguments rather than calling the global variable directly. This makes the code easier to test and harder to exploit.

---

## 2. Refactoring with OOP (Continued)
* **`db_write()` method:** Added to the base `Database` class for SQL actions like INSERT, UPDATE, and DELETE.
* **`User` Class:** Move authentication logic into a dedicated class. This keeps your "public" login page clean and focuses only on displaying the result.

---

## 3. Technical Validation
* **Email Validation:** Use PHP's `filter_var($email, FILTER_VALIDATE_EMAIL)` to verify email formats securely without complex regex patterns.

---

## section : 3 Least Privilege

---

## 1. Core Principles
* **Principle of Least Privilege:** Give users only the access they absolutely need.
* **Information Leakage:** Use generic login error messages to prevent username enumeration.
* **Don't Trust User Data:** Always sanitize and validate inputs.

---

## 2. Role-Based Access Control (RBAC)
Organizing users into "ranks" or "levels" is critical for protecting administrative areas.

### A. Database and Session Setup
* **User Ranks:** Add a `rank` column to your `users` table (e.g., admin, editor, user).
* **Session Persistence:** Store the user's rank in `$_SESSION['user_rank']` upon successful login. This allows for fast, site-wide permission checks.

### B. The `access()` Function
Create a centralized function to check permissions. This prevents "spaghetti code" and ensures security logic is consistent.
* **Hierarchical Access:** Ensure higher ranks (like Admin) can access everything lower ranks (like Editor or User) can.
* **Whitelisting Roles:** Within the function, check if the current user's rank exists in a predefined "allowed" array for that specific action.

### C. Implementation in Templates
Wrap sensitive content in conditional blocks:
`if (access('editor')) { /* Display editor tools */ }`

---


## 3. Technical Checklist
1. **Session Safety:** Move `session_start()` to your main entry file.
2. **Access Checks:** Audit every sensitive page to ensure an `access()` check is in place.

---

## section: 4 SQL Injection

---

## 1. Core Principles
* **Multi-Layered Defense:** Don't rely on one method; use validation, escaping, and quoting together.
* **Don't Trust User Data:** Sanitize and validate every form input.

---

## 2. SQL Injection (POST Data)
SQL Injection is an attack where malicious code is inserted into input fields to manipulate database queries.

### A. How Attacks Work
Attackers use characters like `'`, `--`, and `#` to break out of a query and add their own conditions. 
* **Example Payload:** `admin@site.com' OR 1=1 #`
* **Result:** The database sees the `OR 1=1` as always true and logs the attacker in as the admin.

### B. Defending the Database
1. **Server-Side Validation:** Use `filter_var()` to ensure an email is actually an email. This stops injections before they reach the query.
2. **Escaping Characters:** Use `addslashes()` or `mysqli_real_escape_string()` to neutralize quotes. This converts `'` into `\\'`, which the database treats as text, not code.
3. **Mandatory Quoting:** Always wrap SQL variables in single quotes in your query (e.g., `WHERE id = '$id'`).
4. **Data Casting:** If expecting a number, use `(int)$variable`. This strips all non-numeric characters from the input.

---


## 3. Technical Checklist
* [ ] Use `filter_var()` for email inputs.
* [ ] Wrap all database variables in `'quotes'`.
* [ ] Apply `addslashes()` to all POST data.
* [ ] Cast numeric inputs to `(int)`.
* [ ] Use a generic "Wrong email or password" message.

---

## section : 5 Prepared Statements

---


## 1. Core Principles
* **Separation of Concerns:** Keep your SQL commands separate from your user data.
* **Don't Trust User Data:** Use Prepared Statements as the ultimate defense.
* **Multi-Layered Defense:** Continue using validation and sanitization alongside prepared statements.

---

## 2. Prepared Statements with PDO
Prepared statements are the most effective way to eliminate SQL Injection.

### A. How They Work
1. **Prepare:** You send a query template with placeholders (e.g., `SELECT * FROM users WHERE email = :email`).
2. **Execute:** You send the data separately. The database engine treats the data as a literal value, not as executable code.

### B. Why PDO?
* **Database Agnostic:** Works with MySQL, PostgreSQL, SQLite, etc.
* **Cleaner Code:** Simplifies the process of binding parameters compared to the procedural `mysqli` approach.

### C. Implementation Steps
* **Use Placeholders:** Replace direct variables in your SQL with named placeholders (e.g., `:id`).
* **The Execute Array:** Pass an associative array to the `execute()` method where keys match the placeholders.
* **Try-Catch Blocks:** Use these to catch database connection errors without leaking sensitive server info to the public.

---

## 3. Architecture & Refactoring Recap
* **OOP Inheritance:** The `Database` class handles the PDO connection, while other classes (like `User` or `Posts`) use its methods to run secure queries.
* **Efficiency:** Refactoring the `Database` class once protects the entire site immediately.

---

## 4. Technical Checklist
* [ ] Switch from `mysqli_connect` to `new PDO()`.
* [ ] Replace variables in SQL strings with `:placeholder` tags.
* [ ] Pass data into `execute([':placeholder' => $value])`.
* [ ] Use `try...catch` for database connections.
* [ ] Disable detailed error reporting on live production servers.

---

## section : 6 Cross-Site Scripting (XSS)

---


## 1. Core Principles
* **Don't Trust User Data:** Even if data is in your own database, treat it as malicious when displaying it.
* **Output Escaping:** Always clean data at the point of output to prevent script execution.
* **Multi-Layered Defense:** Use prepared statements for input and escaping for output.

---

## 2. Cross-Site Scripting (XSS)
XSS is an attack where malicious JavaScript is injected into a website to be executed in other users' browsers.

### A. How XSS Attacks Work
* **Persistent XSS:** Malicious code (like `<script>`) is saved in your database (e.g., in a comment or profile).
* **Consequences:** Attackers can steal session cookies using `document.cookie`, allowing them to hijack accounts.

### B. The Solution: `htmlspecialchars()`
The primary defense against XSS is to convert special HTML characters into their entity equivalents:
* `<` becomes `&lt;`
* `>` becomes `&gt;`
* This ensures the browser displays the code as text rather than executing it.

### C. Implementation Strategy: Centralized Sanitization
Create a helper function to ensure every piece of data displayed on your site is safe:
`function esc($data) { return htmlspecialchars($data); }`


---

## 3. Technical Checklist
* [ ] Wrap every `echo` of user data in `htmlspecialchars()`.
* [ ] Create a centralized `esc()` or `clean()` helper function.
* [ ] Use `filter_var()` for email validation.
* [ ] Use `try...catch` for database connections to hide system details on production.

---
