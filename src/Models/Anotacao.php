<?php
namespace ProjetoIntegrador\Models;

final class Anotacao
{
    public int $id;
    public int $usuarioId;
    public bool $favorito;
    public string $texto;
    public ?string $criadoEm;
    public ?string $atualizadoEm;

    public function __construct(
        int $id,
        int $usuarioId,
        bool $favorito,
        string $texto,
        ?string $criadoEm,
        ?string $atualizadoEm
    ) {
        $this->id = $id;
        $this->usuarioId = $usuarioId;
        $this->favorito = $favorito;
        $this->texto = $texto;
        $this->criadoEm = $criadoEm;
        $this->atualizadoEm = $atualizadoEm;
    }

    public static function fromArray(array $dados): self
    {
        return new self(
            (int) $dados['id'],
            (int) $dados['usuario_id'],
            (bool) $dados['favorito'],
            (string) $dados['anotacao'],
            isset($dados['criado_em']) ? (string) $dados['criado_em'] : null,
            isset($dados['atualizado_em']) ? (string) $dados['atualizado_em'] : null
        );
    }
}
