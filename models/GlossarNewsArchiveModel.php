<?php

/**
 * Contao Open Source CMS
 */

/**
 * @file GlossarNewsArchiveModel.php
 * @class GlossarNewsArchiveModel
 * @author Sascha Weidner
 * @version 3.0.0
 * @package sioweb.contao.extensions.glossar
 * @copyright Sascha Weidner, Sioweb
 */


if(!class_exists('GlossarNewsArchiveModel')) {
class GlossarNewsArchiveModel extends NewsArchiveModel {
  
  public static function findByPidsAndInactiveGlossar($arrPid,$arrOptions = array()) {
    $t = static::$strTable;
    $arrColumns = array("$t.id IN('".implode("','",$arrPid)."') AND $t.glossar_disallow = 1");
    return static::findBy($arrColumns, array(), $arrOptions);
  }
  
}}