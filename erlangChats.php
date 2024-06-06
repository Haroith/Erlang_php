<?php
// We know chat messages forecast
// We want to achieve service level
// How many agents we need?
// Let's make functions for 15 minutes interval
// Using ErlangChats formulae
// It's ErlangA that works with concurrency

$messagesForecast15min = 147; // 147 messages per 15 minutes
$serviceLevelPercentsGoal = 80; // 80%
$serviceLevelTimeGoal = 20; // 20 seconds
// convert to minutes
$serviceLevelTimeGoalMinutes = $serviceLevelTimeGoal / 60;

// ErlangA formulae work with queue, so SL=80%/20seconds.

// We need to know more statistics
$averageHandlingTimeSeconds = 480; // average handling time in seconds
$averagePatience = 20; // average patience

// We want agents to work with 3 chats
$concurrency = 3;




class ErlangChats {

}