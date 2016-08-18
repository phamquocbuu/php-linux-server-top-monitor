<?php
namespace wapmorgan\ServerStat;

use \COM;

class WindowsInformation {
    public $com;
    protected $localeCache;

    public function __construct() {
        if (extension_loaded('com') || extension_loaded('com_dotnet'))
            $this->com = new COM('winmgmts:{impersonationLevel=impersonate}');
        else
            $this->com = false;
    }

    public function processorsNumber() {
        if ($this->com)
        {
            $cpus = $this->com->execquery('SELECT NumberOfLogicalProcessors FROM Win32_Processor');
            foreach ($cpus as $cpu) return $cpu->NumberOfLogicalProcessors;
        }
        else
        {
            exec('wmic cpu get NumberOfLogicalProcessors', $p);
            return intval($p[1]);
        }
    }

    public function processorLoad() {
        if ($this->com)
        {
            // echo 'inner start: '.($st = microtime(true)).PHP_EOL;
            $cpus = $this->com->execquery('SELECT LoadPercentage FROM Win32_Processor');
            foreach ($cpus as $cpu) { $load = $cpu->LoadPercentage; break; }
            // echo 'inner end: '.(microtime(true)).PHP_EOL;
            return $load;
        }
        else
        {
            exec('wmic cpu get LoadPercentage', $p);
            array_shift($p);
            return array_sum($p);
        }
        // exec('typeperf -sc 1 "'.$_ENV['typeperfCounter'].'"', $p);
        // $line = explode(',', $p[2]);
        // $load = trim($line[1], '"');
        // return $load;
    }

    public function totalMemory() {
        if ($this->com)
        {
            $cs = $this->com->execquery('SELECT TotalPhysicalMemory FROM Win32_ComputerSystem');
            foreach ($cs as $cs) return $cs->TotalPhysicalMemory;
        }
        else
        {
            exec('wmic computersystem get TotalPhysicalMemory', $p);
            return floatval($p[1]);
        }
    }

    public function freeMemory() {
        if ($this->com)
        {
            $os = $this->com->execquery('SELECT FreeVirtualMemory FROM Win32_OperatingSystem');
            foreach ($os as $os) return $os->FreeVirtualMemory;
        }
        else
        {
            exec('wmic os get FreeVirtualMemory', $p);
            return floatval($p[1]);
        }
    }

    public function swapSize() {
        if ($this->com)
        {
            $os = $this->com->execquery('SELECT TotalSwapSpaceSize FROM Win32_OperatingSystem');
            foreach ($os as $os) return ($os->TotalSwapSpaceSize === NULL ? 0 : $os->TotalSwapSpaceSize);
        }
        else
        {
            exec('wmic os get TotalSwapSpaceSize', $p);
            array_shift($p);
            return array_sum($p);
        }
    }

    public function freeSwapSize() {
        if ($this->com)
        {
            $os = $this->com->execquery('SELECT FreeSpaceInPagingFiles FROM Win32_OperatingSystem');
            foreach ($os as $os) return $os->FreeSpaceInPagingFiles;
        }
        else
        {
            exec('wmic os get FreeSpaceInPagingFiles', $p);
            return floatval($p[1]);
        }
    }

    public function tasksNumber() {
        if ($this->com)
        {
            $p = $this->com->execquery('SELECT LastBootUpTime FROM Win32_Process');
            return count($p);
        }
        else
        {
            exec('tasklist /FO csv', $p);
            return count($p) - 2;
        }
    }

    public function runningTasksNumber() {
        if ($this->com)
        {
            $p = $this->com->execquery('SELECT LastBootUpTime FROM Win32_Process WHERE `status` = "RUNNING"');
            return count($p);
        }
        else
        {
            exec('tasklist /FI "STATUS eq RUNNING" /FO csv', $p);
            return count($p) - 2;
        }
    }

    public function uptime() {
        if ($this->com)
        {
            $os = $this->com->execquery('SELECT LastBootUpTime FROM Win32_OperatingSystem');
            foreach ($os as $os) { $v = $os->LastBootUpTime; break; };
            $time = mktime(substr($v, 8, 2), substr($v, 10, 2), substr($v, 12, 2), substr($v, 4, 2), substr($v, 6, 2), substr($v, 0, 4));
            return time() - $time;
        }
        else
        {
            exec('wmic os get LastBootUpTime', $p);
            $v = trim($p[1]);
            $time = mktime(substr($v, 8, 2), substr($v, 10, 2), substr($v, 12, 2), substr($v, 4, 2), substr($v, 6, 2), substr($v, 0, 4));
            return time() - $time;
        }
    }
}
