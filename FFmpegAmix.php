<?php 

class FFmpegAmix {
    
       function __construct($cmd, $outFile, $records) {
        $this->cmd = $cmd;
        $this->outFile = $outFile;
        $this->records = $records;

        $this->inputs = array();
        $this->adelays = array();
        $this->volume = array();
        $this->amixInput = array();

        $this->ffmpegString = "";

        $this->parse();
        $this->createFFmpegString();
    }
    function parse() {
        foreach ($this->records as $k=>$v) {
            $this->inputs[] = '-i '.$v->file->url;
                        try {
            $this->adelays[] = "[$k]adelay=".$v->time->adelay."|".$v->time->adelay."[a$k] ;";
            } catch (Exception $e) {}
            $this->volume[] = "[a$k]volume=1[aud$k] ;";
            $this->amixInput[] = "[aud$k]";
        }
        unset($this->adelays[0]);
        $this->volume[0] = "[0]volume=0.1[aud0] ;";
    }
    function createFFmpegString() {
        $this->ffmpegString = $this->cmd." ".
        implode(" ",$this->inputs)."  -filter_complex  \" ".
        implode(" ",$this->adelays)." ".
        implode(" ",$this->volume). " ". 
        implode("",$this->amixInput)."amix=".count($this->amixInput).
        ":dropout_transition=0,dynaudnorm\""." $this->outFile -y ";
    }
    function exec() {
      exec($this->ffmpegString);
    }
}