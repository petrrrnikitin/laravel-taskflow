<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('tasks:notify-overdue')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('reports:cleanup')
    ->weeklyOn(1, '03:00')
    ->withoutOverlapping()
    ->onOneServer();