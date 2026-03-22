# IEEE ZSB Backend Phase 2: PHP Project Documentation

---

## Milestone 0: Core PHP Mechanics

> **Objective:** Build a strong baseline in PHP syntax before diving into application architecture. This section covers essential data handling, logic, and functional programming concepts.

### 1. Variables & Output
Variables in PHP are denoted by a `$` prefix and should ideally follow `camelCase` naming conventions. To render output to the browser, you can use `echo` or `print`.

* **`echo`:** Outputs one or multiple comma-separated strings. It doesn't return a value, making it marginally faster.
* **`print`:** Outputs a single string and always returns `1`, meaning it can be used inside expressions.

```php
<?php
$greeting = "Hello, World!";

// Standard output
echo $greeting;
print $greeting;

// Echoing multiple segments
echo "Hello", ", ", "World!";
?>

<?= $greeting ?>
```

## 2. String Concatenation  

Combine strings in PHP using the dot (`.`) operator.  

```php
<?php
$name     = "Ahmed";
$greeting = "Hello, " . $name . "!"; // Outputs: Hello, Ahmed!
```
## 3. Logic & Conditionals  

PHP uses standard boolean logic (`true`/`false`) and traditional `if/else` control structures, alongside ternary operators for concise assignments.  

```php
<?php
$isLoggedIn = true;

// Traditional if/else
if ($isLoggedIn) {
    echo "Welcome back!";
} else {
    echo "Please log in.";
}

// Ternary shorthand
echo $isLoggedIn ? "Welcome back!" : "Please log in.";
```
## 4. Arrays  

### **Indexed Arrays**  
Standard arrays use zero-based numeric indexes to store ordered lists.  

```php
<?php
$books = [
    ["Clean Code", 2008, "Robert C. Martin"],
    ["The Pragmatic Programmer", 1999, "Andrew Hunt"]
];

echo $books[0][0]; // Outputs: Clean Code
```
### Associative Arrays  

Similar to maps or dictionaries in other languages, associative arrays use named string keys.  

```php
<?php
$book = [
    "title"       => "Clean Code",
    "releaseYear" => 2008,
    "author"      => "Robert C. Martin",
];

echo $book["title"]; // Outputs: Clean Code
```
## 5. Iteration  

The `foreach` loop is the most effective way to traverse arrays in PHP.  

### Standard Syntax  

```php
<?php
foreach ($books as $book) {
    echo $book["title"] . " by " . $book["author"] . "\n";
}
```
## Template Syntax (Ideal for Views)  

To keep HTML clean, use the alternative colon/`endforeach` syntax combined with shorthand echo tags.  

```php
<?php foreach ($books as $book) : ?>
    <li><?= $book["title"] ?> — <?= $book["author"] ?></li>
<?php endforeach; ?>
```
## 6. Functions

Functions encapsulate reusable logic. PHP supports named functions, anonymous functions (closures), and callbacks.

```php
<?php
// 1. Named Function
function filterByAuthor(array $books, string $author): array {
    $result = [];
    foreach ($books as $book) {
        if (isset($book['author']) && $book['author'] === $author) {
            $result[] = $book;
        }
    }
    return $result;
}

// 2. Anonymous Function (assigned to a variable)
$filterByYear = function (array $books, int $year): array {
    $result = [];
    foreach ($books as $book) {
        if (isset($book['releaseYear']) && $book['releaseYear'] > $year) {
            $result[] = $book;
        }
    }
    return $result;
};

// 3. Using Built-in Callbacks (array_filter)
$filteredBooks = array_filter($books, function($book) {
    return isset($book['releaseYear']) && $book['releaseYear'] > 2000;
});
```
## 7. Superglobals
Superglobals are built-in variables that are always available in all scopes.
* **`$_GET`**: Contains data sent via URL query parameters (e.g., `?id=1`).
* **`$_POST`**: Contains data sent via HTTP POST (usually from forms).
* **`$_SERVER`**: Holds information about headers, paths, and script locations.
* **`$_SESSION`**: Used to store user data across multiple pages.

