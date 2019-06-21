<?php
require_once('./PlayInterval.php');

class PlayLogParser {
   function __construct($log) {
        $this->log = $log;
        $this->intervals = array();
        $this->_rmGarbage();
        $this->_getContentId();
        $this->_getIntervals();
        unset($this->log);
    }
  function _rmGarbage() {
        $newArr = array();
        foreach ($this->log as $k => $v) {
            if ($v->type == 'stop') {
                $newArr[] = $v;
                continue;
            }
            if ($v->type != $this->log[$k + 1]->type) {
                $newArr[] = $v;
                continue;
            }
        }
        $this->log = $newArr;
    }
    function _getContentId() {
      $this->contentId = $this->log[0]->contentId;
    }
    function _getIntervals() {
        while(count($this->log) >= 2) {
        $this->_setInterval(array_shift($this->log), array_shift($this->log));
        }
    }
    function _setInterval($start,$stop) {
        $this->intervals[] = new PlayInterval($start, $stop);
    }
}

