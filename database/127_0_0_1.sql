CREATE DATABASE IF NOT EXISTS `codenest`;
USE `codenest`;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `level` int(11) NOT NULL DEFAULT 1,
  `exp` int(11) NOT NULL DEFAULT 0,
  `coins` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
);

INSERT INTO `users` (`username`, `email`, `password`, `level`, `exp`, `coins`)
VALUES ('123', 'elijah@gmail.com', '$2y$10$84uegFmUCirOQwxHWJPj8Ofj9S473XzfhLVPPy3tAbkRG6uD1UzSS', 1, 0, 0);