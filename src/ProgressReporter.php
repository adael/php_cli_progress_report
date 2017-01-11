<?php

namespace Adael\ProgressReporter;

/**
 * Shows a progress bar
 *
 * Usage:
 * <code>
 *     use Adael\ProgressReporter\ProgressReporter;
 *
 *     $items = range(1, 100);
 *     $report = new ProgressReporter(count($items), "Doing something");
 *     $report->interval(1); // reports each iteration
 *     foreach($items as $item){
 *         $report->report();
 *         usleep(10000);
 *     }
 * </code>
 */
class ProgressReporter
{

    /**
     * Start time
     * @var int
     */
    private $start;

    /**
     * Start time for calculate ops
     * @var int
     */
    private $ops_start;

    /**
     * Current operation within ops threshold
     * @var int
     */
    private $ops_current = 0;

    /**
     * Calculate ops within N seconds
     * @var integer in seconds
     */
    private $ops_threshold = 30;

    /**
     * Elapsed time
     * @var integer
     */
    private $elapsed = 0;

    /**
     * Total amount of operations
     * @var integer
     */
    private $total = 0;

    /**
     * Current iteration
     * @var integer
     */
    private $current = 0;

    /**
     * Brief description of operation
     * @var string
     */
    private $desc;

    /**
     * Progress bar width
     * @var integer
     */
    private $width = 20;

    /**
     * Operations per second
     * @var integer
     */
    private $ops = 0;

    /**
     * Sets the update interval
     * @var int
     */
    private $interval = 1;

    /**
     * Character for carriage return
     * @var string
     */
    private $cr_code = "\r";

    /**
     * Escape sequence for clearing the current line
     * @var string
     */
    private $cl_code = "\033[K";

    /**
     * Output indentation
     * @var integer
     */
    private $indent = 4;

    private $only_cli = true;

    /**
     * Initializes the reporter with the total number of operations
     * and an optional description about the task.
     *
     * @param int $total total number of operations
     * @param string $desc brief description of the task
     */
    public function __construct($total, $desc = "")
    {
        $this->start = microtime(true);
        $this->ops_start = microtime(true);
        $this->total = $total;
        $this->desc = $desc;
        $this->current = 0;
    }

    /**
     * Sets the update interval
     *
     * @param int $interval >= 1
     */
    public function interval($interval)
    {
        $this->interval = $interval;
    }

    /**
     * Updates and render the progress bar
     * @param string $desc updates the description of the task
     */
    public function report($desc = null)
    {
        if ($this->only_cli && PHP_SAPI !== 'cli') {
            return;
        }

        if ($desc) {
            $this->desc = $desc;
        }

        $this->current++;
        $this->ops_current++;

        if ($this->current % $this->interval === 0) {
            $this->update();
            $this->render();
        }
    }

    /**
     * After the last progress report you must remember to print the "\n",
     * so this method do it for you.
     *
     * Ah, also renders the progress bar for the last time
     */
    public function finish()
    {
        if ($this->only_cli && PHP_SAPI !== 'cli') {
            return;
        }

        $this->update();
        $this->render();
        echo PHP_EOL;
    }

    /**
     * Determines if the reporter only works on cli.
     * True by default.
     *
     * @param boolean $value
     */
    public function onlyCli($value = true)
    {
        $this->only_cli = $value;
    }

    private function update()
    {
        $this->elapsed = (microtime(true) - $this->start);
        $this->ops_elapsed = (microtime(true) - $this->ops_start);

        if ($this->microToSeconds($this->ops_elapsed) > $this->ops_threshold) {
            $this->ops_start = microtime(true);
            $this->ops_current = 0;
        }

        if ($this->ops_elapsed > 0) {
            $this->ops = round($this->ops_current / $this->ops_elapsed, 3);
        }
    }

    private function render()
    {
        $pb = $this->getProgress();

        echo $this->cl_code;
        echo str_repeat(" ", $this->indent);
        echo $pb;

        if ($this->desc) {
            echo " - " . $this->desc;
        }

        echo $this->cr_code;
    }

    private function getProgress()
    {
        if ($this->total > 0) {
            $percent_done = round($this->current * 100 / $this->total, 3);
        } else {
            $percent_done = 0;
        }

        $done_chars = min(100, floor($percent_done * $this->width / 100));
        $undone_chars = max(0, $this->width - $done_chars);

        $bar = str_repeat("#", $done_chars) . str_repeat(".", $undone_chars);

        $pb = sprintf("[%s] %s/%s@%s", $bar, $this->current, $this->total,
            $this->ops);

        if ($this->ops > 0) {
            $pb .= $this->getEstimatedTime();
        }

        return $pb;
    }

    private function getEstimatedTime()
    {
        return " ETA: " . gmdate("H:i:s", ceil(($this->total - $this->current) / $this->ops));
    }

    private function microToSeconds($microseconds)
    {
        return $microseconds / 1000000;
    }

}
