<?php

require_once __DIR__ . '/AppointmentAvailability.php';

class AppointmentPolicy
{
    private $availability;

    public function __construct(AppointmentAvailability $availability)
    {
        $this->availability = $availability;
    }

    public function isDateFuture(string $date, DateTimeImmutable $today): bool
    {
        $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = DateTimeImmutable::getLastErrors();

        if (
            $parsedDate === false
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $parsedDate->format('Y-m-d') !== $date
        ) {
            return false;
        }

        return $date > $today->format('Y-m-d');
    }

    public function isSubjectValid(string $subject): bool
    {
        if (trim($subject) === '') {
            return false;
        }

        $characterCount = preg_match_all('/./us', $subject, $matches);

        return $characterCount !== false && $characterCount <= 110;
    }

    public function isTimeslotIdValid(int $timeslotId): bool
    {
        return $timeslotId >= 1 && $timeslotId <= 5;
    }

    public function canBook(
        bool $clientKnown,
        string $date,
        int $timeslotId,
        string $subject,
        DateTimeImmutable $today
    ): bool {
        if (
            !$clientKnown
            || !$this->isDateFuture($date, $today)
            || !$this->isTimeslotIdValid($timeslotId)
            || !$this->isSubjectValid($subject)
        ) {
            return false;
        }

        return $this->availability->isSlotAvailable($date, $timeslotId);
    }
}
