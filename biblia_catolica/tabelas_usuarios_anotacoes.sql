-- Selecione o banco configurado em DB_NAME antes de importar.

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    email VARCHAR(254) NOT NULL,
    tipo ENUM('usuario') NOT NULL DEFAULT 'usuario',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS anotacoes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    favorito TINYINT(1) NOT NULL DEFAULT 0,
    anotacao TEXT NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_anotacoes_usuario_id (usuario_id),
    CONSTRAINT fk_anotacoes_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS favoritos_biblicos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    tipo ENUM('capitulo', 'versiculo') NOT NULL,
    livro_id INT UNSIGNED NOT NULL,
    capitulo INT UNSIGNED NOT NULL,
    versiculo INT UNSIGNED NOT NULL DEFAULT 0,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_favoritos_biblicos_alvo (usuario_id, tipo, livro_id, capitulo, versiculo),
    KEY idx_favoritos_biblicos_usuario_criado (usuario_id, criado_em),
    CONSTRAINT fk_favoritos_biblicos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE usuarios
SET tipo = 'usuario'
WHERE tipo <> 'usuario';

ALTER TABLE usuarios
    MODIFY tipo ENUM('usuario') NOT NULL DEFAULT 'usuario';
