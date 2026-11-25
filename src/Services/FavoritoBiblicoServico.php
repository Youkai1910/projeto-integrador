<?php
declare(strict_types=1);

namespace ProjetoIntegrador\Services;

use InvalidArgumentException;
use PDO;
use ProjetoIntegrador\Database\ConexaoBD;

final class FavoritoBiblicoServico
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? ConexaoBD::getConexao();
    }

    /**
     * @return array{capitulo: bool, versiculos: array<int, bool>}
     */
    public function estadoNoTrecho(int $usuarioId, int $livroId, int $capitulo): array
    {
        $this->validarId($usuarioId);
        $this->validarId($livroId);
        $this->validarId($capitulo);

        $consulta = $this->pdo->prepare(
            'SELECT tipo, versiculo
             FROM favoritos_biblicos
             WHERE usuario_id = :usuario_id AND livro_id = :livro_id AND capitulo = :capitulo'
        );
        $consulta->execute([
            ':usuario_id' => $usuarioId,
            ':livro_id' => $livroId,
            ':capitulo' => $capitulo,
        ]);

        $estado = ['capitulo' => false, 'versiculos' => []];
        foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $favorito) {
            if ($favorito['tipo'] === 'capitulo') {
                $estado['capitulo'] = true;
            } else {
                $estado['versiculos'][(int) $favorito['versiculo']] = true;
            }
        }

        return $estado;
    }

    /**
     * @return array<int, array{id: int, tipo: string, livro_id: int, capitulo: int, versiculo: int, livro_nome: string, texto: ?string, criado_em: string}>
     */
    public function listarDoUsuario(int $usuarioId): array
    {
        $this->validarId($usuarioId);
        $consulta = $this->pdo->prepare(
            'SELECT f.id, f.tipo, f.livro_id, f.capitulo, f.versiculo,
                    b.label AS livro_nome, v.text AS texto, f.criado_em
             FROM favoritos_biblicos f
             INNER JOIN books b ON b.id = f.livro_id
             LEFT JOIN verses v
                ON f.tipo = \'versiculo\'
                AND v.book = f.livro_id
                AND v.chapter = f.capitulo
                AND v.verse = f.versiculo
             WHERE f.usuario_id = :usuario_id
             ORDER BY f.criado_em DESC, f.id DESC'
        );
        $consulta->execute([':usuario_id' => $usuarioId]);

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function definirFavorito(
        int $usuarioId,
        string $tipo,
        int $livroId,
        int $capitulo,
        int $versiculo,
        bool $favorito
    ): void {
        $this->validarId($usuarioId);
        $this->validarId($livroId);
        $this->validarId($capitulo);

        if (($tipo === 'capitulo' && $versiculo !== 0)
            || ($tipo === 'versiculo' && $versiculo < 1)
            || !in_array($tipo, ['capitulo', 'versiculo'], true)) {
            throw new InvalidArgumentException('O favorito bíblico informado é inválido.');
        }

        if ($favorito) {
            $consulta = $this->pdo->prepare(
                'INSERT INTO favoritos_biblicos (usuario_id, tipo, livro_id, capitulo, versiculo)
                 VALUES (:usuario_id, :tipo, :livro_id, :capitulo, :versiculo)
                 ON DUPLICATE KEY UPDATE id = id'
            );
            $consulta->execute([
                ':usuario_id' => $usuarioId,
                ':tipo' => $tipo,
                ':livro_id' => $livroId,
                ':capitulo' => $capitulo,
                ':versiculo' => $versiculo,
            ]);
            return;
        }

        $consulta = $this->pdo->prepare(
            'DELETE FROM favoritos_biblicos
             WHERE usuario_id = :usuario_id
               AND tipo = :tipo
               AND livro_id = :livro_id
               AND capitulo = :capitulo
               AND versiculo = :versiculo'
        );
        $consulta->execute([
            ':usuario_id' => $usuarioId,
            ':tipo' => $tipo,
            ':livro_id' => $livroId,
            ':capitulo' => $capitulo,
            ':versiculo' => $versiculo,
        ]);
    }

    private function validarId(int $id): void
    {
        if ($id < 1) {
            throw new InvalidArgumentException('O identificador precisa ser positivo.');
        }
    }
}
