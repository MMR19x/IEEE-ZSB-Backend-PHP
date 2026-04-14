# IEEE ZSB Backend Phase 2 — PHP Project Documentation

---
## section 1:Login System & Session Management Summary
## 1. Routing & Resource Naming
To maintain a RESTful structure, login/logout is treated as managing a **Session** resource.

* **GET `/login`**: Displays the login form (Controller: `Sessions\Create`).
* **POST `/login`**: Handles the login logic (Controller: `Sessions\Store`).
* **DELETE `/session`**: Ends the user session (Controller: `Sessions\Destroy`).
    * *Note:* Since HTML forms don't support `DELETE`, use a hidden `_method` input with the value `DELETE`.

---

## 2. Authentication Flow
The core logic for verifying a user involves three distinct steps:

1.  **Email Lookup**: Query the database for a record matching the provided email.
2.  **Password Verification**: Use `password_verify($password, $user['password'])` to check the plain-text input against the hashed database value.
3.  **Generic Error Handling**: If either the email is missing or the password is wrong, return a generic message: *"No matching account found."* This prevents "user enumeration" (attackers checking if an email exists).

---

## 3. Session Security & Helpers
### The `login()` Helper
Abstracting the login logic into a helper function ensures consistency across registration and login:
```php
function login($user) {
    $_SESSION['user'] = [
        'email' => $user['email']
    ];
    session_regenerate_id(true); // Prevents session fixation attacks
}
```

Session Regeneration
Always call `session_regenerate_id(true)` during login. This changes the session ID while keeping the data, making it harder for attackers to hijack a session.

---

## 4. Proper Logout Procedure
Logging out is more than just clearing a variable. A complete logout involves:
### 1.  **Clear Superglobal**: `$_SESSION = [];`
### 2.  **Destroy Session File**: `session_destroy();`
### 3.  **Expire the Cookie**: Clear the PHPSESSID cookie by setting its expiration date to a point in the past.
* Use `session_get_cookie_params()` to ensure the path and domain match the original cookie.

--- 

## 5. Navigation UI Logic
Use session state to toggle UI elements in your navigation bar:
* Guest Mode: Show "Login" and "Register" links.
* Auth Mode: Show the "Logout" button and "Notes" (or other protected resources).
* Protection: Even if a link is hidden, always use Middleware on the routes to prevent direct URL access by unauthorized users.
---

## section 2: Refactoring & Architecture: IEEE-ZSB Backend

---

##  1. Structural Philosophy: Core vs. HTTP

To improve maintainability, the project structure is now split based on the "lifespan" and "purpose" of the code:

### **`core/` (The Framework)**
* **Purpose:** Infrastructure code that could theoretically be reused in other projects.
* **Contents:** Router, Database, Container, Validator, and base functions.

### **`Http/` (The Application)**
* **Purpose:** Code unique to this specific application’s web interface.
* **Contents:**
    * **`controllers/`**: Logic for handling specific routes.
    * **`Forms/`**: Classes dedicated to validating specific user inputs (e.g., `LoginForm`).

---

##  2. Router Convention Update

Instead of manual, hardcoded paths in the `routes.php` file, the Router now follows a directory-based convention.

* **Old Way:** `controllers/sessions/store.php`
* **New Way:** Simply use `sessions/store.php`.
* **Implementation:** The `Router` class was modified to automatically prepend `Http/controllers/` to the requested path. This keeps the routing file clean and easier to read.

---

##  3. The `LoginForm` Class Refactor

Validation logic has been extracted from the controller into a dedicated class. This follows the **Single Responsibility Principle**.

### **Key Features:**
* **Encapsulation**: The `$errors` property is `protected` to prevent external tampering.
* **Getter Method**: Use `public function errors()` to retrieve the error array safely.
* **Boolean Validation**: The `validate()` method returns a simple `true/false`, making the controller logic much cleaner.

### **Code Comparison:**

**Before (In Controller):**
```php
$errors = [];
if (!Validator::email($email)) { $errors['email'] = '...'; }
// ... messy procedural code ...
```
**After (In Controller):**
```php
$form = new LoginForm();

if (!$form->validate($email, $password)) {
    return view('session/create.view.php', [
        'errors' => $form->errors()
    ]);
}
```
##  4. The PRG Pattern (Post-Redirect-Get)

By refactoring the authentication flow, we ensure the PRG Pattern is maintained:

## 1. **POST**: User submits credentials.
## 2. **REDIRECT**: After successful login, the server sends a header('location: /').
## 3. **GET**: The browser loads the home page.
Benefit: This prevents the "Confirm Form Resubmission" error when a user hits the refresh button.

---

##  5.  Security Best Practices
* Generic Messaging: Use "No matching account found" for both failed emails and failed passwords to prevent user enumeration.
* Session Fixation: Always call session_regenerate_id(true) upon successful login to secure the user's session.

---

## section 3: Authentication Logic & Refactoring

This phase of the refactor focuses on extracting authentication logic into a dedicated service and streamlining controller readability.

---

## 1. The Authenticator Class
The `Authenticator` class acts as the "Gatekeeper" of the application.

