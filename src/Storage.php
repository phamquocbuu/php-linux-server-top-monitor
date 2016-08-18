<?php
namespace wapmorgan\ServerStat;

class Storage {
    public $type;

    public function __construct($type) {
        $this->type = $type;
    }

    public function save(array $info) {
        if ($this->type == 'file') {
            return file_put_contents($this->defaultFileName(), '<?php return '.var_export($info, true).';') > 0;
        }
    }

    public function retrieve() {
        if ($this->type == 'file') {
            return include($this->defaultFileName());
        }
    }

    public function defaultFileName() {
        return dirname(dirname(__FILE__)).'/serverstat.cache';
    }
}
