<?php
namespace ProjetoIntegrador\Models;

final class Versiculo
{
    public int $id;
    public ?int $livroId;
    public ?int $capitulo;
    public ?int $numero;
    public ?string $texto;

    public function __construct(int $id, ?int $livroId, ?int $capitulo, ?int $numero, ?string $texto)
    {
        $this->id = $id;
        $this->livroId = $livroId;
        $this->capitulo = $capitulo;
        $this->numero = $numero;
        $this->texto = $texto;
    }

    public static function fromArray(array $dados): self
    {
        return new self(
            (int) $dados['id'],
            isset($dados['book']) ? (int) $dados['book'] : null,
            isset($dados['chapter']) ? (int) $dados['chapter'] : null,
            isset($dados['verse']) ? (int) $dados['verse'] : null,
            isset($dados['text']) ? (string) $dados['text'] : null
        );
    }
}
