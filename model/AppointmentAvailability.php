<?php

interface AppointmentAvailability
{
    public function isSlotAvailable(string $date, int $timeslotId): bool;
}
