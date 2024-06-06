<?php
// We know calls forecast
// We want to achieve service level
// How many agents we need?
// Let's make functions for 15 minutes interval
// Using ErlangA formulae
// It's ErlangC that works with abandons

$callsForecast15min = 147; // 47 calls per 15 minutes
$serviceLevelPercentsGoal = 80; // 80%
$serviceLevelTimeGoal = 20; // 20 seconds
// convert to minutes
$serviceLevelTimeGoalMinutes = $serviceLevelTimeGoal / 60;

// ErlangA formulae work with queue, so SL=80%/20seconds.

// We need to know more statistics
$averageHandlingTimeSeconds = 120; // average handling time in seconds
$averagePatience = 10; // average patience

// First step - to find agents without abandons
$ErlangA = new ErlangA($callsForecast15min, $averageHandlingTimeSeconds, $averagePatience);
$calculatedSL = 1;
$agents = ceil($ErlangA->a);
while ($calculatedSL < $serviceLevelPercentsGoal) {
    $agents++;
    $calculatedSL = $ErlangA->ServiceLevelPercents($agents, $serviceLevelTimeGoalMinutes);
}
// Second step - to find abandons
$calculatedPab = $ErlangA->ProbabilityOfAbandon($agents);
print_r('required agents=' . $agents . '.');
print_r("\n");
print_r('resulted probability of abandon=' . $calculatedPab*100 .'%'); // about 5% - very few
print_r("\n");
print_r("\n");
// Last step - to find agents with abandons
$callsForecast15min = $callsForecast15min*(1-$calculatedPab);
$ErlangA = new ErlangA($callsForecast15min, $averageHandlingTimeSeconds, $averagePatience);
$calculatedSL = 1;
$agents = ceil($ErlangA->a);
while ($calculatedSL < $serviceLevelPercentsGoal) {
    $agents++;
    $calculatedSL = $ErlangA->ServiceLevelPercents($agents, $serviceLevelTimeGoalMinutes);
}

print_r('required agents='.$agents.'.');
print_r("\n");
print_r('resulted service level='.$calculatedSL.'%');

class ErlangA {
    private const ACCURACYFORABANDONS = 0.00001;
    private $lambda; // calls per minute
    private $mu; // service rate
    private $O; // individual abandonment rate
    private $beta; // averageHandlingTime in minutes
    public $a; // load in erlangs
    public function __construct($callsForecast15min, $averageHandlingTimeSeconds, $averagePatience)
    {
        $this->lambda = $callsForecast15min / 15;
        $this->mu = 1 / ($averageHandlingTimeSeconds/60); // /60? in minutes?
        $this->O = 1 / $averagePatience;
        $this->beta = $averageHandlingTimeSeconds / 60;
        $this->a = $this->LoadC($this->lambda, $this->beta);
    }
    private function factorial(int $number){
        $result = 1;
        for($i = 1; $i <= $number; $i++) {
            $result *= $i;
        }
        return $result;
    }
    private function LoadC($calls, $averageHandlingTime) {
        $load = $calls * $averageHandlingTime;
        return $load;
    }
    // $s = number of agents
    private function SumForDP($s) {
        $sumForDP = 0;
        for ($j = 0; $j <= $s-1; $j++){
            $sumForDP += pow($this->a, $j) / $this->factorial($j);
        }
        return $sumForDP;
    }
    // $s = number of agents
    private function DelayProbability($s) {
        $delayProbability = (pow($this->a, $s) / ($this->factorial($s-1)*($s-$this->a))) *
            pow($this->SumForDP($s) + (pow($this->a, $s)/($this->factorial($s-1)*($s-$this->a))), -1);
        return $delayProbability;
    }
    // $s = number of agents
    // $t = service level goal time in seconds
    public function ServiceLevelPercents($s, $t) {
        $serviceLevelPercents = 1 - $this->DelayProbability($s) * exp(-1* ($s/$this->beta-$this->lambda) * $t);
        $serviceLevelPercents *= 100;
        return $serviceLevelPercents;
    }

    private function Load($n) {
        $load = $this->lambda / ($n * $this->mu);
        return $load;
    }
    private function A($x, $y) {
        $eternalSum = 0;
        $previousSum = -1;
        $j = 1;
        while ($eternalSum - $previousSum > self::ACCURACYFORABANDONS) {
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