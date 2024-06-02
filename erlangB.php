<?php
// We know calls forecast
// We want to achieve service level
// How many agents we need?
// Let's make functions for 15 minutes interval
// Using ErlangB formulae

$callsForecast = 47; // 47 calls
$seviceLevelGoal = 80; // 80%

// ErlangB formulae work without queue, so SL=80, not 80/20.

// We need to know more statistics
$aht = 63; // average handling time in seconds
$aht = $aht/60; // convert to minutes

// Prepare the data
$ErlangB = new ErlangB($callsForecast, $aht);

// Now we have to 'guess' number of agents
$calculatedSL = 0;
$agents = 0;
while ($calculatedSL > (1-$seviceLevelGoal/100)) {
    $agents += 1;
    $calculatedSL = $ErlangB->ProbabilityOfBlocking($agents);
}
print_r($agents);
print_r('   ');
print_r($calculatedSL);

class ErlangB {

    private $erlangs;

    public function __construct($calls, $averageHandlingTime)
    {
        $this->erlangs = $this->Erlangs($calls, $averageHandlingTime);
    }

    private function factorial(int $number){
        $result = 1;

        for($i = 1; $i <= $number; $i++) {
            $result *= $i;
        }
        return $result;
    }

    private function Erlangs($calls, $averageHandlingTime) {
        $erlangs = $calls * $averageHandlingTime;
        return $erlangs;
    }

    private function DenominatorForPb($agents) {
        $denominator = 0;
        for ($i = 0; $i <= $agents; $i++){
            $denominator += pow($this->erlangs, $i) / $this->factorial($i);
        }
        return $denominator;
    }

    public function ProbabilityOfBlocking($agents) {
        $probabilityOfBlocking =
        (pow($this->erlangs, $agents) / $this->factorial($agents)) / $this->DenominatorForPb($agents);
        return $probabilityOfBlocking;
    }
}
