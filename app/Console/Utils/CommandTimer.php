<?php

namespace App\Console\Utils;

use Illuminate\Console\OutputStyle;
use Laravel\Prompts\Output\ConsoleOutput;

class CommandTimer
{
    protected $output;
    protected $startTime;
    protected $taskName;

    public function __construct()
    {
        $this->output = new ConsoleOutput();
    }

    public function startTimer(string $taskName)
    {
        $this->taskName = $taskName;
        $this->startTime = microtime(true);
    }

    public function endTimer()
    {
        $duration = round((microtime(true) - $this->startTime) * 1000);
        $dots = max(1, 60 - strlen($this->taskName));
        $this->output->write('  ' . $this->taskName . ' ' . str_repeat('.', $dots) . " {$duration} ms ");
        $this->output->writeln('<info>DONE</info>');
    }
}
