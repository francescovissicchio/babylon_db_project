
-- DATABASE: telemedicina

DROP DATABASE IF EXISTS telemedicina;
CREATE DATABASE telemedicina;
USE telemedicina;

SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE Utente (
    id_utente INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    tipo_utente VARCHAR(20) CHECK (tipo_utente IN ('Medico', 'Paziente'))
);

CREATE TABLE Medico (
    id_medico INT PRIMARY KEY,
    specializzazione VARCHAR(100),
    FOREIGN KEY (id_medico) REFERENCES Utente(id_utente)
);

CREATE TABLE Paziente (
    id_paziente INT PRIMARY KEY,
    data_nascita DATE,
    FOREIGN KEY (id_paziente) REFERENCES Utente(id_utente)
);

CREATE TABLE Chat (
    id_chat INT PRIMARY KEY AUTO_INCREMENT,
    data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Chatbot (
    id_chatbot INT PRIMARY KEY AUTO_INCREMENT,
    nome_bot VARCHAR(100)
);

CREATE TABLE Visita (
    id_visita INT PRIMARY KEY AUTO_INCREMENT,
    data_visita TIMESTAMP,
    descrizione TEXT
);

CREATE TABLE Partecipa (
    id_medico INT,
    id_chat INT,
    PRIMARY KEY (id_medico, id_chat),
    FOREIGN KEY (id_medico) REFERENCES Medico(id_medico),
    FOREIGN KEY (id_chat) REFERENCES Chat(id_chat)
);

CREATE TABLE Avvia (
    id_paziente INT,
    id_chat INT,
    PRIMARY KEY (id_paziente, id_chat),
    FOREIGN KEY (id_paziente) REFERENCES Paziente(id_paziente),
    FOREIGN KEY (id_chat) REFERENCES Chat(id_chat)
);

CREATE TABLE Sceglie (
    id_chatbot INT PRIMARY KEY,
    id_medico INT,
    FOREIGN KEY (id_chatbot) REFERENCES Chatbot(id_chatbot),
    FOREIGN KEY (id_medico) REFERENCES Medico(id_medico)
);

CREATE TABLE Presiede (
    id_visita INT PRIMARY KEY,
    id_medico INT,
    FOREIGN KEY (id_visita) REFERENCES Visita(id_visita),
    FOREIGN KEY (id_medico) REFERENCES Medico(id_medico)
);

CREATE TABLE Interagisce (
    id_paziente INT,
    id_chatbot INT,
    PRIMARY KEY (id_paziente, id_chatbot),
    FOREIGN KEY (id_paziente) REFERENCES Paziente(id_paziente),
    FOREIGN KEY (id_chatbot) REFERENCES Chatbot(id_chatbot)
);

CREATE TABLE Prenota (
    id_visita INT PRIMARY KEY,
    id_paziente INT,
    FOREIGN KEY (id_visita) REFERENCES Visita(id_visita),
    FOREIGN KEY (id_paziente) REFERENCES Paziente(id_paziente)
);

CREATE TABLE Messaggio (
    id_messaggio INT PRIMARY KEY AUTO_INCREMENT,
    id_chat INT,
    id_mittente INT,
    contenuto TEXT,
    data_invio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_chat) REFERENCES Chat(id_chat),
    FOREIGN KEY (id_mittente) REFERENCES Utente(id_utente)
);

SET FOREIGN_KEY_CHECKS=1;
