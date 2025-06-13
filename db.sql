-- Database: progetto_babylon_ammendola

-- CREAZIONE TABELLE

 DROP TABELLE SE ESISTONO
DROP TABLE IF EXISTS esame, terapia, chat, sceglie, visita, care, medico, paziente, utente, chatbot;

-- UTENTE
CREATE TABLE `utente` (
  `id_utente` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) DEFAULT NULL,
  `cognome` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `password` VARCHAR(255) DEFAULT NULL,
  `tipo_utente` ENUM('Medico','Paziente','Admin') NOT NULL,
  `foto_profilo` VARCHAR(255) DEFAULT NULL,
  `data_registrazione` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cancellato` BOOLEAN NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_utente`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- MEDICO
CREATE TABLE `medico` (
  `id_medico` INT(11) NOT NULL,
  `specializzazione` VARCHAR(100) NOT NULL DEFAULT 'Medicina generale',
  `rating` DOUBLE NOT NULL DEFAULT 0,
  `disponibilita` BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id_medico`),
  CONSTRAINT `medico_ibfk_1` FOREIGN KEY (`id_medico`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PAZIENTE
CREATE TABLE `paziente` (
  `id_paziente` INT(11) NOT NULL,
  `data_nascita` DATE DEFAULT NULL,
  `sesso` VARCHAR(100) DEFAULT NULL,
  `statura_cm` INT DEFAULT NULL,
  `peso_kg` DECIMAL(5,2) DEFAULT NULL,
  PRIMARY KEY (`id_paziente`),
  CONSTRAINT `paziente_ibfk_1` FOREIGN KEY (`id_paziente`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- CHATBOT
CREATE TABLE `chatbot` (
  `id_chatbot` INT(11) NOT NULL AUTO_INCREMENT,
  `nome_bot` VARCHAR(100) DEFAULT NULL,
  `sintomi_riportati` TEXT DEFAULT NULL,
  `specializzazione_dedotta` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id_chatbot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- VISITA
CREATE TABLE `visita` (
  `id_visita` INT(11) NOT NULL AUTO_INCREMENT,
  `id_paziente` INT(11) NOT NULL,
  `id_medico` INT(11) NOT NULL,
  `id_chatbot` INT(11) DEFAULT NULL,
  `data_visita` DATETIME DEFAULT NULL,
  `esito_visita` TEXT DEFAULT NULL,
  `stato` ENUM('in_attesa', 'pianificata', 'completata', 'annullata') DEFAULT 'in_attesa',
  PRIMARY KEY (`id_visita`),
  KEY `id_medico` (`id_medico`),
  KEY `id_chatbot` (`id_chatbot`),
  CONSTRAINT `visita_ibfk_1` FOREIGN KEY (`id_paziente`) REFERENCES `paziente` (`id_paziente`) ON DELETE CASCADE,
  CONSTRAINT `visita_ibfk_2` FOREIGN KEY (`id_medico`) REFERENCES `medico` (`id_medico`) ON DELETE CASCADE,
  CONSTRAINT `visita_ibfk_3` FOREIGN KEY (`id_chatbot`) REFERENCES `chatbot` (`id_chatbot`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SCEGLIE
CREATE TABLE `sceglie` (
  `id_chatbot` INT(11) NOT NULL,
  `id_medico` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id_chatbot`),
  KEY `id_medico` (`id_medico`),
  CONSTRAINT `sceglie_ibfk_1` FOREIGN KEY (`id_chatbot`) REFERENCES `chatbot` (`id_chatbot`) ON DELETE CASCADE,
  CONSTRAINT `sceglie_ibfk_2` FOREIGN KEY (`id_medico`) REFERENCES `medico` (`id_medico`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- CHAT
CREATE TABLE `chat` (
  `id_paziente` INT(11) NOT NULL,
  `id_chatbot` INT(11) NOT NULL,
  `data_avvio` DATETIME NOT NULL,
  `data_fine` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_paziente`, `id_chatbot`),
  CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`id_paziente`) REFERENCES `paziente` (`id_paziente`) ON DELETE CASCADE,
  CONSTRAINT `chat_ibfk_2` FOREIGN KEY (`id_chatbot`) REFERENCES `chatbot` (`id_chatbot`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- CARE
CREATE TABLE `care` (
  `id_care` INT(11) NOT NULL AUTO_INCREMENT,
  `id_medico` INT(11) NOT NULL,
  `id_paziente` INT(11) NOT NULL,
  `data_inizio` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_fine` DATETIME DEFAULT NULL,
  `follow_up` TEXT DEFAULT NULL,
  `feedback` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_care`),
  KEY `id_medico` (`id_medico`),
  KEY `id_paziente` (`id_paziente`),
  CONSTRAINT `care_ibfk_1` FOREIGN KEY (`id_medico`) REFERENCES `medico` (`id_medico`) ON DELETE CASCADE,
  CONSTRAINT `care_ibfk_2` FOREIGN KEY (`id_paziente`) REFERENCES `paziente` (`id_paziente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TERAPIA (prescrizioni + piani terapeutici)
CREATE TABLE `terapia` (
  `id_terapia` INT AUTO_INCREMENT PRIMARY KEY,
  `id_care` INT NOT NULL,
  `tipo` ENUM('Farmaco', 'Fisioterapia', 'Dieta', 'Altro') DEFAULT 'Farmaco',
  `descrizione` TEXT NOT NULL,
  `data_inizio` DATE DEFAULT NULL,
  `data_fine` DATE DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  CONSTRAINT `fk_terapia_care` FOREIGN KEY (`id_care`) REFERENCES `care` (`id_care`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ESAME (nuova tabella)
CREATE TABLE `esame` (
  `id_esame` INT AUTO_INCREMENT PRIMARY KEY,
  `id_care` INT NOT NULL,
  `tipo_esame` VARCHAR(100) NOT NULL,
  `descrizione` TEXT,
  `data_prescrizione` DATE DEFAULT NULL,
  `data_esecuzione` DATE DEFAULT NULL,
  `risultati` TEXT,
  `note` TEXT,
  CONSTRAINT `fk_esame_care` FOREIGN KEY (`id_care`) REFERENCES `care` (`id_care`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- INSERIMENTO ADMIN
INSERT INTO `utente` (`nome`, `email`, `password`, `tipo_utente`)
VALUES (
  'Admin Babylon',
  'admin@babylon.com',
  '$2y$10$X9pD7bGqT.lFQhN6UWY6LeKVmzNEwFhZJzjk8QkekDWCUHjByACz2',
  'Admin'
);
