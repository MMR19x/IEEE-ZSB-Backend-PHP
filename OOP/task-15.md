
# PHP OOP Research Questions

### Difference between Class and Object.

* **Class** : A blueprint, template, or "user-defined data type" that defines the structure (properties) and behavior (methods) for a specific entity.

`class Cars {
  // PHP code goes here...
}`

* **Object**: A concrete, individual "instance" of a class that exists in memory and contains actual data.

`$objectName = new ClassName($value);`


```php
<?php
class Car {  // Class definition
    public $brand, $model, $year;
    function __construct($brand, $model, $year) {
        $this->brand = $brand;
        $this->model = $model;
        $this->year = $year;
    }
    function displayInfo() {
        echo "$this->brand $this->model, $this->year";
    }
}
$car = new Car("Toyota", "Corolla", 2021);  // define object from class
$car->displayInfo(); 
?>
```
---

### $this vs. self::

* **self ::** : The self operator represents the current class and thus is used to access class variables or static variables because these members belong to a class rather than the object of that class. `self::$static_member`

* **$this** : represents the current object of a class. It is used to access non-static members of a class. `$this->name = $name;`

| Feature | `$this` | `self` |
| :--- | :--- | :--- |
| **Refers to** | The current **object** (instance). | The current **class** definition. |
| **Context** | Non-static context. | Static context. |
| **Accesses** | Properties and methods specific to an instance. | Static properties, static methods, and constants. |
| **Operator** | Object operator (`->`). | Scope Resolution Operator (`::`). |
| **Required?** | No, if you aren't using objects. | Yes, for static members. |

* You use `$this` for Instance Members. This is for anything that changes from one object to another.

* You use `self` for Static Members. This is for anything that is exactly the same for every single object created from that class.

---

### public, protected, and private 

Public, private and protected are called access modifiers. The visibility of a property, a method or a constant can be defined by prefixing the declaration with these keywords. 

* **Public** : can be accessed everywhere.

* **protected** : it can be accessed only within the class itself and by inheriting child classes.

* **Private** : it may only be accessed by the class that defines the member.

The "Cooling System" Analogy: By making the property private, you protect the "internal machinery." The only way to change the temperature is through a method that includes Validation Logic (Safety Constraints).

```php
class CoolingSystem {
    private $targetTemp = 22;

    // The "Gatekeeper" method
    public function setTemperature($newTemp) {
        // Safety check: The machine can only handle 16°C to 30°C
        if ($newTemp >= 16 && $newTemp <= 30) {
            $this->targetTemp = $newTemp;
            echo "Temperature set to " . $this->targetTemp . "°C";
        } else {
            // Rejection: Protects the hardware from "User Data"
            echo "Error: Temperature out of safe engineering limits!";
        }
    }
}

$ac = new CoolingSystem();
$ac->setTemperature(25);   // Success: 25 is safe.
$ac->setTemperature(500);  // Blocked: The system stays safe at 25.
```
---

##  Typed Properties

**Typed Properties** allow you to declare the data type a property must hold.

### Benefits:

1. **Type Safety**: Prevents bugs by ensuring only correct data types are assigned
2. **Self-Documenting**: Makes code clearer about what data is expected
3. **IDE support**: Catches type mismatches immediately and Better autocomplete.

### PHP Example - WITHOUT Typed Properties:

```php
<?php
class Product {
    public $name;
    public $price;
    public $inStock;

    public function __construct($name, $price, $inStock) {
        $this->name = $name;
        $this->price = $price;
        $this->inStock = $inStock;
    }

    public function calculateTotal($quantity) {
        return $this->price * $quantity;  // What if price is a string?
    }
}

// BUG: Wrong types can be passed!
$product = new Product("Laptop", "expensive", "yes");  // price is string!

// This will cause unexpected behavior or errors
// echo $product->calculateTotal(2);  // "expensive" * 2 = error or 0
?>
```

### PHP Example - WITH Typed Properties:

```php
<?php
class Product {
    public string $name;        // Must be string
    public float $price;        // Must be float
    public bool $inStock;       // Must be boolean

    public function __construct(string $name, float $price, bool $inStock) {
        ...
    }

    public function calculateTotal(int $quantity): float {
        ...
    }
}
?>
```

### Common Type Declarations:

```php
<?php
class TypeExamples {
    public int $age;                    // Integer
    public float $salary;               // Float/decimal
    public string $name;                // String
    public bool $isActive;              // Boolean
    public array $hobbies;              // Array
    public ?string $middleName;         // Nullable string (can be null)
    public DateTime $createdAt;         // Object type
}
?>
```

---

## Constructor Methods

* **__construct()** Method: __construct is a public magic method that is used to create and initialize a class object. __construct assigns some property values while creating the object. This method is automatically called when an object is created.

* __construct is a public magic method.
* __construct is a method that must have public visibility
* __construct method can accept one and more arguments.
* __construct method is used to create an object.
* __construct method can call the class method or functions
* __construct method can call constructors of other classes also.

`function __construct() {
    // Initialize the object properties
}`

* **Default Constructor**:  By default, __construct() method has no parameters. The values passed to the default constructor are default.

* **Parameterized Constructor**: In parameterized constructor __construct() method takes one and more parameters. You can provide different values to the parameters.

* **Copy Constructor**: In the copy constructor, the __construct() method accepts the address of the other objects as a parameter.

### important of passing arg to constructor:

* Ensuring a "Valid State" from the Start

Without constructor arguments, you often create an "empty" object and then use setters to fill it. The danger is that your code might try to use the object before you've finished setting it up.

In engineering, you don't build a generic "machine" and then decide it's a 50W motor. You build it as a 50W motor from the first bolt.

```php 
<?php

class student {
  
    // Class properties
    public $name;
    public $surname;
    
    // constructor with parameter
    public function __construct($name, $surname) {
        $this->name = $name;
        $this->surname = $surname;
    }

      // Display student data
    public function display() {
        echo "My name is " . $this->name 
              . "<br>Surname is " . $this->surname; 
    }
}
    
// Create class object and pass value
$user = new student("john", "biber");
$user->display();    

?>
```
