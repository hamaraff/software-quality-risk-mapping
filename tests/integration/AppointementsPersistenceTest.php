<?php

require_once __DIR__ . '/../../model/Appointements.php';

function assertIntegrationSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            $message . ' Expected ' . var_export($expected, true)
            . ', got ' . var_export($actual, true) . '.'
        );
    }
}

function seedAppointmentFixtures(PDO $db): void
{
    $db->exec(
        "INSERT INTO users (userId, userFirstName, userLastName, userEmail) "
        . "VALUES (10001, 'Test', 'Client', 'client-10001@example.test')"
    );
    $db->exec(
        "INSERT INTO timeslots (timeslot_id, start_at, end_at) "
        . "VALUES (10001, '10:00:00', '10:30:00'), (10002, '11:00:00', '11:30:00')"
    );
}

function insertAppointment(PDO $db, int $userId = 10001, int $timeslotId = 10001): int
{
    $appointment = new Appointements($db);
    $appointment->userId_fk = $userId;
    $appointment->timeslot_id_fk = $timeslotId;
    $appointment->user_subject = 'Consultation de test';
    $appointment->c_date = '2030-05-15';
    $appointment->createAnAppointement();

    return (int) $db->lastInsertId();
}

function appointementsPersistenceTests(): array
{
    return array(
        'create_insertsAppointment' => function (PDO $db): void {
            seedAppointmentFixtures($db);
            $appointmentId = insertAppointment($db);

            $stmt = $db->prepare(
                'SELECT userId_fk, timeslot_id_fk, user_subject, c_date '
                . 'FROM appointements WHERE appointement_id = :id'
            );
            $stmt->execute(array(':id' => $appointmentId));

            assertIntegrationSame(
                array(
                    'userId_fk' => 10001,
                    'timeslot_id_fk' => 10001,
                    'user_subject' => 'Consultation de test',
                    'c_date' => '2030-05-15',
                ),
                $stmt->fetch(PDO::FETCH_ASSOC),
                'The created appointment should be persisted.'
            );
        },
        'read_returnsRequestedAppointment' => function (PDO $db): void {
            seedAppointmentFixtures($db);
            $appointmentId = insertAppointment($db);
            $appointment = new Appointements($db);
            $appointment->appointement_id = $appointmentId;

            $row = $appointment->read()->fetch(PDO::FETCH_ASSOC);

            assertIntegrationSame($appointmentId, (int) $row['appointement_id'], 'Read should return the requested appointment.');
            assertIntegrationSame('Consultation de test', $row['user_subject'], 'Read should return the appointment subject.');
        },
        'update_changesPersistedAppointment' => function (PDO $db): void {
            seedAppointmentFixtures($db);
            $appointment = new Appointements($db);
            $appointment->appointement_id = insertAppointment($db);
            $appointment->timeslot_id_fk = 10002;
            $appointment->user_subject = 'Sujet modifié';
            $appointment->c_date = '2030-05-16';
            $appointment->UpdateAnAppointement();

            $row = $appointment->read()->fetch(PDO::FETCH_ASSOC);

            assertIntegrationSame(10002, (int) $row['timeslot_id_fk'], 'Update should persist the new timeslot.');
            assertIntegrationSame('Sujet modifié', $row['user_subject'], 'Update should persist the new subject.');
            assertIntegrationSame('2030-05-16', $row['c_date'], 'Update should persist the new date.');
        },
        'delete_removesPersistedAppointment' => function (PDO $db): void {
            seedAppointmentFixtures($db);
            $appointment = new Appointements($db);
            $appointment->appointement_id = insertAppointment($db);
            $appointment->deleteAnAppointement();

            assertIntegrationSame(
                0,
                (int) $db->query('SELECT COUNT(*) FROM appointements')->fetchColumn(),
                'Delete should remove the appointment.'
            );
        },
        'create_rejectsUnknownUserForeignKey' => function (PDO $db): void {
            seedAppointmentFixtures($db);

            try {
                insertAppointment($db, 99999, 10001);
            } catch (PDOException $error) {
                return;
            }

            throw new RuntimeException('The user foreign-key constraint should reject an unknown user.');
        },
        'create_rejectsUnknownTimeslotForeignKey' => function (PDO $db): void {
            seedAppointmentFixtures($db);

            try {
                insertAppointment($db, 10001, 99999);
            } catch (PDOException $error) {
                return;
            }

            throw new RuntimeException('The timeslot foreign-key constraint should reject an unknown timeslot.');
        },
    );
}
