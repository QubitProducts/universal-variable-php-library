<?php

/**
 * UV_VERSION
 */
define("UV_VERSION", "1.2.1");

/**
 * UV_LIB_VERSION
 */
define("UV_LIB_VERSION", "1.0.1");

/**
 * Class UVArray
 */
class UVArray extends UVObject {

  /**
   * @param $object
   */
  public function add($object) {
    $count = count(get_object_vars($this));
    $this->$count = new UVObject($object);
  }

  /**
   * @param $key
   * @param $value
   */
  public function set($key, $value) {
    if (is_numeric($key)) {
      $this->$key = $value;
    }
  }

}

/**
 * Class UVObject
 */
class UVObject {

  /**
   * @param array $attributes
   */
  public function __construct($attributes = array()) {
    if (!empty($attributes)) {
      foreach ($attributes as $key => $value) {
        $this->set($key, $value);
      }
    }
    $this->convertNestedObjects();
  }

  /**
   * @param $key
   * @param $value
   */
  public function set($key, $value) {
    $this->$key = $value;
  }

  /**
   * @param $index
   * @return mixed
   */
  public function get($index) {
    return $this->$index;
  }

  /**
   * @return array|stdClass
   */
  public function toArray() {
    $obj = array();
    $vars = get_object_vars($this);
    foreach ($vars as $key => $value) {
      if (is_object($value) && method_exists($value, "toArray")) {
        $obj[$key] = $value->toArray();
      } else {
        $obj[$key] = $value;
      }
    }
    if (count($obj) == 0 && !($this instanceof UVArray)) {
      return new stdClass();
    }
    return $obj;
  }

  /**
   * @return bool
   */
  public function isEmpty() {
    $vars = get_object_vars($this);
    if (empty($vars)) {
      return true;
    } else if (count($vars) === 1 && isset($vars["line_items"]) && count(get_object_vars($vars["line_items"])) == 0) {
      return true;
    } else if (count($vars) === 1 && isset($vars["items"]) && count(get_object_vars($vars["items"])) == 0) {
      return true;
    } else if (count($vars) === 2 && isset($vars["linked_products"]) && count(get_object_vars($vars["linked_products"])) == 0 && isset($vars["reviews"]) && count(get_object_vars($vars["reviews"])) == 0) {
      return true;
    } else {
      return false;
    }
  }

  /**
   *
   */
  private function convertNestedObjects() {
    $vars = get_object_vars($this);
    foreach ($vars as $key => $value) {
      if (is_array($value) && $this->isAssociative($value)) {
        $this->$key = new UVObject($value);
      } else if (is_array($value)) {
        $this->$key = new UVArray($value);
      }
    }
  }

  /**
   * @param $arr
   * @return bool
   */
  private function isAssociative($arr) {
    return array_keys($arr) !== range(0, count($arr) - 1);
  }

}

/**
 * Class BuildUV
 */
class BuildUV extends UVObject {

  /**
   *
   */
  public function __construct() {
    $this->set("page", new UVObject());
    $this->set("user", new UVObject());
    $this->set("product", new UVObject());
    $this->get("product")->set("linked_products", new UVArray());
    $this->get("product")->set("reviews", new UVArray());
    $this->set("listing", new UVObject());
    $this->get("listing")->set("items", new UVArray());
    $this->set("transaction", new UVObject());
    $this->get("transaction")->set("line_items", new UVArray());
    $this->set("basket", new UVObject());
    $this->get("basket")->set("line_items", new UVArray());
    $this->set("events", new UVArray());
    $this->set("recommendation", new UVArray());
  }

  /**
   * @return string
   */
  public function toJSON() {
    $uvOutput = array();

    $uvOutput["page"] = $this->page->toArray();
    $uvOutput["user"] = $this->user->toArray();
    $uvOutput["events"] = $this->events->toArray();
    $uvOutput["version"] = UV_VERSION;
    $uvOutput["php_lib_version"] = UV_LIB_VERSION;

    $vars = get_object_vars($this);
    foreach ($vars as $key => $value) {
      if (isset($uvOutput[$key])) {
        continue;
      }
      if (method_exists($value, "isEmpty") && !$value->isEmpty()) {
        if (method_exists($value, "toArray")) {
          $uvOutput[$key] = $value->toArray();
        } else {
          $uvOutput[$key] = $value;
        }
      }
    }
    return json_encode($uvOutput);
  }

  /**
   * @return string
   */
  public function toHTML() {
    return "\n
        <!-- Qubit Universal Variable data layer v" . UV_VERSION . " - PHP Lib v" . UV_LIB_VERSION . " -->
        <script>
            window.universal_variable = " . $this->toJSON() . ";
        </script>
        <!-- End UV -->
        \n";
  }

}

