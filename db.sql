-- progetto_babylon_vissicchio.chatbot definition

CREATE TABLE `chatbot` (
  `id_chatbot` int(11) NOT NULL AUTO_INCREMENT,
  `nome_bot` varchar(100) DEFAULT NULL,
  `sintomi_riportati` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_chatbot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- progetto_babylon_vissicchio.utente definition

CREATE TABLE `utente` (
  `id_utente` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `tipo_utente` enum('Medico','Paziente') NOT NULL,
  PRIMARY KEY (`id_utente`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- progetto_babylon_vissicchio.medico definition

CREATE TABLE `medico` (
  `id_medico` int(11) NOT NULL,
  `Specializzazione` varchar(100) NOT NULL DEFAULT 'Medicina generale',
  `Rating` double NOT NULL DEFAULT 0,
  `Disponibilita` BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id_medico`),
  CONSTRAINT `medico_ibfk_1` FOREIGN KEY (`id_medico`) REFERENCES `utente` (`id_utente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- progetto_babylon_vissicchio.paziente definition

CREATE TABLE `paziente` (
  `id_paziente` int(11) NOT NULL,
  `data_nascita` date DEFAULT NULL,
  `Sesso` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_paziente`),
  CONSTRAINT `paziente_ibfk_1` FOREIGN KEY (`id_paziente`) REFERENCES `utente` (`id_utente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- progetto_babylon_vissicchio.visita definition

CREATE TABLE `visita` (
  `id_visita` INT(11) NOT NULL AUTO_INCREMENT,
  `id_paziente` INT(11) NOT NULL,
  `id_medico` INT(11) NOT NULL,
  `data_visita` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `esito_visita` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_visita`),
  UNIQUE KEY `unico_paziente_visita` (`id_paziente`),
  KEY `id_medico` (`id_medico`),
  CONSTRAINT `visita_ibfk_1` FOREIGN KEY (`id_paziente`) REFERENCES `paziente` (`id_paziente`),
  CONSTRAINT `visita_ibfk_2` FOREIGN KEY (`id_medico`) REFERENCES `medico` (`id_medico`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- progetto_babylon_vissicchio.sceglie definition

CREATE TABLE `sceglie` (
  `id_chatbot` int(11) NOT NULL,
  `id_medico` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_chatbot`),
  KEY `id_medico` (`id_medico`),
  CONSTRAINT `sceglie_ibfk_1` FOREIGN KEY (`id_chatbot`) REFERENCES `chatbot` (`id_chatbot`),
  CONSTRAINT `sceglie_ibfk_2` FOREIGN KEY (`id_medico`) REFERENCES `medico` (`id_medico`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- progetto_babylon_vissicchio.chat definition

CREATE TABLE `chat` (
  `id_paziente` int(11) NOT NULL,
  `id_chatbot` int(11) NOT NULL,
  `data_avvio` date NOT NULL,
  PRIMARY KEY (`id_paziente`,`id_chatbot`),
  KEY `id_chatbot` (`id_chatbot`),
  CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`id_paziente`) REFERENCES `paziente` (`id_paziente`),
  CONSTRAINT `chat_ibfk_2` FOREIGN KEY (`id_chatbot`) REFERENCES `chatbot` (`id_chatbot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
