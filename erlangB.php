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


class ErlangB {

    public function __construct()
    {

    }

    private function ProbabilityOfBlocking($erlangs, $agents) {
        $probabilityOfBlocking = (pow($erlangs, $agents) / gmp_fact($agents)) / (1);
        return $probabilityOfBlocking;
    }

    private function Erlangs($calls, $averageHandlingTime) {
        $erlangs = $calls * $averageHandlingTime;
        return $erlangs;
    }

    private function DenominatorForPb() {
        $denominator = 1;
        return $denominator;

    }
}
