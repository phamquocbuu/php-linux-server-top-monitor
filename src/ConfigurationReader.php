<?php
namespace wapmorgan\ServerStat;

class ConfigurationReader {
    public $config;

    public function __construct() {
        $this->config = parse_ini_file(dirname(dirname(__FILE__)).'/serverstat.conf');
    }
}
