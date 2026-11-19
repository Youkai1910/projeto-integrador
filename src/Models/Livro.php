<?php
namespace ProjetoIntegrador\Models;

final class Livro
{
    public int $id;
    public ?string $nome;
    public ?string $nomeBusca;
    public ?int $testamentoId;
    public ?string $testamentoNome;
    public int $totalCapitulos;
    public int $totalVersiculos;

    public function __construct(
        int $id,
        ?string $nome,
        ?string $nomeBusca,
        ?int $testamentoId,
        ?string $testamentoNome,
        int $totalCapitulos,
        int $totalVersiculos
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->nomeBusca = $nomeBusca;
        $this->testamentoId = $testamentoId;
        $this->testamentoNome = $testamentoNome;
        $this->totalCapitulos = $totalCapitulos;
        $this->totalVersiculos = $totalVersiculos;
    }

    public static function fromArray(array $dados): self
    {
        return new self(
            (int) $dados['id'],
            isset($dados['label']) ? (string) $dados['label'] : null,
            isset($dados['search_name']) ? (string) $dados['search_name'] : null,
            isset($dados['testament']) ? (int) $dados['testament'] : null,
            isset($dados['testament_name']) ? (string) $dados['testament_name'] : null,
            isset($dados['chapter_count']) ? (int) $dados['chapter_count'] : 0,
            isset($dados['verse_count']) ? (int) $dados['verse_count'] : 0
        );
    }
}
