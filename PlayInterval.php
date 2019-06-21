
<?php

class PlayInterval {
    function __construct($start, $stop) {
        $this->start = $start->time;
        $this->stop = $stop->time;
    }
    function setRecords($records) {
        $this->records = [];
        foreach ($records as $record) {
            if ($record->time->startTime - 3000 < $this->start) {
                continue;
            }
            if ($record->time->startTime  - 3000 > $this->stop) {
                continue;
            }
            $record->time->adelay = $record->time->startTime - 3000 - $this->start;
            $this->records[] = $record;
        }
    }
}