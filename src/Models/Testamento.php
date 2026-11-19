<?php
namespace ProjetoIntegrador\Models;

final class Testamento
{
    public int $id;
    public ?string $nome;

    public function __construct(int $id, ?string $nome)
    {
        $this->id = $id;
        $this->nome = $nome;
    }

    public static function fromArray(array $dados): self
    {
        return new self(
            (int) $dados['id'],
            isset($dados['name']) ? (string) $dados['name'] : null
        );
    }
}
