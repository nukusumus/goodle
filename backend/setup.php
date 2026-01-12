<?php

require_once 'config.php';

try {

  $pdo->exec("CREATE DATABASE IF NOT EXISTS goodle");

  $pdo->exec("USE goodle");

  $pdo->exec("CREATE TABLE IF NOT EXISTS USERS (

    user_id VARCHAR(255) PRIMARY KEY

  )");

  $pdo->exec("CREATE TABLE IF NOT EXISTS polls (

    poll_id VARCHAR(255) PRIMARY KEY,

    name VARCHAR(255) NOT NULL,

    creating_user VARCHAR(255) NOT NULL,

    create_date DATETIME NOT NULL,

    FOREIGN KEY (creating_user) REFERENCES USERS(user_id)

  )");

  $pdo->exec("CREATE TABLE IF NOT EXISTS poll_participants (

    poll_id VARCHAR(255) NOT NULL,

    user_id VARCHAR(255) NOT NULL,

    user_color VARCHAR(7) NOT NULL,

    nickname VARCHAR(255) NOT NULL,

    PRIMARY KEY (poll_id, user_id),

    FOREIGN KEY (poll_id) REFERENCES polls(poll_id),

    FOREIGN KEY (user_id) REFERENCES USERS(user_id)

  )");

  $pdo->exec("CREATE TABLE IF NOT EXISTS votes (

   vote_id INT AUTO_INCREMENT PRIMARY KEY,

   poll_id VARCHAR(255) NOT NULL,

   voted_date DATE NOT NULL,

   user_id VARCHAR(255) NOT NULL,

   vote_type ENUM('YES', 'MAYBE') NOT NULL,

   UNIQUE KEY unique_vote (poll_id, user_id, voted_date),

   FOREIGN KEY (poll_id) REFERENCES polls(poll_id),

   FOREIGN KEY (user_id) REFERENCES USERS(user_id)

 )");

 try {
   $pdo->exec("ALTER TABLE votes ADD UNIQUE KEY unique_vote (poll_id, user_id, voted_date)");
 } catch (Exception $e) {
   // Ignore if already exists
 }

 echo "Database setup complete.";

} catch (Exception $e) {

  echo "Error: " . $e->getMessage();

}