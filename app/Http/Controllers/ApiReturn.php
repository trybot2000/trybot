<?php
namespace App\Http\Controllers;

class ApiReturn
{
  
  public $meta;
  public $result;
  public $data;
  public $error;
  public $headers;
  
  public function __construct() {
    $this->meta = null;
    $this->result = false;
    $this->data = null;
    $this->error = null;
    $this->headers = null;
  }

  public function setMeta($key, $value)
  {
    $this->meta[$key] = $value;
  }

  public function setResult($value = true)
  {
    $this->result = $value;
  }

  public function setData($value)
  {
    $this->data = $value;
  }

  public function setError($value)
  {
    $this->error = $value;
  }

  public function setHeaders($value)
  {
    $this->headers = $value;
  }

  public function addToMetaKey($key, $value = 1)
  {
    if (!isset($this->meta[$key])) {
      $this->setMeta($key, 0);
    }
    $this->meta[$key] += $value;
  }

  public function toArray()
  { 
    $r = array();
    foreach ($this as $k => $v) {
      $r[$k] = $v;
    }
    return $r;
  }

}