### Key Methods:
* **`attempt($email, $password)`**: 
    * Checks if the user exists in the database.
    * Verifies the hashed password using `password_verify()`.
    * Calls `login()` if successful.
    * Returns `true` on success, `false` on failure.
* **`login($user)`**: Handles session population and ID regeneration.
* **`logout()`**: Clears session data and expires cookies.

---

## 2. New Global Helper: `redirect()`
To maintain clean code and ensure scripts stop executing after a redirect, a helper was added to `functions.php`:

```php
function redirect($path) {
    header("location: {$path}");
    exit();
}
```
## 3. Improving the `LoginForm` Class
We added a "manual override" for errors to allow the class to handle both structural validation (regex/email format) and logical validation (credentials matching).

```php
public function error($field, $message) {
    $this->errors[$field] = $message;
    return $this;
}
```
---
## 4. Final Controller Logic (The "Happy Path")
With these changes, the Sessions\Store controller now reads like a story:

* **Instantiate** the Form.
* **Validate** the inputs.
* **Attempt** authentication through the Authenticator.
* **Redirect** on success OR Add Error and return the view on failure.

"The goal of refactoring is not to change what the code does, but to change how clearly it explains itself."

---

## section 4:  PRG Pattern & Session Flashing

## Why PRG?
Prevents "Confirm Form Resubmission" by ensuring every POST is followed by a Redirect to a GET request.

## Flash Data Lifecycle
1. **Request A (POST):** Form fails. We `Session::flash('errors', [...])`.
2. **Redirect:** Browser moves to Request B.
3. **Request B (GET):** Page loads. `Session::get('errors')` pulls the data.
4. **Cleanup:** At the end of Request B, `Session::unflash()` deletes the errors so they don't appear again on a refresh.

## The Session Class API
- `Session::put(key, value)`
- `Session::get(key, default)`
- `Session::flash(key, value)`
- `Session::destroy()`
- `has($key)`
- `flush()`
- `unflash()`

* The Strategy: "Flash" the Input
In your StoreController (or wherever you handle the form submission), you need to capture the user's input before you redirect them back.
```php
// Inside your Session/Store controller
if (!$form->validate($email, $password)) {
    // 1. Flash the errors (as we did before)
    Session::flash('errors', $form->errors());

    // 2. Flash the input (except the password, for security!)
    Session::flash('old', [
        'email' => $_POST['email']
    ]);

    return redirect('/login');
}
```
* The `old()` Helper Function
Writing `Core\Session::get('old')['email'] ??''` inside your HTML is ugly and prone to errors. Instead, we’ll create a global helper in `functions.php` to make our views look like professional code.

```php
/**
 * Retrieve old form input from the session.
 */
function old($key, $default = '') {
    // Look into the 'old' array we flashed to the session
    $old = Core\Session::get('old');

    return $old[$key] ?? $default;
}
```
---

## section: 5 Advanced Validation: The "Happy Path" Refactor

This refactor represents a major architectural shift in the IEEE-ZSB backend, moving from manual, repetitive conditional checks to a centralized, exception-driven validation system.

---

## 1. The Core Philosophy: The "Happy Path"
In a standard controller, logic often gets buried under "guard clauses" (if statements checking for errors). 

**The Goal:** Write the controller logic as if everything will go right. If something goes wrong, the system should "short-circuit" automatically.

* **Before:** The controller manually checks validation, manually flashes session data, and manually redirects.
* **After:** The controller calls `$form->validate()`. If it fails, an exception is thrown, caught globally, and the user is redirected before the rest of the controller even runs.

---

## 2. The `ValidationException` Class
Instead of returning `true` or `false`, we use a custom Exception to carry our data across the application.

```php
namespace core;

class ValidationException extends \\Exception {
    public readonly array $errors;
    public readonly array $old;

    public static function throw($errors, $old) {
        $instance = new static;
        $instance->errors = $errors;
        $instance->old = $old;

        throw $instance;
    }
}
```
## 3. Global Exception Handling (The Front Controller)
By wrapping the routing logic in `public/index.php` with a try-catch block, we handle every validation failure in the entire application in one place.

```php 
// public/index.php
try {
    $router->route($uri, $method);
} catch (ValidationException $exception) {
    Session::flash('errors', $exception->errors);
    Session::flash('old', $exception->old);

    return redirect($router->previousUrl());
}
```
## 4. Refactored LoginForm Logic
The `LoginForm` now acts as its own "Static Constructor."

1. `validate($attributes)`: Instantiates the form and checks rules.
2. `failed()`: Checks if the error array is populated.
3. `throw()`: Triggers the ValidationException.

---
## 5. The "New" Controller Flow
The controller is now a clean, linear "Manager" of the process:

```php 
// 1. Validation (Short-circuits here if it fails)
LoginForm::validate($attributes = [
    'email' => $_POST['email'],
    'password' => $_POST['password']
]);

// 2. Authentication
$signedIn = (new Authenticator)->attempt(
    $attributes['email'], 
    $attributes['password']
);

// 3. Manual Exception Trigger (For Auth Failures)
if (! $signedIn) {
    (new LoginForm)->error('email', 'No matching account.')->throw();
}

// 4. Success (The Happy Path)
redirect('/');
```
---

