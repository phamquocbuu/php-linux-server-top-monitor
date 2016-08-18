<?php
namespace wapmorgan\ServerStat;

class InformationCollector {
    protected $windows = false;
    protected $linux = false;
    protected $memoryTotalCache = false;

    public function __construct() {
        if (strncasecmp(PHP_OS, 'win', 3) === 0) {
            $this->windows = new WindowsInformation();
        } else {
            $this->linux = true;
        }
    }

    public function collect() {
        /* get core information (snapshot) */
        $stat1 = $this->GetCoreInformation();
        /* sleep on server for one second */
        sleep(1);
        /* take second snapshot */
        $stat2 = $this->GetCoreInformation();
        /* get the cpu percentage based off two snapshots */
        $data = $this->GetCpuPercentages($stat1, $stat2);
        $total = 0;
        $count = 0;
        foreach ($data as $key => $value) {
            $total += $value['user'] + $value['nice'] + $value['sys'];
            $count++;
        }

        $information = array
        (
            'processors' => $this->collectProccessorsNumber(),
            'processor_load' => $this->collectProcessorLoad(),
            'cpu' => round($total / ($count ?: 1)),
            'memory' => array('total' => $this->collectMemoryTotal(), 'free' => $this->collectMemoryFree()),
            'swap' => array('total' => $this->collectSwapTotal(), 'free' => $this->collectSwapFree()),
            'tasks' => $this->collectTasksNumber(),
            'uptime' => $this->collectUptime(),
        );
        $information['memory']['busy'] = $information['memory']['total'] - $information['memory']['free'];
        $information['swap']['busy'] = $information['swap']['total'] - $information['swap']['free'];
        return $information;
    }

    protected function collectProccessorsNumber() {
        if ($this->windows)
        {
            echo microtime(true).PHP_EOL;
            return $this->windows->processorsNumber();
        }
        else
        {
            echo microtime(true).PHP_EOL;
            return substr_count(file_get_contents('/proc/cpuinfo'), 'processor');
        }
    }

    protected function collectProcessorLoad() {
        if ($this->windows)
        {
            echo ($pl = microtime(true)).PHP_EOL;
            $load = $this->windows->processorLoad();
            //echo 'processor load: '.(microtime(true) - $pl).PHP_EOL;
            return $load;
        }
        else
        {
            echo microtime(true).PHP_EOL;
            exec('top -bn1 | head -n3', $p);
            $line = $p[2];
            preg_match('~([0-9.,]+) id~', $line, $load);
            return 100 - $load[1];
        }
    }

    protected function collectMemoryTotal() {
        if ($this->windows)
        {
            echo microtime(true).PHP_EOL;
            if ($this->memoryTotalCache === false)
                return ($this->memoryTotalCache = $this->windows->totalMemory());
            else
                return $this->memoryTotalCache;
        }
        else
        {
            echo microtime(true).PHP_EOL;
            $meminfo = file('/proc/meminfo');
            $line = explode(' ', $meminfo[0]);
            return $line[count($line) - 2];
        }
    }

    protected function collectMemoryFree() {
        if ($this->windows)
        {
            echo microtime(true).PHP_EOL;
            return $this->windows->freeMemory();
        }
        else
        {
            echo microtime(true).PHP_EOL;
            $meminfo = file('/proc/meminfo');
            $line = explode(' ', $meminfo[1]);
            return $line[count($line) - 2];

        }
    }

    protected function collectSwapTotal() {
        if ($this->windows)
        {
            echo microtime(true).PHP_EOL;
            return $this->windows->swapSize();
        }
        else
        {
            echo microtime(true).PHP_EOL;
            $meminfo = file('/proc/meminfo');
            foreach ($meminfo as $l)
            {
                $line = explode(' ', $l);
                if ($line[0] == 'SwapTotal:')
                    return $line[count($line) - 2];
            }
        }
    }

    protected function collectSwapFree() {
        if ($this->windows)
        {
            echo microtime(true).PHP_EOL;
            return $this->windows->freeSwapSize();
        }
        else
        {
            echo microtime(true).PHP_EOL;
            $meminfo = file('/proc/meminfo');
            foreach ($meminfo as $l)
            {
                $line = explode(' ', $l);
                if ($line[0] == 'SwapFree:')
                    return $line[count($line) - 2];
            }

        }
    }

    protected function collectTasksNumber() {
        if ($this->windows)
        {
            echo microtime(true).PHP_EOL;
            return $this->windows->tasksNumber();
        }
        else
        {
            echo microtime(true).PHP_EOL;
            exec('ps -aux', $p);
            return count($p) - 1;
        }
    }

    protected function collectTasksRunningNumber() {
        if ($this->windows)
        {
            echo microtime(true).PHP_EOL;
            return $this->windows->runningTasksNumber();
        }
        else
        {
            echo microtime(true).PHP_EOL;
            exec('ps -auxr', $p);
            return count($p) - 1;
        }

    }

    protected function collectUptime() {
        if ($this->windows)
        {
            echo microtime(true).PHP_EOL;
            return $this->windows->uptime();
        }
        else
        {
            echo microtime(true).PHP_EOL;
            exec('uptime --since', $o);
            $time = strtotime($o[0]);
            return time() - $time;
        }
    }

    /* Gets individual core information */
    protected function GetCoreInformation() {
        $data = file('/proc/stat');
        $cores = array();
        foreach( $data as $line ) {
            if( preg_match('/^cpu[0-9]/', $line) )
            {
                $info = explode(' ', $line );
                $cores[] = array(
                    'user' => $info[1],
                    'nice' => $info[2],
                    'sys' => $info[3],
                    'idle' => $info[4]
                );
            }
        }
        return $cores;
    }
    /* compares two information snapshots and returns the cpu percentage */
    protected function GetCpuPercentages($stat1, $stat2) {
        if( count($stat1) !== count($stat2) ) {
            return;
        }
        $cpus = array();
        for( $i = 0, $l = count($stat1); $i < $l; $i++) {
            $dif = array();
            $dif['user'] = $stat2[$i]['user'] - $stat1[$i]['user'];
            $dif['nice'] = $stat2[$i]['nice'] - $stat1[$i]['nice'];
            $dif['sys'] = $stat2[$i]['sys'] - $stat1[$i]['sys'];
            $dif['idle'] = $stat2[$i]['idle'] - $stat1[$i]['idle'];
            $total = array_sum($dif);
            $cpu = array();
            foreach($dif as $x=>$y) $cpu[$x] = round($y / $total * 100, 1);
            $cpus['cpu' . $i] = $cpu;
        }
        return $cpus;
    }
    
}
