# Universal Variable PHP Library

A high level library for creating Universal Variable with PHP.


## Introduction

Universal Variable (UV) always requires some server-side development work to instantiate on each page of websites. At [Qubit](http://qubitproducts.com) we've created a PHP library to assist with this, thus requiring a little less 
intimate knowledge of client-side JavaScript.

This library is not prescriptive - it can be used to create the standard UV data layer, as well as extending it for more advanced and custom functionality.

Requires PHP version 5.2.0 or greater.


## Example

Create a page object within the UV and return json.

```php
$uv = new BuildUV();
$uv->get("page")->set("category", "Home");
$uv->get("page")->set("subcategory", "Mens - Shoes");

print $uv->toJSON()
=> {"page":{"category":"Home","subcategory":"Mens - Shoes"},"user":{},"events":[]}
```


## Installation

Download the library directly from Github:

```
git clone tbc@tbc.com
```

Include the `uv_library.php` file in any models/templates where you wish to instantiate UV.



## API

### Instantiation

Before you can run any methods, create the UV class:

```php
$uv = new BuildUV();
```

This automatically instantiates the standard specification object variables:

* page
* user
* product
* listing
* basket
* transaction
* events (array)


### get

Retreive a varaible within the UV. Works with any pre-declared variables.

```php
$uv->get("page")
=> UVObject Object ( )
```

The `get` method is usually only used to retreive a variable prior to setting one of its children.


### set

Set variables within the `product` object:

```php
$uv->get("product")->set("id", "12324214");
$uv->get("product")->set("name", "Sparkly Shoes");
$uv->get("product")->set("unit_price", 15);
```


### add 

Add a variable to an array. The following are the standard arrays found in UV:

* `listing.items`
* `basket.line_items`
* `transaction.line_items`
* `events`

```php

// Add event object to the events array
$uv->get("events")->add(array(
  "category": "checkout",
  "action": "step1"
));

// Add a line item object to the line_items array
$uv->get("basket")->get("line_items")->add(array(
  "product" => array(
    "id" => "12342332"
  ),
  "subtotal" => 10,
  "quantity" => 1
));
```

### toJSON

Outputs the UV object as json. Uses `json_encode`.

```php
print $uv->toJSON();
=> {"page":{"category":"Home","subcategory":"Mens - Shoes"},"user":{},"events":[]}
```

### toHTML

Outputs the UV object as json, declared as `window.universal_variable`, within `<script>` tags:

```php
print $uv->toHTML();
=>  <!-- Qubit Universal Variable data layer -->
    <script>
      window.universal_variable = {"page":{"category":"Home","subcategory":"Mens - Shoes"},"user":{},"events":[]};
    </script>
```


### Custom variables

If you'd like to add additional custom variables to the UV, you can do so by instantiating a new `UVObject` or `UVArray` class, and then run the methods as normal:

```php
// Object
$uv->my_custom = new UVObject();
$uv->get("my_custom")->set("id", "54321");

// Array
$uv->clicks = new UVArray();

// Add a string
$uv->add("click_button"); 

// Add an object
$uv->add(array(
  "category" => "signup",
  "action" => "button_click"
));
```



