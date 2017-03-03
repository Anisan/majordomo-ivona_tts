<?php
/**
* Ivona TTS 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 13:03:10 [Mar 13, 2016])
*/
//
//
class ivona_tts extends module {
/**
* ivona_tts
*
* Module class constructor
*
* @access private
*/
function ivona_tts() {
  $this->name="ivona_tts";
  $this->title="Ivona TTS";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 $this->getConfig();
 $out['ACCESS_KEY']=$this->config['ACCESS_KEY'];
 $out['SECRET_KEY']=$this->config['SECRET_KEY'];
 $out['VOICE']=$this->config['VOICE'];
 $out['SILENT']=$this->config['SILENT'];
 if ($this->view_mode=='update_settings') {
   global $access_key;
   $this->config['ACCESS_KEY']=$access_key;
   global $secret_key;
   $this->config['SECRET_KEY']=$secret_key;
   global $voice;
   $this->config['VOICE']=$voice;
   global $silent;
   $this->config['SILENT']=$silent;
   $this->saveConfig();
   $this->redirect("?");
 }
 
 global $clean;
 if ($clean) {
    array_map("unlink", glob(ROOT . "cached/voice/*_ivona.mp3"));
    $this->redirect("?");
 } 
}

/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
 function processSubscription($event, &$details) {
  $this->getConfig();
  if ($event=='SAY' && !$details['ignoreVoice']) {
    /* Хук на функцию say() */
    $level=$details['level'];
    $message=$details['message'];
    
    include_once("./modules/ivona_tts/IvonaTTS.php");
 
    $accessKey=$this->config['ACCESS_KEY'];
    $secretKey=$this->config['SECRET_KEY'];
    $voice=$this->config['VOICE'];

    $filename       = md5($message) . '_ivona.mp3';
    $cachedVoiceDir = ROOT . 'cached/voice';
    $cachedFileName = $cachedVoiceDir . '/' . $filename;

    if ($this->config['SILENT']==1 || $level >= (int)getGlobal('minMsgLevel'))
    {
        if (!file_exists($cachedFileName))
        {
            $obj = new IvonaTTS($accessKey,$secretKey,"ru-RU",$voice); 
            $obj->save_mp3($message,$cachedFileName);
        }
    }
    
    if ($level >= (int)getGlobal('minMsgLevel'))
    {
        
        @touch($cachedFileName);
        if (file_exists($cachedFileName))
        {
            playSound($cachedFileName, 1, $level);
            $details['ignoreVoice'] = 1;
        }
    }
  }
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  subscribeToEvent($this->name, 'SAY', '', 15);
  parent::install();
 }
 /**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  unsubscribeFromEvent($this->name, 'SAY');
  parent::uninstall();
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWFyIDEzLCAyMDE2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
