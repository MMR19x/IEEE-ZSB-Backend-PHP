
# PHP OOP Research Questions

### Inhertiance 

* **Inheritance** allows classes to share their attributes, methods and functionalities to other classes. So in order to link those classes inheritance adopts a hierarchical schema relationship.

* In inheritance hierarchy we have the parent class which is the class that gives its features to another class. They can also be called base class or super class.
* On the other hand we have the child class which is the class that inherits the features from another class. They can also be called sub class or derived class.

* The main benefit of inheritance in Object-Oriented Programming (OOP) is code reusability, which allows child classes to automatically inherit, reuse, and extend the methods and fields of a parent class. This reduces code redundancy, minimizes development time, and increases maintainability by allowing changes to be made in one place. 

```php
<?php
// Parent class
class Fruit {
  public $name;
  public $color;

  public function __construct($name, $color) {
    $this->name = $name;
    $this->color = $color;
  }

  public function intro() {
    echo "The fruit is $this->name and the color is $this->color.<br>";
  }
}

// Strawberry is child class which inherited from Fruit
class Strawberry extends Fruit {
  public function message() {
    echo "Am I a fruit or a berry? ";
  }
}

$strawberry = new Strawberry("Strawberry", "red");
$strawberry->intro();
$strawberry->message();
?>
```

---

### Final keyword

* The `final` keyword in PHP is used to restrict inheritance and prevent further modifications to specific parts of your object-oriented code.

* Before a Class: It prevents the class from being extended by any other class. If a developer attempts to create a subclass using the `extends` keyword, PHP will throw a Fatal Error: "Class [ChildClass] may not inherit from final class ([ParentClass])".

```php 
<?php
final class BaseClass {
   public function test() {
       echo "BaseClass::test() called\n";
   }

   // As the class is already final, the final keyword is redundant
   final public function moreTesting() {
       echo "BaseClass::moreTesting() called\n";
   }
}

class ChildClass extends BaseClass {
}
// Results in Fatal error: Class ChildClass may not inherit from final class (BaseClass)
?>
```


* Before a Method: It prevents that specific method from being overridden in any child class. While the class itself can still be inherited, any attempt to redefine a `final` method in a subclass will result in a Fatal Error: "Cannot override final method [ParentClass]::methodName".

```php 
<?php
class BaseClass {
   public function test() {
       echo "BaseClass::test() called\n";
   }
   
   final public function moreTesting() {
       echo "BaseClass::moreTesting() called\n";
   }
}

class ChildClass extends BaseClass {
   public function moreTesting() {
       echo "ChildClass::moreTesting() called\n";
   }
}
// Results in Fatal error: Cannot override final method BaseClass::moreTesting()
?>
```

* A developer would use the `final` keyword in Java primarily to enforce immutability, prevent unexpected modifications, improve security, and aid performance. It restricts re-assignment of variables, overriding of methods, and inheritance of classes, creating more robust and predictable code.

---

### Overriding Methods

* **Overriding** a method in a child class means redefining a method already present in the parent (superclass) to provide a specific, customized implementation. When an object of the child class calls this method, the child's version executes instead of the parent's. It is used for specialization and runtime polymorphism.

```php
<?php
class BaseClass {
    public function showMessage() {
        return "Base message";
    }
}

class DerivedClass extends BaseClass {
    #[\Override]
    public function showMessage() {
        // Explicitly overriding to provide a different message.
        return "Derived message";
    }
}
```
* To call an original parent method from within a child's overridden method in PHP, use the `parent::` keyword followed by the method name.

* **Method Extension**: This is used when you want to keep the original functionality of the parent method and add new behavior on top of it.

```php 
class ParentClass {
    public function greet() {
        return "Hello from Parent!";
    }
}

class ChildClass extends ParentClass {
    public function greet() {
        // Calling the original parent method
        $parentMessage = parent::greet(); 
        return $parentMessage . " And welcome from Child!";
    }
}
```

---

### Abstract Class vs. Interface

The main difference between an **abstract class** and an **interface** is that abstract classes provide a partial base implementation for closely related objects and support constructors/state, whereas interfaces define a contract for behavior (often for unrelated classes) and focus entirely on method signatures rather than implementation.

