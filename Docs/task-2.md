# IEEE ZSB Backend Phase 2: PHP Project Documentation

---

#  Database Constraints & Relationships

> **Objective:** Understand how to maintain data integrity using database constraints, foreign keys, and referential actions.

### 1. Constraints vs. Indices
While often discussed together, constraints and indices serve distinct purposes:
* **Constraints** dictate the strict rules for your columns (e.g., `NOT NULL`, `UNIQUE`, `PRIMARY KEY`). They ensure invalid data is never saved to the database.
* **Indices** (Indexes) are data structures used by the database engine to speed up data retrieval. 

*(Note: When you apply a `PRIMARY KEY` or `UNIQUE` constraint, the database automatically creates an index for that column behind the scenes to make lookups faster, which is why the terms are often used interchangeably!)*

### 2. Foreign Keys
Foreign Keys are used to establish a link between the data in two tables. They enforce "referential integrity" by ensuring that a value in one table must match an existing value in another table's Primary Key. 
* **Example:** A `user_id` column in the `notes` table acting as a Foreign Key that strictly references the `id` column in the `users` table.

### 3. On Delete / On Update Actions
When setting up a Foreign Key, you must explicitly instruct the database on what to do with the linked data (e.g., the notes) if the parent record (e.g., the user) is deleted or updated.

