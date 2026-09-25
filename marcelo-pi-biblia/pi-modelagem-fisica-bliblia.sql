-- 1. Criação da tabela de Usuários
CREATE TABLE usuarios (
    id_usuarios INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo_usuarios VARCHAR(50) NOT NULL,
    CONSTRAINT pk_usuarios PRIMARY KEY (id_usuarios),
    CONSTRAINT uq_usuarios_email UNIQUE (email)
);

-- 2. Criação da tabela da Bíblia
CREATE TABLE biblia (
    id_biblia INT NOT NULL AUTO_INCREMENT,
    livro VARCHAR(100) NOT NULL,
    capitulo INT NOT NULL,
    versiculo INT NOT NULL,
    texto TEXT NOT NULL,
    CONSTRAINT pk_biblia PRIMARY KEY (id_biblia)
);

-- 3. Criação da tabela de Anotações (Contém as Chaves Estrangeiras)
CREATE TABLE anotacoes (
    id_anotacoes INT NOT NULL AUTO_INCREMENT,
    id_usuarios INT NOT NULL,
    id_biblia INT NOT NULL,
    comentarios TEXT,
    blocos_notas VARCHAR(100),
    CONSTRAINT pk_anotacoes PRIMARY KEY (id_anotacoes),
    CONSTRAINT fk_anotacoes_usuarios FOREIGN KEY (id_usuarios) 
        REFERENCES usuarios (id_usuarios) 
        ON DELETE CASCADE,
    CONSTRAINT fk_anotacoes_biblia FOREIGN KEY (id_biblia) 
        REFERENCES biblia (id_biblia)
);