* 1. Interfaces cannot have properties, while abstract classes can
* 2. All interface methods must be public, while abstract methods can be public or protected
* 3. All methods in an interface are abstract, so they cannot be implemented in code and the abstract keyword is not necessary
* 4. Classes can implement an interface while inheriting from another class at the same time

```php 
<?php
interface Animal {
  public function fromFamily();
  public function makeSound();
}

class Cat implements Animal {
  public function fromFamily() {
    echo "From family: Felidae (Relatives: lions, tigers, jaguars, lynx, cougars, and cheetahs).<br>";
  }
  public function makeSound() {
    echo "Sound: Meow.";
  }
}
```
```php
<?php
// Abstract base class
abstract class Car {
  public $name;

  // Non-abstract method
  public function __construct($name) {
    $this->name = $name;
  }

  // Abstract method - forces child classes to implement it
  abstract public function intro();
}

// Child class that extends the abstract class
class Audi extends Car {
  public function intro() {
    return "German quality! I'm an $this->name!";
  }
}
```

* A class can implement multiple interfaces in most modern object-oriented programming languages.

* Syntax: Multiple interfaces are listed after the implements keyword, separated by commas.

---


###  Polymorphism

* is a core concept in object-oriented programming (OOP) derived from Greek, meaning "many forms". It refers to the ability of an entity—such as a variable, function, or object—to take on multiple forms, allowing a single interface to represent different underlying data types or behaviors This means one entity can take many forms.

* 1. Multiple Behaviors: The same method can behave differently depending on the object that calls this method.
* 2. Method Overriding: A child class can redefine a method of its parent class.
* 3. Method Overloading: We can define multiple methods with the same name but different parameters.
* 4 . Runtime Decision: At runtime, Java determines which method to call depending on the object's actual class.

```php 
// Base class Person
class Person {

    // Method that displays the
    // role of a person
    void role() { System.out.println("I am a person."); }
}

// Derived class Father that
// overrides the role method
class Father extends Person {

    // Overridden method to show
    // the role of a father
    @Override void role()
    {
        System.out.println("I am a father.");
    }
}

public class Main {
    public static void main(String[] args)
    {

        // Creating a reference of type Person
        // but initializing it with Father class object
        Person p = new Father();

        // Calling the role method. It calls the
        // overridden version in Father class
        p.role();
    }
}
```

### Polymorphism 

Imagine you are building a checkout page. You want to be able to "process" a payment, but the logic for a Credit Card is very different from the logic for PayPal.

* 1. The Parent Class (The Blueprint)
First, we define a base class that says, "Any payment method must have a process() method."

* 2. Why is this useful?
The power of polymorphism is that your Checkout code doesn't need to know which payment method the user chose. It just knows that whatever it is, it has a process() method.

```php
function finalizeOrder(Payment $method, $total) {
    // This function works for CreditCard, PayPal, or any future method!
    $method->process($total);
}

$userChoice = new PayPal();
finalizeOrder($userChoice, 150); 
// Output: Redirecting to PayPal... Deducting $150 from wallet.
```


```php

class CreditCard extends Payment {
    public function process($amount) {
        // Logic specific to banks and encryption
        echo "Validating card... Charging $" . $amount . " to the bank.";
    }
}

class PayPal extends Payment {
    public function process($amount) {
        // Logic specific to API tokens and digital wallets
        echo "Redirecting to PayPal... Deducting $" . $amount . " from wallet.";
    }
}
```
** the Classic Example: "Make Sound"

** Imagine you have a group of animals. Even though they are all different, you know that every animal can "make a sound."

** If you tell a Dog to makeSound(), it will bark. If you tell a Cat to makeSound(), it will meow. You are using the exact same command for both, but the result depends on which animal is "behind" the method.

```php

class Animal {
    public function makeSound() {
        echo "Some generic animal sound";
    }
}

class Dog extends Animal {
    public function makeSound() {
        echo "Woof! ";
    }
}

class Cat extends Animal {
    public function makeSound() {
        echo "Meow! ";
    }
}

// Polymorphism in action:
$animals = [new Dog(), new Cat()];

foreach ($animals as $animal) {
    // We don't need to check if it's a dog or cat. 
    // We just call the same method name.
    $animal->makeSound(); 
}
```