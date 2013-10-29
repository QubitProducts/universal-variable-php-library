<?php

  define("UV_VERSION", "1.1.1");
  define("UV_LIB_VERSION", "1.0.0");

  class UVArray extends UVObject {

    public function add($object) {
      $count = count(get_object_vars($this));
      $this->$count = new UVObject($object);
    }

    public function set($key, $value) {
      if (is_numeric($key)) {
        $this->$key = $value;
      }
    }

  }

  class UVObject {

    public function __construct($attributes=array()) {
      if (!empty($attributes)) {
        foreach ($attributes as $key => $value) {
          $this->set($key, $value);
        }
      }
      $this->convertNestedObjects();
    }

    public function set($key, $value) {
      $this->$key = $value;
    }

    public function get($index) {
      return $this->$index;
    }

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

    public function isEmpty() {
      $vars = get_object_vars($this);
      if (empty($vars)) {
        return true;
      } else if (count($vars) === 1 && isset($vars["line_items"]) && count(get_object_vars($vars["line_items"])) == 0) {
        return true;
      } else if (count($vars) === 1 && isset($vars["items"]) && count(get_object_vars($vars["items"])) == 0) {
        return true;
      } else {
        return false;
      }
    }

    private function convertNestedObjects() {
      $vars = get_object_vars($this);
      foreach($vars as $key => $value) {
        if (is_array($value) && $this->isAssociative($value)) {
          $this->$key = new UVObject($value);
        } else if (is_array($value)) {
          $this->$key = new UVArray($value);
        }
      }
    }

    private function isAssociative($arr) {
      return array_keys($arr) !== range(0, count($arr) - 1);
    }

  }


  class BuildUV extends UVObject {

    public function __construct() {
      $this->set("page", new UVObject());
      $this->set("user", new UVObject());
      $this->set("product", new UVObject());
      $this->set("listing", new UVObject());
      $this->get("listing")->set("items", new UVArray());
      $this->set("transaction", new UVObject());
      $this->get("transaction")->set("line_items", new UVArray());
      $this->set("basket", new UVObject());
      $this->get("basket")->set("line_items", new UVArray());
      $this->set("events", new UVArray());
    }

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

    public function toHTML() {
      return "\n
      <!-- Qubit Universal Variable data layer v" + UV_VERSION + " - PHP Lib v" + UV_LIB_VERSION + " -->
      <script>
        window.universal_variable = " . $this->toJSON() . ";
      </script>
      <!-- End UV -->
      \n";
    }

  }

?>
