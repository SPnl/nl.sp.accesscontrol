<?php

class CRM_Accesscontrol_Filesystem {

  public static function config(&$config) {
    if (CRM_Core_Permission::check('access to all files')) {
      return;
    }

    /*var_dump(CRM_Core_Permission::check('access to all files'));
    var_dump($config->imageUploadURL);
    echo "<hr>";
    var_dump($config->imageUploadDir); exit();*/

    $config->imageUploadURL = $config->imageUploadURL . 'Afdelingen/';
    $config->imageUploadDir = $config->imageUploadDir . 'Afdelingen/';

    if (!file_exists($config->imageUploadDir)) {
      mkdir($config->imageUploadDir);
    }
  }

}