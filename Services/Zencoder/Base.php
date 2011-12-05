<?php
/*

  Zencoder API PHP Library
  Version: 2.0
  See the README file for info on how to use this library.

*/

abstract class Services_Zencoder_Base
    implements Services_Zencoder_HttpProxy {

  protected $proxy;

  public function __construct(Services_Zencoder_HttpProxy $proxy)
  {
    $this->proxy = $proxy;
  }

  public function createData($path, $body = "")
  {
      return $this->proxy->createData($path, $params);
  }

  public function retrieveData($path, array $params = array())
  {
      return $this->proxy->retrieveData($path, $params);
  }

  public function updateData($path, $body = "")
  {
      return $this->proxy->updateData($path, $params);
  }

  public function deleteData($path)
  {
      return $this->proxy->deleteData($path, $params);
  }
}
