CREATE TABLE users (
    userId INT NOT NULL AUTO_INCREMENT,
    userFirstName VARCHAR(110) DEFAULT NULL,
    userLastName VARCHAR(110) DEFAULT NULL,
    userCIN VARCHAR(110) DEFAULT NULL,
    userEmail VARCHAR(110) DEFAULT NULL,
    Reference VARCHAR(110) DEFAULT NULL,
    PRIMARY KEY (userId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE timeslots (
    timeslot_id INT NOT NULL AUTO_INCREMENT,
    start_at TIME DEFAULT NULL,
    end_at TIME DEFAULT NULL,
    PRIMARY KEY (timeslot_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE appointements (
    userId_fk INT DEFAULT NULL,
    timeslot_id_fk INT DEFAULT NULL,
    user_subject VARCHAR(110) DEFAULT NULL,
    c_date DATE DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    appointement_id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (appointement_id),
    KEY userId_fk (userId_fk, timeslot_id_fk),
    KEY timeslot_id_fk (timeslot_id_fk),
    CONSTRAINT appointements_user_fk FOREIGN KEY (userId_fk) REFERENCES users (userId),
    CONSTRAINT appointements_timeslot_fk FOREIGN KEY (timeslot_id_fk) REFERENCES timeslots (timeslot_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
