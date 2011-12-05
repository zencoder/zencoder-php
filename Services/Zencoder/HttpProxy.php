<?php
/*

  Zencoder API PHP Library
  Version: 2.0
  See the README file for info on how to use this library.

*/

interface Services_Zencoder_HttpProxy
{
  function createData($key, $body = "");
  function retrieveData($key, array $params = array());
  function updateData($key, $body = "");
  function deleteData($key);
}
