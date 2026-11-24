<?php
declare(strict_types=1);

namespace ProjetoIntegrador\Services;

use PDO;

final class LeituraBiblicaServico
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    /** @return array<int, array{id: int, label: string, search_name: string, testament: int}> */
    public function listarLivros(string $busca = '', ?int $testamento = null): array
    {
        $condicoes = [];
        $parametros = [];

        if ($busca !== '') {
            $condicoes[] = '(label LIKE :label OR search_name LIKE :search_name)';
            $termo = '%' . $busca . '%';
            $parametros['label'] = $termo;
            $parametros['search_name'] = $termo;
        }
        if ($testamento !== null) {
            $condicoes[] = 'testament = :testament';
            $parametros['testament'] = $testamento;
        }

        $sql = 'SELECT id, label, search_name, testament FROM books';
        if ($condicoes !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $condicoes);
        }
        $sql .= ' ORDER BY id';

        $consulta = $this->conexao->prepare($sql);
        $consulta->execute($parametros);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array{id: int, label: string, search_name: string, testament: int}|null */
    public function obterLivro(int $idLivro): ?array
    {
        $consulta = $this->conexao->prepare(
            'SELECT id, label, search_name, testament FROM books WHERE id = :id LIMIT 1'
        );
        $consulta->execute(['id' => $idLivro]);
        $livro = $consulta->fetch(PDO::FETCH_ASSOC);

        return $livro === false ? null : $livro;
    }

    /** @return int[] */
    public function listarCapitulos(int $idLivro): array
    {
        $consulta = $this->conexao->prepare(
            'SELECT DISTINCT chapter FROM verses WHERE book = :book AND chapter IS NOT NULL ORDER BY chapter'
        );
        $consulta->execute(['book' => $idLivro]);
        return array_map('intval', $consulta->fetchAll(PDO::FETCH_COLUMN));
    }

    /** @return array<int, array{verse: int, text: string}> */
    public function listarVersiculos(int $idLivro, int $capitulo): array
    {
        $consulta = $this->conexao->prepare(
            'SELECT verse, text FROM verses WHERE book = :book AND chapter = :chapter ORDER BY verse'
        );
        $consulta->execute(['book' => $idLivro, 'chapter' => $capitulo]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array{book: int, chapter: int, verse: int, text: string}|null */
    public function obterVersiculoAleatorio(): ?array
    {
        $consulta = $this->conexao->query(
            'SELECT book, chapter, verse, text FROM verses WHERE text IS NOT NULL ORDER BY RAND() LIMIT 1'
        );
        $versiculo = $consulta->fetch(PDO::FETCH_ASSOC);
        return $versiculo === false ? null : $versiculo;
    }

    public function obterTestamento(int $idTestamento): ?string
    {
        $consulta = $this->conexao->prepare('SELECT name FROM testament WHERE id = :id LIMIT 1');
        $consulta->execute(['id' => $idTestamento]);
        $nome = $consulta->fetchColumn();
        return $nome === false ? null : (string) $nome;
    }
}