* **CASCADE:** Automatically deletes all linked child records. *(If User 1 is deleted, all of User 1's notes are instantly wiped out).*
* **RESTRICT:** Prevents the parent record from being deleted if any linked child records exist. *(The database throws an error if you try to delete User 1 while they still have notes).*
* **SET NULL:** Deletes the parent record and changes the foreign key in the child records to `NULL`. *(User 1 is deleted, but their notes remain in the database without an owner).*
* **SET DEFAULT:** Deletes the parent record and sets the foreign key in the child records to a predefined default value.

---

# Fetching & Displaying Data (The MVC Flow)

> **Objective:** Solidify the Model-View-Controller (MVC) architecture by strictly dividing database interactions (Controller) from HTML rendering (View).

### 1. Bootstrapping Order Matters
In your main entry point (`index.php`), the order in which files are required is critical. Because the router loads your controllers, any global dependencies those controllers need must be initialized *first*.
* If your controllers need to interact with the database, the `$db` instance must be created **before** `require 'router.php'`. 
* If the router loads a controller before `$db` exists, PHP will throw a fatal "undefined variable" error.

### 2. Controller Duty (Fetching)
The controller handles the business logic. Its only job is to interact with the database, fetch the necessary data, store it in a variable, and pass it to the view. It should contain no HTML.

**`controllers/notes.php`**
```php
<?php
// 1. Execute the query and save results to a variable
$notes = $db->query('SELECT * FROM notes WHERE user_id = 1')->get();

// 2. Load the view (which inherits the $notes variable)
require "views/notes.view.php";
```
###  Single Item Pages & Query Strings

**1. Passing Data via the URL (Query Strings)**
To view a single record, you need to tell the server *which* record to fetch. You do this by appending a query string to the URL dynamically inside your view.
* **Example:** `<a href="/note?id=<?= $note['id'] ?>">View Note</a>`
* This creates unique links like `/note?id=1`, `/note?id=3`, etc.

**2. Retrieving URL Data (`$_GET`)**
Inside your controller, you grab that specific ID from the URL using the `$_GET` superglobal.
* **Example:** `$_GET['id']` will grab the `1` from `/note?id=1`.

**3. Security Reminder: Prepared Statements**
When taking an ID from the URL and using it in a SQL query, **never inline it** directly. Always use placeholders to prevent SQL Injection.
* **WRONG:** `query("SELECT * FROM notes WHERE id = " . $_GET['id'])`
* **RIGHT:** `query("SELECT * FROM notes WHERE id = :id", ['id' => $_GET['id']])`

**4. `fetch()` vs `fetchAll()`**
When interacting with your database through PDO, choose your fetch method based on what you expect to return:
* **`fetchAll()`:** Use this when expecting **multiple records** (e.g., a list of all notes). It returns an array of arrays.
* **`fetch()`:** Use this when querying for a **single, specific record** (e.g., getting one note by its ID). It returns a single associative array.

**5. One Controller, One View**
As a general architectural rule: every single unique URI that presents data to a user should have its own dedicated View file. 
* `/notes` -> `notes.php` (Controller) -> `notes.view.php` (View showing the list)
* `/note?id=1` -> `note.php` (Controller) -> `note.view.php` (View showing the details of one note)
### Authorization & Access Control

**1. The Vulnerability (Broken Access Control)**
Just because a user knows or guesses the URL for a record (e.g., `/note?id=6`) does not mean they should be allowed to view it. If your controller only fetches data based on the `id` in the query string, any user can view any note in your database. 

**2. Authentication vs. Authorization**
* **Authentication:** Verifying *who* the user is (e.g., logging in).
* **Authorization:** Verifying *what* the user is allowed to do or see (e.g., "Does this user own this note?").

**3. Database-Level Authorization (The Query Fix)**
One effective way to prevent unauthorized access is to enforce ownership directly inside your SQL query. You adjust the query to mandate that both the note's `id` AND the `user_id` match.
* **Secure Query:** `SELECT * FROM notes WHERE id = :id AND user_id = :user`
* With this setup, if a user tries to view a note they didn't create, the database will simply return no results (`false`).

###  Error Handling & HTTP Status Codes (404 vs 403)

**1. Handling Missing Data (`false`)**
When querying a database for a single record (e.g., using `fetch()`), PDO returns the data as an array if it exists. However, if the record doesn't exist, it returns the boolean `false`. If your code tries to interact with `false` as if it were an array, your app will throw an error. You must always check if the data exists before using it.

**2. Why NOT do Authorization in the SQL Query?**
If you put your ownership check directly into the SQL query (e.g., `WHERE id = :id AND user_id = :user`), the database will just return `false` for both scenarios:
* The note doesn't exist.
* The note belongs to someone else.
Because both return `false`, you lose the ability to give the user accurate feedback about *why* the page failed to load.

**3. Distinguishing 404 vs. 403**
To provide the correct response, query the database for the note using *only* the ID, and then use PHP logic to determine the appropriate HTTP status code:
* **404 Not Found:** The record physically does not exist in the database.
* **403 Forbidden:** The record exists, but the current user does not have permission/authorization to view it.

**4. The Proper Logic Flow**
By separating the fetch from the authorization check, you can trigger the correct error view:

```php
// Fetch the note by ID only
$note = $db->query('SELECT * FROM notes WHERE id = :id', ['id' => $_GET['id']])->fetch();

// Check 1: Does the note exist?
if (!$note) {
    abort(404); // Display 404.php view
}

// Check 2: Is the user authorized? (Assuming $currentUserId = 1 for now)
if ($note['user_id'] !== $currentUserId) {
    abort(403); // Display 403.php view
}
```
### Code Clarity: Eliminating "Magic Numbers"

**1. What is a Magic Number?**
A "magic number" is a hardcoded value in your code (like `1`, `404`, or `403`) that has a specific meaning, but lacks obvious context. While it might make sense to you while writing it, a year later (or to a new teammate), the significance of that number will be a mystery.

**2. Variables for Context**
Even if a value is only used once in a file, extracting it into a descriptively named variable is a great practice. It serves as inline documentation.
* **Confusing:** `if ($note['user_id'] !== 1)`
* **Clear:** ```php
    $currentUserId = 1;
    if ($note['user_id'] !== $currentUserId)
    ```

**3. Global Constants (The `Response` Class)**
For values used across your entire application—like HTTP status codes—assigning variables in every single file is repetitive. Instead, create a dedicated class to hold these values as **Constants**.

Create a new file called `Response.php`:
```php
<?php

class Response {
    const NOT_FOUND = 404;
    const FORBIDDEN = 403;
}
```

### Refactoring: Taking Ownership of the Database API

**1. The Problem with `PDOStatement`**
In the previous setup, the `query()` method returned a native `PDOStatement` object. 
* The problem: You don't own the `PDOStatement` class, which means you cannot add your own custom helper methods (like a `findOrFail()` method) to it.

**2. The Solution: Returning `$this`**
Instead of returning the raw `PDOStatement`, you can save that statement as an instance property inside your `Database` class. Then, you return the `Database` instance itself (`return $this;`).

**3. Implementation Steps**
By returning `$this`, you can continue chaining methods. You just need to build your own `fetch()` method that delegates the work to the saved PDO statement.

```php
class Database {
    public $connection;
    public $statement; // 1. Add a property to store the statement

    public function query($query, $params = []) {
        $this->statement = $this->connection->prepare($query);
        $this->statement->execute($params);

        return $this; // 2. Return the current Database instance
    }

    // 3. Create your OWN fetch method
    public function fetch() {
        return $this->statement->fetch();
    }
    
    // 4. Create your OWN fetchAll method
    public function fetchAll() {
        return $this->statement->fetchAll();
    }
}
```
###  Custom DB Methods: `find()` and `findOrFail()`

**1. The Repetitive Code Problem**
When building a web app, fetching a single record almost always requires checking if it exists before proceeding. If it doesn't, you throw a 404 error.
```php
$note = $db->query('SELECT * FROM notes WHERE id = :id', ['id' => $id])->fetch();

if (!$note) {
    abort();
}
```
. Renaming Methods for Readability
Because you now own the fetch methods on your custom Database class, you can rename them to be more descriptive. Instead of PDO's fetch(), you can rename your method to find().

3. The Solution: findOrFail()
To DRY (Don't Repeat Yourself) up your code, you can encapsulate the 404-check directly inside your Database class.

Add these methods to your Database class:

PHP
```php
    // A cleaner name for fetching a single record
    public function find() {
        return $this->statement->fetch();
    }

    // The magical helper method
    public function findOrFail() {
        // 1. Try to find the record
        $result = $this->find();

        // 2. If it is false (not found), abort automatically
        if (!$result) {
            abort(); // Triggers your global 404 abort function
        }

        // 3. Otherwise, return the actual data
        return $result;
    }
    
```    
4. The Clean Controller
With this new method, your controller logic is reduced from multiple lines to a single, beautifully readable chain:

PHP
```php
$note = $db->query('SELECT * FROM notes WHERE id = :id', [
    'id' => $_GET['id']
])->findOrFail();
```

### Refactoring: Authorization & Cleaner Data Fetching

**1. The `authorize()` Helper Function**
When checking if a user has permission to view or edit a resource, writing `if ($note['user_id'] !== $currentUserId) { abort(Response::FORBIDDEN); }` gets repetitive and disrupts the flow of your controller. 

To fix this, abstract that logic into a dedicated helper function inside your `functions.php` file:

```php
// functions.php
function authorize($condition, $status = Response::FORBIDDEN) {
    if (!$condition) {
        abort($status);
    }
}

```
```php
authorize($note['user_id'] === $currentUserId);

```
###  Route Naming Conventions (RESTful Patterns)

**1. Convention Over Configuration**
While PHP allows you to name your URIs whatever you want, following established community guidelines makes your code predictable, professional, and much easier for a team to collaborate on. 

**2. Resource-Based Naming**
Instead of using "verbs" or actions in your URLs (like `/viewNote`, `/deleteNote`, or `/createNote`), modern web development uses a "RESTful" approach. You name routes based on the **resource** you are interacting with (e.g., `notes`, `photos`, `users`).

**3. Standard URI Patterns**
Here is the standard pattern for a resource like "notes":
* **`/notes`** : Display a list of all notes (Index).
* **`/note`** (or `/notes/{id}` in advanced routers) : Display a single specific note (Show).
* **`/notes/create`** : Display the HTML form to create a new note (Create).

**4. Looking Ahead: HTTP Verbs**
Right now, the URLs alone dictate the action. But as your app advances, you will use the exact same URLs but change the **HTTP Request Type** (`GET`, `POST`, `PATCH`, `DELETE`) to trigger different actions. 
* *Example:* A `GET` request to `/notes` shows the list of notes, but a `POST` request to `/notes` saves a new note to the database.

###  HTTP Methods: GET vs. POST

Whenever a user interacts with your website, their browser sends an HTTP request to your server. The two most common types of requests are **GET** and **POST**.



#### 1. GET Request (Retrieving Data)
A GET request is used exclusively to **request data from a specified resource**. 
* **How it works:** Any data sent to the server is appended directly to the URL in a "query string" (e.g., `/note?id=5`).
* **Visibility:** Everyone can see the data in the URL bar.
* **History & Bookmarks:** GET requests remain in the browser history and can be bookmarked.
* **Caching:** GET requests can be cached by the browser.
* **When to use it:** Searching, filtering, or viewing a specific page/record. **Never** use GET requests when dealing with sensitive data (like passwords) or when modifying the database.

#### 2. POST Request (Submitting Data)
A POST request is used to **send data to a server to create or update a resource**.
* **How it works:** The data is packaged inside the "body" of the HTTP request, completely hidden from the URL.
* **Visibility:** The data does not appear in the URL bar (though it can be seen in browser developer tools).
* **History & Bookmarks:** POST requests are not saved in browser history and cannot be bookmarked.
* **Caching:** POST requests are never cached.
* **When to use it:** Submitting forms, creating a new user, saving a new note to the database, or sending sensitive information (like logging in).

---

### 📊 Quick Comparison Table

| Feature | GET | POST |
| :--- | :--- | :--- |
| **Primary Purpose** | Read / Fetch data | Create / Submit data |
| **Data Location** | Appended to the URL | Hidden in the Request Body |
| **Security** | Low (visible in URL) | Higher (hidden from URL) |
| **Data Length Limit**| Yes (URL length restrictions) | No limit |
| **PHP Superglobal** | `$_GET` | `$_POST` |

# Milestone 5: Inserting Data into the Database

> **Objective:** Handle form submissions and securely insert new records into the database using SQL `INSERT` statements and PDO prepared statements.

### 1. The `INSERT` Statement Syntax
To add a new row to a database table, use the `INSERT INTO` command followed by the table name, the specific columns you want to fill, and the corresponding `VALUES`.

```sql
INSERT INTO notes (body, user_id) VALUES ('My new note', 1);
```
### 2. Securely Inserting Data via PHP
To prevent SQL injection, we must never concatenate $_POST data directly into the SQL string. Instead, we use the prepared statements architecture built into our Database class.

We place placeholders (like :body and :user_id) in the query, and pass the actual user input as a separate array.

# Milestone 6: Cross-Site Scripting (XSS) & Output Escaping

> **Objective:** Understand the risks of Cross-Site Scripting (XSS) and learn how to safely render user-generated content in the browser using PHP's built-in escaping functions.

### 1. The Illusion of Safety
In the previous milestone, we used **Prepared Statements** to secure our application. However, prepared statements *only* protect the database from SQL Injection. They do not protect the browser. 

If a user submits malicious code, the database will safely store it exactly as written. The vulnerability occurs later, when we pull that data out and display it on the page.

### 2. Demonstrating the Vulnerability
Imagine a user submits the following text into the "Create Note" form:

```html
<strong class="text-red-500 font-bold">Hacked!</strong>
<script>alert('You have been compromised.');</script>
```
If your view file simply echoes this data blindly (`<?= $note['body'] ?>`), the browser will not treat it as plain text. It will see the HTML tags and execute them. The text will turn red and bold, and the JavaScript alert will pop up for anyone who views that note. This is called a **Cross-Site Scripting (XSS)** attack.

> **The Golden Rule:** Never trust user input. Assume that any data provided by a user is malicious.

### 3. The Fix: Escaping Output with `htmlspecialchars()`
To fix this, we must sanitize the data right before it is displayed on the page. PHP provides a built-in function for this: `htmlspecialchars()`.
This function takes a string and converts special characters into HTML entities.

* `<` becomes `&lt;`
* `>` becomes `&gt;`

When the browser sees `&lt;strong&gt;`, it does not interpret it as an HTML tag. Instead, it literally prints out the text `<strong>` safely on the screen.
### Secure View (views/notes.view.php)
```php
<?php foreach ($notes as $note) : ?>
    <li>
        <a href="/note?id=<?= $note['id'] ?>">
            <?= htmlspecialchars($note['body']) ?>
        </a>
    </li>
<?php endforeach; ?>
# Milestone 7: The Illusion of Client-Side Validation

> **Objective:** Understand why HTML-based validation is purely for user experience (UX) and how easily it can be bypassed using direct HTTP requests.

### 1. The Limits of Browser Validation
Adding HTML attributes like `required`, `minlength`, or `maxlength` to your forms is a great practice. It provides immediate, helpful feedback to the user before they even click submit. However, **client-side validation provides zero actual security**. 

Because this validation happens inside the user's browser, the user has complete control over it. A malicious user can simply open their browser's Developer Tools, delete the `required` attribute from the `<textarea>`, and submit an empty note.

### 2. Bypassing the Browser Entirely with cURL
Attackers don't even need to use a web browser to interact with your application. They can send HTTP requests directly to your server using command-line utilities like `curl`, completely circumventing your HTML form.

Here is how someone can manually force a `POST` request to your endpoint with an empty payload:

```bash
# Sending a POST request with an empty body directly to the server
curl -X POST http://localhost:8888/note/create -d "body="
```
# Milestone 8: Server-Side Validation & User Feedback

> **Objective:** Implement inline PHP validation to verify incoming data on the server, prevent invalid database queries, and display helpful error messages back to the user on the frontend.

### 1. The Validation Array Strategy
When validating a form, a single submission might have multiple issues (e.g., a missing title *and* a body that is too long). Rather than failing immediately on the first error, the standard approach is to initialize an empty `$errors` array and append messages to it as checks fail.

### 2. Inline Validation Logic (The Controller)
We can use PHP's `strlen()` (string length) function to check if the user provided any text. If the length is zero, we add an error message to the `$errors` array mapped to the `'body'` key. 

Crucially, we wrap our database `INSERT` query inside an `empty($errors)` check. The query will *only* execute if the `$errors` array has zero items.

**`controllers/note-create.php`**
```php
<?php
require 'Database.php';
$config = require 'config.php';
$db = new Database($config['database']);
$heading = 'Create Note';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // 1. Validate the input (trim removes accidental whitespace)
    if (strlen(trim($_POST['body'])) === 0) {
        $errors['body'] = 'A body is required.';
    }

    // 2. Only run the query if validation passed
    if (empty($errors)) {
        $db->query('INSERT INTO notes(body, user_id) VALUES(:body, :user_id)', [
            'body'    => $_POST['body'],
            'user_id' => 1
        ]);
    }
}
// 3. Load the view (the $errors array is passed along naturally)
require('views/note-create.view.php');
```
# Milestone 9: Advanced Validation & Preserving State

> **Objective:** Enforce maximum character limits on user input and improve UX by retaining previously typed data when a form fails validation.

### 1. Expanding Validation Rules (Max Length)
Currently, our application ensures a user provides *something*, but it doesn't stop them from submitting *too much*. Unrestricted inputs can bloat your database, create spam, or exceed the column limits defined in your SQL schema.

We can add a secondary check to our controller using `strlen()` to ensure the input stays under a reasonable limit, such as 1,000 characters.

**`controllers/note-create.php` (Updated)**
```php
<?php
// ... database connection setup ...

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Safely capture and trim the input
    $body = trim($_POST['body'] ?? '');
    
    // Rule 1: Minimum length (Cannot be empty)
    if (strlen($body) === 0) {
        $errors['body'] = 'A body is required.';
    }

    // Rule 2: Maximum length
    if (strlen($body) > 1000) {
        $errors['body'] = 'The body cannot be more than 1,000 characters.';
    }

    // Execute query only if all rules pass
    if (empty($errors)) {
        $db->query('INSERT INTO notes(body, user_id) VALUES(:body, :user_id)', [
            'body'    => $body,
            'user_id' => 1
        ]);
    }
}
```
### 2. The Frustration of Lost Input
When validation fails, we reload the view to show the error. However, because it's a fresh page render, any text the user typed is wiped out. If a user wrote 1,005 characters, they would lose their entire draft just because they were 5 characters over the limit. This is a terrible user experience.

### 3. Preserving State
To fix this, we need to inject the submitted $_POST data back into the HTML form. However, if the page is loaded normally (a standard GET request before submission), $_POST['body'] doesn't exist yet. Calling it blindly would throw an "undefined array key" warning.
```php
<?= isset($_POST['body']) ? $_POST['body'] : '' ?>
```
# Milestone 10: Extracting a Validator Class

> **Objective:** Refactor inline form validation by extracting the logic into a dedicated `Validator` class, ensuring controllers remain clean and validation rules can be reused across the application.

### 1. The Problem with Inline Validation
In the previous milestone, we wrote our validation logic directly inside the controller. While this works for a simple form, it quickly becomes unmanageable. If you have multiple forms across your application that need to validate strings, emails, or passwords, you would be forced to duplicate that `strlen()` logic everywhere. 

The solution is to extract this logic into its own dedicated class.

### 2. Creating the `Validator` Class
Create a new file named `Validator.php` (usually placed in a `Core` or `Http` directory depending on your exact structure). We will start by creating a single, pure method called `string()` to handle our minimum and maximum length checks.

**`Validator.php`**
```php
<?php

