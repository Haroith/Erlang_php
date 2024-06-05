<?php
// We know calls forecast
// We want to achieve service level
// How many agents we need?
// Let's make functions for 15 minutes interval
// Using ErlangA formulae
// It's ErlangC that works with abandons

$callsForecast15min = 47; // 47 calls per 15 minutes
$serviceLevelPercentsGoal = 80; // 80%
$serviceLevelTimeGoal = 20; // 20 seconds
// convert to minutes
$serviceLevelTimeGoalMinutes = $serviceLevelTimeGoal / 60;

// ErlangA formulae work with queue, so SL=80%/20seconds.

// We need to know more statistics
$averageHandlingTimeSeconds = 120; // average handling time in seconds
$averagePatience = 10; // average patience
$averagePatienceTime = exp($averagePatience);

$agents = 9;
$ErlangA = new ErlangA($callsForecast15min, $averageHandlingTimeSeconds, $averagePatience);
$calculatedPab = $ErlangA->ProbabilityOfAbandon($agents);

print_r('required agents=' . $agents . '.');
print_r("\n");
print_r('resulted probability of abandon=' . $calculatedPab*100 .'%'); // about 5% - very few

class ErlangA {
    private $lambda; // calls per minute
    private $mu; // service rate
    private $O; // individual abandonment rate
    public function __construct($callsForecast15min, $averageHandlingTimeSeconds, $averagePatience)
    {
        $this->lambda = $callsForecast15min / 15;
        $this->mu = 1 / ($averageHandlingTimeSeconds/60); // /60? in minutes?
        $this->O = 1 / $averagePatience;
    }
    private function Load($n) {
        $load = $this->lambda / ($n * $this->mu);
        return $load;
    }
    private function A($x, $y) {
        $accuracy = 0.00001;
        $eternalSum = 0;
        $previousSum = 1;
        $j = 1;
        while (abs($eternalSum - $previousSum) > $accuracy) {
            $previousSum = $eternalSum;
            $denominator = 1;
            for ($k=1; $k<=$j; $k++){
                $denominator *= $x + $k;
            }
            $eternalSum += pow($y, $j) / $denominator;
            $j++;
        }
        return 1 + $eternalSum;
    }
    public function ProbabilityOfAbandon($n) {
        $ro = $this->Load($n); // load per agent
        $probabilityOfAbandon = 1/($ro * $this->A($n*$this->mu/$this->O , $this->lambda/$this->O)) + 1 - 1/$ro;
        return $probabilityOfAbandon;
    }
}