## 8. Code Organization: The `require` Statement
The `require` statement is used to import the content of one PHP file into another.
* **Separation of Concerns:** It allows you to keep your **Logic** (PHP) separate from your **Template** (HTML).
* **Dry Principle (Don't Repeat Yourself):** You can `require` a `header.php` or `functions.php` in every file instead of rewriting the same code.
* **Note:** If the file is missing, `require` will throw a fatal error and stop the script (unlike `include`, which only gives a warning).

## 9. Database & Security (PDO)
**PDO (PHP Data Objects)** is a lightweight, consistent interface for accessing databases. It allows your code to work with different database types (MySQL, PostgreSQL, etc.) using the same functions.
### DSN (Data Source Name)
The DSN contains all the information needed to connect to the database.

**Example (MySQL):**
```php
$dsn = "mysql:host=localhost;port=3306;dbname=myapp;charset=utf8mb4";
$pdo = new PDO($dsn, "username", "password");
```
```php
$statement = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$statement->execute([$id]);
```
### Constructor in a Class
If you find yourself constructing the PDO object repeatedly, encapsulate it in a class.
The __construct() method ensures the connection is created only once when the class is instantiated.
```php
<?php
class Database {
    private $connection;

    public function __construct(array $config, string $username = 'root', string $password = '') {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
        $this->connection = new PDO($dsn, $username, $password, [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    public function query(string $sql, array $params = []) {
        $statement = $this->connection->prepare($sql);
        $statement->execute($params);
        return $statement;
    }
}
```

### SQL Injection
This is a security vulnerability where an attacker "injects" malicious SQL code into your query through user input (like a form or URL). 
* **The Danger:** If you write `$query = "SELECT * FROM users WHERE id = " . $_GET['id'];`, an attacker can change the ID to `1 OR 1=1` to see everyone's data.

### The Fix: Prepared Statements
Prepared Statements send the SQL query template and the user data to the database **separately**.
1. The database "prepares" the query plan first.
2. The user data is then treated strictly as a "literal value," not executable code.
3. **Execution:** ```php
    $statement = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $statement->execute(['id' => $_GET['id']]);
    ```

# Milestone 1: Architecture — Views & Partials

**Objective:** Transition from monolithic files to a modular architecture, strictly separating business logic from HTML presentation.

## 1. The Core Philosophy

Mixing database queries, logic, and HTML in a single file creates an unmaintainable mess. We solve this by splitting responsibilities:

- **Logic (Controllers):** Handles data preparation (`index.php`).
- **Templates (Views):** Handles HTML rendering (`index.view.php`).
- **Partials:** Extracts shared UI components (headers, footers, navbars) to prevent repetition.

## 2. Directory Structure

Use a clear, predictable layout so developers can find code quickly and reason about responsibilities.

**Example layout:**

```text
project/
├── index.php             ← Controller: sets variables, requires view
├── functions.php         ← Shared utilities
└── views/
    ├── index.view.php    ← View: Assembles the page
    └── partials/
        ├── header.php    ← Boilerplate <head>
        ├── nav.php       ← Navigation menu
        └── footer.php    ← Boilerplate closing tags
```
## 3. Controller vs. View

Controllers prepare the environment, and views consume it. Because `require` inherits the variable scope of the file calling it, variables defined in the controller are immediately available in the view.

### index.php (Controller)

```php
<?php
$heading = 'Home';
require 'views/index.view.php';
```
### views/index.view.php (View)

```php
<?php require 'partials/header.php'; ?>
<?php require 'partials/nav.php'; ?>

<main>
    <h1><?= htmlspecialchars($heading) ?></h1>
    <p>Welcome to the homepage.</p>
</main>

<?php require 'partials/footer.php'; ?>
```
## 4. Dynamic Navigation & Superglobals

To highlight the active navigation link, leverage the `$_SERVER` superglobal and a small helper in `functions.php`.

```php
<?php
function urlIs(string $value): bool {
    // parse_url safely ignores query strings (e.g., ?ref=email)
    return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === $value;
}
```
## 1. Why Use a Router

Without a router, users directly access files (/about.php). This exposes your file structure, scatters logic, and makes handling 404 errors sloppy. A router funnels all requests through a single entry point, deciding which controller to load based on the URL.
```text
project/
├── index.php             ← Front Controller: Loads dependencies & router
├── router.php            ← Maps URIs to specific controllers
├── controllers/          ← Business logic files moved here
│   ├── index.php
│   └── about.php
└── views/
    └── 404.php           ← Controlled error page
```

## 3. Router Implementation (router.php)
The router extracts the clean URL path, checks it against a predefined routing table, and either loads the correct controller or gracefully aborts.
```php
<?php
/ Extract path, dropping query strings
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Routing table
$routes = [
    '/'        => 'controllers/index.php',
    '/about'   => 'controllers/about.php',
    '/contact' => 'controllers/contact.php',
];

// Dispatcher
if (array_key_exists($uri, $routes)) {
    require $routes[$uri];
} else {
    abort();
}

// Error handler
function abort($code = 404) {
    http_response_code($code);      // Sets actual HTTP status header
    require "views/{$code}.php";    // Double quotes parse the $code variable
    die();                          // Halts further execution
}
```
# Milestone 3: Database Integration (PDO)

## Objective
Establish a secure, abstract database connection using a dedicated **Database class** and **configuration files**.

## 1. Why PDO?
**PHP Data Objects (PDO)** is an abstraction layer.  
It provides:
- A unified API for various databases (MySQL, PostgreSQL, etc.)
- Built-in protection against **SQL injection** via **Prepared Statements**
## 2. Configuration (`config.php`)

Store sensitive or environment-specific data in an isolated file that simply returns an array.

### PHP Example

```php
<?php
return [
    'database' => [
        'host'    => 'localhost',
        'port'    => 3306,
        'dbname'  => 'myapp',
        'charset' => 'utf8mb4',
    ]
];
```
## 3. The Database Class

This class wraps the native **PDO** object, dynamically builds the connection string (DSN), and handles query execution securely.

### PHP Example

```php
<?php
class Database {
    public $connection;

    public function __construct(array $config, string $username = 'root', string $password = '') {
        // Dynamically build the Data Source Name (DSN)
        $dsn = 'mysql:' . http_build_query($config, '', ';');

        $this->connection = new PDO($dsn, $username, $password, [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Return rows as associative arrays
        ]);
    }

    public function query(string $query, array $params = []) {
        $statement = $this->connection->prepare($query);
        $statement->execute($params);
        return $statement;
    }
}
```
## 4. Security: Defeating SQL Injection

Never concatenate user input directly into a SQL string.  
Doing so allows malicious users to append destructive SQL commands to your queries.

### ❌ Vulnerable Approach

```php
$id = $_GET['id'];
// A user passing '1 OR 1=1' will expose the entire table
$db->query("SELECT * FROM users WHERE id = $id");
## ✅ Secure Approach (Prepared Statements)

Instead of concatenating user input directly, use **Prepared Statements**.  
The database parses the query structure **before** inserting the parameter, preventing SQL injection.

### PHP Example

```php
$id = $_GET['id'];
// The database parses the query structure BEFORE inserting the parameter
$db->query("SELECT * FROM users WHERE id = ?", [$id])->fetchAll();
```
## Why Placeholders Work

By using `?` placeholders and passing values via the `execute()` method,  
the database strictly treats the input as **literal data**, not executable SQL.  
This completely neutralizes injection attacks.
## 5. Application Bootstrapping (`index.php`)

Your main entry point now orchestrates the initialization sequence:

### PHP Example

```php
<?php
require 'functions.php';
require 'Database.php';

$config = require 'config.php';
$db = new Database($config['database']);

// Router is required last, so controllers have access to $db
require 'router.php';
```