
# PHP MVC Research Questions

### Database in MVC:

* In the MVC  pattern, **the Model** is the only part of the application that should be allowed to talk directly to the database.

* The Model is responsible for managing the data, business rules, and logic of the application.

---

1. Security and ProtectionThe Model acts as a security gate for your data. the implementation of a `save()` method showed that the Model uses PDO (PHP Data Objects) and Prepared Statements. 

*  By forcing all database communication through the Model, you ensure that every query is "sanitized". 

* This centralizes the defense against SQL Injection, making it much harder for a developer to accidentally leave a vulnerability in a Controller or View.

---


2. Abstraction (The "Need to Know" Basis)

* The Controller and View should be "blind" to the type of database you are using.  

* The Controller asks: "Give me the profile for user #10".  

* The Model handles the "how": It writes the SQL, connects to the server, and returns a clean object.

---


3. The DRY Principle (Don't Repeat Yourself)

* If you were to write SQL queries directly inside a Controller, you would eventually find yourself copying and pasting the same code across multiple files.

---

### Sensitive Information:

* Storing sensitive information like database passwords in separate configuration files or environment variables is a fundamental security practice. Hardcoding these "secrets" directly into source code introduces significant risks that grow as projects scale and teams collaborate.

---


1. Security and Version Control

* The most dangerous thing about hardcoding a password is that it becomes part of your code's history.

* The Risk: If you push your code to a repository like GitHub, your private credentials are now visible to anyone with access to that repo—or the entire world if it's public.

* The Solution: By using a separate file (like `.env` or a specific `config.php`) and adding it to your `.gitignore`, you ensure that your secrets never leave your local machine or your secure server.

---


2. Environment Flexibility 

* Separating configuration from code allows the same application to run in different environments (development, staging, production) without modification.

---


3. Maintenance and Team Collaboration

* Single Point of Change: If your database administrator changes the password, you only have to update one line in one config file. If you hardcoded it, you might have to hunt through dozens of files to find every instance.

* Team Access: You might want a junior developer to work on your CSS or HTML views without giving them full administrative access to your production database. Separating the config allows you to hide those "keys" from people who don't need them.


---

### PDO:

* **PDO** stands for PHP Data Objects. It is a database abstraction layer that provides a consistent way to interact with various databases in PHP.

* PDO is preferred over older methods like `mysqli` for modern PHP development because it is more secure, flexible, and fully object-oriented.

---


1. Database Independence

* The "abstraction" in PDO means it doesn't care which database you use, while mysqli is locked to MySQL only.  

* PDO supports over 12 different database drivers (PostgreSQL, SQLite, Oracle, etc.).  

* Benefit: If you decide to switch your Notes App from MySQL to PostgreSQL later, you only change the connection string; your query logic remains exactly the same.

---

2. Security via Prepared Statements

* PDO provides built-in protection against SQL injection by utilizing prepared statements, which separate the SQL command from the user data.

* Instead of putting user input directly into a query, you use placeholders (like :email or :id).  

* The database "prepares" the query structure first, and then the values are "bound" separately.

---

3. Named Parameters

* PDO supports named parameters (e.g., :`username`), making complex queries easier to read and maintain. mysqli primarily uses positional parameters (`?`).


---

### Prepared Statements:

* Prepared statements (or parameterized queries) protect websites from SQL injection by strictly separating SQL code from user-supplied data. Instead of concatenating input directly into queries, placeholders (`?`) are used, ensuring the database treats user input strictly as data literals rather than executable commands.

* First, the application sends a SQL template to the database server. This template uses placeholders (like :`email` or `?`) instead of actual values.

* The Database Action: The database parses, compiles, and optimizes this query structure before any data ever touches it.

* The Result: The database "freezes" the logic. It knows exactly what the command is (e.g., `SELECT` from a specific table) and where the data is supposed to go, but it doesn't execute anything yet.

* Later, the application sends the actual user input separately.

* The Database Action: The database takes these values and "plugs" them into the placeholders of the alreadycompiled template.


---


### Database Query:

1. Fetching a Single Row

* You need exactly one row when you are looking for a unique entity.this is usually handled by a `findOne()` method or a query that uses a Primary Key (like `id`) or a Unique Constraint (like `email`).

* *User Login* 

* When a user tries to log into your application, you query the database for a user where the email matches what they typed.

* Since every email is unique, the database will return exactly one row representing that specific person.  

* You then take that single object to verify their password and start their session.

---


2. Fetching Multiple Rows

* You need an array of multiple rows when you are displaying lists, feeds, or search results. This is where you use a `find()` or `findAll()` method, often combined with a `WHERE` clause to filter the group.

* *The Notes Dashboard*

* In a Notes App, when a user clicks on "My Notes," the Controller doesn't want just one note; it wants every note that belongs to that specific `user_id`.  

* The database returns an array of rows, which the View then loops through (using a `foreach` loop) to display each note as a card on the screen.

