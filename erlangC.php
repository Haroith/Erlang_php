<?php
// We know calls forecast
// We want to achieve service level
// How many agents we need?
// Let's make functions for 15 minutes interval
// Using ErlangC formulae

$callsForecast15min = 47; // 47 calls per 15 minutes
$serviceLevelPercentsGoal = 80; // 80%
$serviceLevelTimeGoal = 20; // 20 seconds
// convert
$serviceLevelTimeGoalMinutes = $serviceLevelTimeGoal / 60;

// ErlangC formulae work with queue, so SL=80%/20seconds.

// We need to know more statistics
$averageHandlingTimeSeconds = 120; // average handling time in seconds

// Prepare the data
$ErlangC = new ErlangC($callsForecast15min, $averageHandlingTimeSeconds);

// Formula is too complex, we cannot revert it
// Now we have to 'guess' number of agents
// Minimum number of agents = load from ErlangC class
$calculatedSL = 1;
$agents = ceil($ErlangC->a);
while ($calculatedSL < $serviceLevelPercentsGoal) {
    $agents++;
    $calculatedSL = $ErlangC->ServiceLevelPercents($agents, $serviceLevelTimeGoalMinutes);
}
$calculatedASA = $ErlangC->AverageSpeedAnswerSeconds($agents);

print_r('required agents='.$agents.'.');
print_r("\n");
print_r('resulted service level='.$calculatedSL.'%');
print_r("\n");
print_r('resulted average speed of answer='.$calculatedASA.' minutes');

class ErlangC {

    private $lambda; // callsForecast per 1 minute
    private $beta; // averageHandlingTime in minutes
    public $a; // load in erlangs
    public function __construct($callsForecast15min, $averageHandlingTimeSeconds)
    {
        $this->lambda = $callsForecast15min / 15;
        $this->beta = $averageHandlingTimeSeconds / 60;
        $this->a = $this->Load($this->lambda, $this->beta);
    }

    private function factorial(int $number){
        $result = 1;
        for($i = 1; $i <= $number; $i++) {
            $result *= $i;
        }
        return $result;
    }

    private function Load($calls, $averageHandlingTime) {
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

    // $s = number of agents
    public function AverageSpeedAnswerSeconds($s) {
        $averageSpeedAnswerSeconds = ($this->DelayProbability($s) * $this->beta) / ($s - $this->a);
        return $averageSpeedAnswerSeconds;
    }
}