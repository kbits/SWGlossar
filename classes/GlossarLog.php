<?php

/**
 * Contao Open Source CMS
 */

namespace sioweb\contao\extensions\glossar;
use Contao;

/**
 * @file GlossarLog.php
 * @class GlossarLog
 * @author Sascha Weidner
 * @version 3.0.0
 * @package sioweb.contao.extensions.glossar
 * @copyright Sascha Weidner, Sioweb
 */


class GlossarLog extends \BackendModule {

  protected $strTemplate = 'be_glossar_log';

  public function generate() {
    return parent::generate();
  }

  public function compile() {

    $db = &$this->Database;
    $ext = $db->prepare("select * from `tl_repository_installs` where `extension`='SWGlossar'")->execute();

    if($ext->lickey == false || $ext->lickey == 'free2use') {
      $this->Template->lickey = false;
    } else $this->Template->lickey = true;    $Log = \GlossarLogModel::findBy(array("tl_glossar_log.tstamp >= ?"),array(time()-(86400*7*31)),array('order'=>'tstamp DESC','limit'=>500));
    $arrTerms = $arrLog = array();
    if(!empty($Log)) {
      while($Log->next()) {
        $_term = $Log->getRelated('term');
        $arrLog[$Log->action][$Log->term][] = $Log->user;
        $_log = $Log->row();
        $_log['term'] = $_term->row();
        $arrTerms[$_term->id][] = $_log;
      }
    }
    $this->Template->terms = $arrTerms;
    $arrTerms = null;

    $stdArray = array(
      0 => array(
        'avg' => 0,
        'sum' => 0,
        'unique' => 0,
        'user' => array(),
        'user_percent' => array(),
      )
    );
    $arrStats = array(
      'load' => $stdArray,
      'follow' => $stdArray,
      'close' => $stdArray,
      'cloud' => $stdArray,
      'span' => $stdArray,
    );
    $stdArray = null;
    foreach($arrLog as $type => $terms) {
      foreach($terms as $id => $users) {
        $arrStats[$type][$id]['unique'] = count(array_unique($users));
        foreach($users as $key => $sid) {
          if(empty($arrStats[$type][$id]['user'][$sid]))
            $arrStats[$type][$id]['user'][$sid] = 0;
          $arrStats[$type][$id]['user'][$sid]++;

          if(empty($arrStats[$type][0]['user'][$sid]))
            $arrStats[$type][0]['user'][$sid] = 0;
          $arrStats[$type][0]['user'][$sid]++;
        }

        $arrStats[$type][$id]['sum'] = array_sum($arrStats[$type][$id]['user']);
        $arrStats[$type][0]['sum'] += $arrStats[$type][$id]['sum'];

        foreach($arrStats[$type][$id]['user'] as $sid => $count)
          $arrStats[$type][$id]['user_percent'][$sid] = number_format($count * 100 / $arrStats[$type][$id]['sum'],2);
        $arrStats[$type][$id]['avg'] = $arrStats[$type][$id]['sum'] / count($arrStats[$type][$id]['user']);
        ksort($arrStats[$type][$id]);
      }
      $arrStats[$type][0]['avg'] = $arrStats[$type][0]['sum'] / count($arrStats[$type][0]['user']);
      foreach($arrStats[$type][0]['user'] as $sid => $count)
        $arrStats[$type][0]['user_percent'][$sid] = number_format($count * 100 / $arrStats[$type][0]['sum'],2);
      $arrStats[$type][0]['unique'] = count($arrStats[$type][0]['user']);
      ksort($arrStats[$type][0]);
      ksort($arrStats[$type]);
    }

    $this->Template->stats = $arrStats;
    $this->Template->log = $arrLog;
  }

}