class Validator
{
    /**
     * Validate that a string falls within a specific character range.
     * Defaults to a minimum of 1 character and no maximum (INF).
     */
    public function string($value, $min = 1, $max = INF)
    {
        // 1. Trim the value to prevent "spacebar" bypasses
        $value = trim($value);

        // 2. Check if the length is within the bounds
        return strlen($value) >= $min && strlen($value) <= $max;
    }
}
```
### 3. Trimming Before Validation
Notice the $value = trim($value); line. This is a critical security and UX step. Without it, a user could bypass your "cannot be empty" rule by simply holding down the spacebar and submitting 10 empty spaces.

PHP's trim() function automatically strips whitespace from the beginning and end of a string. By trimming the value before checking its length, a string of 10 spaces becomes a string of 0 characters, correctly failing the validation check.
# Milestone 11: Dynamic Validation Rules (Min & Max Lengths)

> **Objective:** Upgrade the `Validator` class to handle dynamic minimum and maximum character limits, allowing us to merge multiple validation rules into a single, clean method call.

### 1. The Goal: Consolidating Rules
In Milestone 9, we had two separate `if` statements in our controller:
1. Check if the string was empty (less than 1 character).
2. Check if the string was too long (more than 1,000 characters).

Instead of writing a new method for every possible scenario (e.g., `validateNotEmpty()`, `validateNotTooLong()`), it is much smarter to expand our single `string()` method to accept flexible boundaries.

### 2. Default Parameters & Infinity (`INF`)
To make the method reusable, we define `$min` and `$max` parameters. 

By default, we assume a string must have at least `1` character. But what if there is no maximum limit for a particular form field? PHP provides a built-in constant called `INF` (Infinity). By setting the default `$max` to `INF`, the string can be infinitely long unless the developer explicitly restricts it.

**`Validator.php`**
```php
<?php

