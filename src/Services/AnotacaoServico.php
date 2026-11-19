<?php
namespace ProjetoIntegrador\Services;

use InvalidArgumentException;
use PDO;
use ProjetoIntegrador\Database\ConexaoBD;
use ProjetoIntegrador\Helpers\Validacoes;
use ProjetoIntegrador\Models\Anotacao;

final class AnotacaoServico
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? ConexaoBD::getConexao();
    }

    /**
     * @return Anotacao[]
     */
    public function listarDoUsuario(int $usuarioId): array
    {
        $this->validarId($usuarioId);
        $consulta = $this->pdo->prepare(
            'SELECT id, usuario_id, favorito, anotacao, criado_em, atualizado_em
             FROM anotacoes
             WHERE usuario_id = :usuario_id
             ORDER BY favorito DESC, atualizado_em DESC, id DESC'
        );
        $consulta->execute([':usuario_id' => $usuarioId]);

        $anotacoes = [];
        foreach ($consulta->fetchAll() as $linha) {
            $anotacoes[] = Anotacao::fromArray($linha);
        }

        return $anotacoes;
    }

    public function criar(int $usuarioId, string $texto): int
    {
        $this->validarId($usuarioId);
        $texto = Validacoes::textoAnotacao($texto);

        $consulta = $this->pdo->prepare(
            'INSERT INTO anotacoes (usuario_id, anotacao) VALUES (:usuario_id, :anotacao)'
        );
        $consulta->execute([
            ':usuario_id' => $usuarioId,
            ':anotacao' => $texto,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(int $id, int $usuarioId, string $texto): bool
    {
        $this->validarId($id);
        $this->validarId($usuarioId);
        $texto = Validacoes::textoAnotacao($texto);

        $consulta = $this->pdo->prepare(
            'UPDATE anotacoes
             SET anotacao = :anotacao
             WHERE id = :id AND usuario_id = :usuario_id'
        );
        $consulta->execute([
            ':anotacao' => $texto,
            ':id' => $id,
            ':usuario_id' => $usuarioId,
        ]);

        return $consulta->rowCount() > 0 || $this->pertenceAoUsuario($id, $usuarioId);
    }

    public function definirFavorito(int $id, int $usuarioId, bool $favorito): bool
    {
        $this->validarId($id);
        $this->validarId($usuarioId);

        $consulta = $this->pdo->prepare(
            'UPDATE anotacoes
             SET favorito = :favorito
             WHERE id = :id AND usuario_id = :usuario_id'
        );
        $consulta->execute([
            ':favorito' => $favorito ? 1 : 0,
            ':id' => $id,
            ':usuario_id' => $usuarioId,
        ]);

        return $consulta->rowCount() > 0 || $this->pertenceAoUsuario($id, $usuarioId);
    }

    public function remover(int $id, int $usuarioId): bool
    {
        $this->validarId($id);
        $this->validarId($usuarioId);

        $consulta = $this->pdo->prepare(
            'DELETE FROM anotacoes WHERE id = :id AND usuario_id = :usuario_id'
        );
        $consulta->execute([
            ':id' => $id,
            ':usuario_id' => $usuarioId,
        ]);

        return $consulta->rowCount() > 0;
    }

    private function pertenceAoUsuario(int $id, int $usuarioId): bool
    {
        $consulta = $this->pdo->prepare(
            'SELECT 1 FROM anotacoes WHERE id = :id AND usuario_id = :usuario_id LIMIT 1'
        );
        $consulta->execute([
            ':id' => $id,
            ':usuario_id' => $usuarioId,
        ]);

        return $consulta->fetchColumn() !== false;
    }

    private function validarId(int $id): void
    {
        if (Validacoes::idPositivo($id) === null) {
            throw new InvalidArgumentException('O identificador precisa ser positivo.');
        }
    }
}
