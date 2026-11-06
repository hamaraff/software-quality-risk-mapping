<?php

require_once __DIR__ . '/../../model/AppointmentPolicy.php';

class StubAppointmentAvailability implements AppointmentAvailability
{
    public $available;
    public $calls = array();

    public function __construct(bool $available)
    {
        $this->available = $available;
    }

    public function isSlotAvailable(string $date, int $timeslotId): bool
    {
        $this->calls[] = array($date, $timeslotId);

        return $this->available;
    }
}

function assertSameValue($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            $message . ' Expected ' . var_export($expected, true)
            . ', got ' . var_export($actual, true) . '.'
        );
    }
}

function appointmentPolicyTests(): array
{
    $today = new DateTimeImmutable('2026-11-06');

    return array(
        'isDateFuture_acceptsTomorrow' => function () use ($today): void {
            // Arrange
            $policy = new AppointmentPolicy(new StubAppointmentAvailability(true));

            // Act
            $result = $policy->isDateFuture('2026-11-07', $today);

            // Assert
            assertSameValue(true, $result, 'Tomorrow should be a future date.');
        },
        'isDateFuture_rejectsTodayAndPastDates' => function () use ($today): void {
            // Arrange
            $policy = new AppointmentPolicy(new StubAppointmentAvailability(true));

            // Act
            $todayResult = $policy->isDateFuture('2026-11-06', $today);
            $pastResult = $policy->isDateFuture('2026-11-05', $today);

            // Assert
            assertSameValue(false, $todayResult, 'Today should not be a future date.');
            assertSameValue(false, $pastResult, 'A past date should be rejected.');
        },
        'isDateFuture_rejectsMalformedCalendarDate' => function () use ($today): void {
            // Arrange
            $policy = new AppointmentPolicy(new StubAppointmentAvailability(true));

            // Act
            $result = $policy->isDateFuture('2026-02-30', $today);

            // Assert
            assertSameValue(false, $result, 'An impossible calendar date should be rejected.');
        },
        'isSubjectValid_acceptsOneAnd110Characters' => function (): void {
            // Arrange
            $policy = new AppointmentPolicy(new StubAppointmentAvailability(true));

            // Act
            $minimumResult = $policy->isSubjectValid('x');
            $maximumResult = $policy->isSubjectValid(str_repeat('x', 110));

            // Assert
            assertSameValue(true, $minimumResult, 'A one-character subject should be accepted.');
            assertSameValue(true, $maximumResult, 'A 110-character subject should be accepted.');
        },
        'isSubjectValid_rejectsBlankAndTooLongValues' => function (): void {
            // Arrange
            $policy = new AppointmentPolicy(new StubAppointmentAvailability(true));

            // Act
            $blankResult = $policy->isSubjectValid(" \t ");
            $tooLongResult = $policy->isSubjectValid(str_repeat('x', 111));

            // Assert
            assertSameValue(false, $blankResult, 'A blank subject should be rejected.');
            assertSameValue(false, $tooLongResult, 'A subject over 110 characters should be rejected.');
        },
        'isTimeslotIdValid_acceptsOnlyConfiguredIds' => function (): void {
            // Arrange
            $policy = new AppointmentPolicy(new StubAppointmentAvailability(true));

            // Act
            $validResult = $policy->isTimeslotIdValid(5);
            $invalidLowResult = $policy->isTimeslotIdValid(0);
            $invalidHighResult = $policy->isTimeslotIdValid(6);

            // Assert
            assertSameValue(true, $validResult, 'Slot 5 should be valid.');
            assertSameValue(false, $invalidLowResult, 'Slot 0 should be invalid.');
            assertSameValue(false, $invalidHighResult, 'Slot 6 should be invalid.');
        },
        'canBook_returnsTrueAndChecksAvailabilityStub' => function () use ($today): void {
            // Arrange
            $availability = new StubAppointmentAvailability(true);
            $policy = new AppointmentPolicy($availability);

            // Act
            $result = $policy->canBook(true, '2026-11-07', 2, 'Consultation', $today);

            // Assert
            assertSameValue(true, $result, 'A valid booking with an available slot should be accepted.');
            assertSameValue(
                array(array('2026-11-07', 2)),
                $availability->calls,
                'Availability should be checked once with the selected date and slot.'
            );
        },
        'canBook_rejectsOccupiedSlotFromAvailabilityStub' => function () use ($today): void {
            // Arrange
            $availability = new StubAppointmentAvailability(false);
            $policy = new AppointmentPolicy($availability);

            // Act
            $result = $policy->canBook(true, '2026-11-07', 2, 'Consultation', $today);

            // Assert
            assertSameValue(false, $result, 'A booking for an occupied slot should be rejected.');
            assertSameValue(
                array(array('2026-11-07', 2)),
                $availability->calls,
                'Availability should be checked for an otherwise valid booking.'
            );
        },
        'canBook_rejectsUnknownClientWithoutCallingAvailabilityStub' => function () use ($today): void {
            // Arrange
            $availability = new StubAppointmentAvailability(true);
            $policy = new AppointmentPolicy($availability);

            // Act
            $result = $policy->canBook(false, '2026-11-07', 2, 'Consultation', $today);

            // Assert
            assertSameValue(false, $result, 'An unknown client should not be allowed to book.');
            assertSameValue(
                array(),
                $availability->calls,
                'Availability must not be checked when the client is unknown.'
            );
        },
        'canBook_rejectsInvalidInputWithoutCallingAvailabilityStub' => function () use ($today): void {
            // Arrange
            $availability = new StubAppointmentAvailability(true);
            $policy = new AppointmentPolicy($availability);

            // Act
            $result = $policy->canBook(true, '2026-11-06', 6, '   ', $today);

            // Assert
            assertSameValue(false, $result, 'Invalid booking data should be rejected.');
            assertSameValue(
                array(),
                $availability->calls,
                'Availability must not be checked when input validation fails.'
            );
        },
    );
}