class Validator
{
    // $min defaults to 1, $max defaults to Infinity
    public function string($value, $min = 1, $max = INF)
    {
        $value = trim($value);

        return strlen($value) >= $min && strlen($value) <= $max;
    }
}
```
### 3. Refactoring the Controller
With our dynamic method in place, we can return to the controller and completely rip out the old, stacked if statements. We merge them into a single condition by negating our validator (!).
```php
$validator = new Validator();
// Merge the checks into one clean line
if (! $validator->string($_POST['body'] ?? '', 1, 1000)) {
    $errors['body'] = 'A body of no more than 1,000 characters is required.';
}
```
# Milestone 12: Pure Functions & Static Methods

> **Objective:** Understand the concept of "pure functions" and use PHP's `static` keyword to call validation methods directly from the class without needing to instantiate an object.

### 1. What is a "Pure Function"?
If you look closely at the `string()` method we just wrote, you'll notice it operates in complete isolation. 
* It does not rely on any outside variables.
* It does not reference other classes.
* It does not use `$this` to access internal object state.

It simply takes an input (`$value`), performs an operation on it (`trim` and `strlen`), and returns an output (`true` or `false`). This is known as a **pure function**. Because it doesn't depend on the state of a specific object instance, we don't actually need an "instance" of the `Validator` class to use it.

### 2. The `static` Keyword
When a method is pure and doesn't require an object instance to work, we can declare it as `static`. 

A `static` method belongs to the class itself, rather than to any specific object created from that class.

**`Validator.php` (Updated)**
```php
<?php

