-- Database: progetto_babylon_vissicchio

-- DROP TABLES IF EXISTS (per sicurezza, se vuoi fare reset completo)
DROP TABLE IF EXISTS `chat`, `sceglie`, `visita`, `paziente`, `medico`, `chatbot`, `utente`;

-- CREAZIONE TABELLE

CREATE TABLE `chatbot` (
  `id_chatbot` INT(11) NOT NULL AUTO_INCREMENT,
  `nome_bot` VARCHAR(100) DEFAULT NULL,
  `sintomi_riportati` TEXT DEFAULT NULL,
  `specializzazione_dedotta` VARCHAR(100) DEFAULT NULL, -- ✅ NUOVA COLONNA
  PRIMARY KEY (`id_chatbot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `utente` (
  `id_utente` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) DEFAULT NULL,
  `cognome` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `password` VARCHAR(255) DEFAULT NULL,
  `tipo_utente` ENUM('Medico','Paziente','Admin') NOT NULL,
  PRIMARY KEY (`id_utente`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `medico` (
  `id_medico` INT(11) NOT NULL,
  `specializzazione` VARCHAR(100) NOT NULL DEFAULT 'Medicina generale',
  `rating` DOUBLE NOT NULL DEFAULT 0,
  `disponibilita` BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id_medico`),
  CONSTRAINT `medico_ibfk_1` FOREIGN KEY (`id_medico`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `paziente` (
  `id_paziente` INT(11) NOT NULL,
  `data_nascita` DATE DEFAULT NULL,
  `sesso` VARCHAR(100) DEFAULT NULL,
  `statura_cm` INT DEFAULT NULL,             -- ✅ NUOVA COLONNA
  `peso_kg` DECIMAL(5,2) DEFAULT NULL,       -- ✅ NUOVA COLONNA
  PRIMARY KEY (`id_paziente`),
  CONSTRAINT `paziente_ibfk_1` FOREIGN KEY (`id_paziente`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `visita` (
  `id_visita` INT(11) NOT NULL AUTO_INCREMENT,
  `id_paziente` INT(11) NOT NULL,
  `id_medico` INT(11) NOT NULL,
  `id_chatbot` INT(11) DEFAULT NULL,
  `data_visita` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `esito_visita` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_visita`),
  KEY `id_medico` (`id_medico`),
  KEY `id_chatbot` (`id_chatbot`),
  CONSTRAINT `visita_ibfk_1` FOREIGN KEY (`id_paziente`) REFERENCES `paziente` (`id_paziente`) ON DELETE CASCADE,
  CONSTRAINT `visita_ibfk_2` FOREIGN KEY (`id_medico`) REFERENCES `medico` (`id_medico`) ON DELETE CASCADE,
  CONSTRAINT `visita_ibfk_3` FOREIGN KEY (`id_chatbot`) REFERENCES `chatbot` (`id_chatbot`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `sceglie` (
  `id_chatbot` INT(11) NOT NULL,
  `id_medico` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id_chatbot`),
  KEY `id_medico` (`id_medico`),
  CONSTRAINT `sceglie_ibfk_1` FOREIGN KEY (`id_chatbot`) REFERENCES `chatbot` (`id_chatbot`) ON DELETE CASCADE,
  CONSTRAINT `sceglie_ibfk_2` FOREIGN KEY (`id_medico`) REFERENCES `medico` (`id_medico`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `chat` (
  `id_paziente` INT(11) NOT NULL,
  `id_chatbot` INT(11) NOT NULL,
  `data_avvio` DATE NOT NULL,
  `data_fine` DATE DEFAULT NULL,
  PRIMARY KEY (`id_paziente`, `id_chatbot`),
  KEY `id_chatbot` (`id_chatbot`),
  CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`id_paziente`) REFERENCES `paziente` (`id_paziente`) ON DELETE CASCADE,
  CONSTRAINT `chat_ibfk_2` FOREIGN KEY (`id_chatbot`) REFERENCES `chatbot` (`id_chatbot`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- INSERIMENTO ADMIN (password = Fh8$kZr#2025admin!)
INSERT INTO `utente` (`nome`, `email`, `password`, `tipo_utente`)
VALUES (
  'Admin Babylon',
  'admin@babylon.com',
  '$2y$10$X9pD7bGqT.lFQhN6UWY6LeKVmzNEwFhZJzjk8QkekDWCUHjByACz2',
  'Admin'
);