## section: 6 Composer & PSR-4 Integration Guide

Integrating Composer moves the project from a manual, custom autoloader to the industry-standard tool for managing PHP packages and class loading.

---

## 1. Project Initialization
To start using Composer, you must create a configuration file that acts as the manifest for the application.

* **Command:** `composer init`
* **The .gitignore File:** During initialization, Composer will offer to add the `/vendor` folder to Git. **Always ensure this is included in your .gitignore.**
    * *Logic:* You should only track your specific source code and the configuration (`composer.json`). The actual library code is downloaded locally by running `composer install`.

---

## 2. Configuring PSR-4 Autoloading
**PSR-4** is the modern standard for mapping namespaces to specific directories. Instead of manually requiring files, Composer handles it based on rules defined in `composer.json`.



### **Namespace Mapping**
Update the `autoload` section to map the top-level directories:

```json
"autoload": {
    "psr-4": {
        "Core\\": "core/",
        "Http\\": "Http/"
    }
}
```
## 3. Activation in the Front Controller
Once the mapping is defined, the old manual `spl_autoload_register` function should be removed from the entry point and replaced with Composer's generated autoloader.

```php 
// public/index.php
require base_path('vendor/autoload.php');
```
This single line gives the application access to every class in the `Core\` and `Http\` namespaces, along with any third-party packages installed later.

## 4. Essential Composer Commands

- `composer install` => Downloads dependencies and generates the autoloader based on `composer.lock`.
- `composer dump-autoload` => Critical: Run this whenever you change the `autoload` section in composer.json.
- `composer require <package>` => Adds a new third-party library (e.g., a testing framework or a logger).
- `composer update` => Updates your dependencies to the latest versions allowed by your config.

## 5. Architectural Benefits

* **Interoperability**: The code now follows the same standards as professional frameworks like Laravel and Symfony.
* **Scalability**: Adding complex features (like PDF generation, email handling, or testing) now takes seconds instead of hours.
* **Cleanliness**: Eliminates the need for "require" statements at the top of every file, reducing cognitive load.

---

## section 7: Automated Testing in PHP: 

---

## 🛡️ 1. The Philosophy of Testing
Testing is not just about finding bugs; it is an essential architectural tool.
* **Confidence:** Automated tests provide a "safety net." You can refactor or change messy code with the assurance that if you break something, the tests will catch it immediately.
* **Refactoring Support:** Without tests, refactoring becomes risky, leading developers to leave "spaghetti code" untouched for years.
* **Documentation:** Tests serve as living documentation that describes exactly how a specific feature or function is intended to behave.

---

## 🧬 2. Testing Styles: Unit vs. Feature

| Style | Scope | Example |
| :--- | :--- | :--- |
| **Unit Test** | A single, isolated unit of code (class or function). | Testing if a `Validator::email()` method correctly identifies a valid email string. |
| **Feature Test** | A larger application feature from the user's perspective. | Simulating a user signing up, redeeming a referral code, and checking their balance. |

### The "Scratch Pad" Concept
Testing can act as a design tool. By writing a test first, you are forced to identify the **Nouns** (classes) and **Verbs** (methods) your application needs before writing any implementation logic.

---

## 🔄 3. TDD (Test Driven Development)
The video discusses the practice of writing tests *before* writing the implementation code.
* **Advice:** Don't be dogmatic. If TDD doesn't fit your current headspace, it is perfectly acceptable to write the implementation first and "backfill" with tests later.
* **The Goal:** Make testing work for your productivity, not against it.

---

## 🛠️ 4. The Workflow: Arrange, Act, Assert
A standard structure for writing a test follows these three steps:
1.  **Arrange:** Set up the environment, instantiate the class, or prepare the data.
2.  **Act:** Execute the specific method or action you want to test.
3.  **Assert (Expect):** Compare the actual output against your expectation (e.g., `expect($result)->toBeTrue()`).

---

## 🏗️ 5. Practical Implementation: The Validator Test
The segment demonstrates testing a `Validator` class:
* **The Process:** Start with a failing test (e.g., calling a method that doesn't exist yet).
* **Progress:** Use the failure message to guide the creation of implementation code.
* **Edge Cases:** Tests are used to ensure the validator handles empty strings, minimum/maximum character counts, and properly formatted data.

---

## 🏷️ 6. PHP Type Hinting & Return Values
As an aside during testing, the importance of PHP's **Type System** is highlighted:
* **Input Types:** Explicitly declaring that a parameter must be a `string` or `int`.
* **Return Types:** Declaring that a function must return a `bool`.
* **Benefit:** Using types makes the code more self-documenting and prevents "type-juggling" bugs that might occur during refactoring.

---

## 🚀 7. Key Takeaways
* **Pest PHP:** An elegant, expressive testing framework built on top of PHPUnit.
* **Managerial Delegation:** Good tests allow you to delegate work to others by providing a clear set of success criteria.
* **Necessity:** For any project intended to live for more than a few weeks or be used by others, automated testing is a requirement.
"""