class Validator
{
    // Add the 'static' keyword before 'function'
    public static function string($value, $min = 1, $max = INF)
    {
        $value = trim($value);

        return strlen($value) >= $min && strlen($value) <= $max;
    }
}
```
### 3. Calling Static Methods (::)
Because the method is now static, we no longer need to use the new keyword to instantiate the Validator class in our controller.

Instead, we can trigger the method directly on the class name using the Scope Resolution Operator, which is just two colons (::).

# Milestone 13: Validating Email Addresses

> **Objective:** Expand the `Validator` class with a static method to validate email formats using PHP's built-in filtering functions.

### 1. The Scope of Email Validation
When we validate an email address in PHP, we are generally checking its *shape*, not its *existence*. 

A validation check will confirm that `joe@example.com` is formatted correctly (it has characters, an `@` symbol, and a valid domain extension). It will **not** ping an external mail server to verify if Joe actually owns that inbox. That level of verification is typically handled later by sending a confirmation link to the address.

### 2. Avoiding Regular Expressions
To check if a string matches the shape of an email, you could write a highly complex Regular Expression (Regex). However, email regex patterns are notoriously difficult to write and maintain. 

Instead, PHP provides a robust built-in function called `filter_var()`, which allows you to sanitize or validate strings using predefined filters.

### 3. Implementing the `email` Method
We will add a new static method to our `Validator` class. We will pass the user's input to `filter_var()` and apply the `FILTER_VALIDATE_EMAIL` constant.

**`Validator.php` (Updated)**
```php
<?php

class Validator
{
    public static function string($value, $min = 1, $max = INF)
    {
        $value = trim($value);
        return strlen($value) >= $min && strlen($value) <= $max;
    }

    public static function email($value)
    {
        // Uses PHP's built-in filter to check for a valid email format
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }
}
```