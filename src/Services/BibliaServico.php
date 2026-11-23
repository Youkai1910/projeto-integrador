<?php
namespace ProjetoIntegrador\Services;

use InvalidArgumentException;
use PDO;
use ProjetoIntegrador\Database\ConexaoBD;
use ProjetoIntegrador\Models\Livro;
use ProjetoIntegrador\Models\Testamento;
use ProjetoIntegrador\Models\Versiculo;

final class BibliaServico
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? ConexaoBD::getConexaoBiblia();
    }

    /**
     * @return Testamento[]
     */
    public function listarTestamentos(): array
    {
        $linhas = $this->pdo->query('SELECT id, name FROM testament ORDER BY id')->fetchAll();
        $testamentos = [];

        foreach ($linhas as $linha) {
            $testamentos[] = Testamento::fromArray($linha);
        }

        return $testamentos;
    }

    /**
     * @return Livro[]
     */
    public function listarLivros(?int $testamentoId = null, string $termo = ''): array
    {
        $condicoes = ['1 = 1'];
        $parametros = [];

        if ($testamentoId !== null) {
            $this->validarId($testamentoId);
            $condicoes[] = 'b.testament = :testament';
            $parametros[':testament'] = [$testamentoId, PDO::PARAM_INT];
        }

        if ($termo !== '') {
            $condicoes[] = '(b.label LIKE :termo_label OR b.search_name LIKE :termo_search)';
            $parametros[':termo_label'] = ['%' . $termo . '%', PDO::PARAM_STR];
            $parametros[':termo_search'] = ['%' . $termo . '%', PDO::PARAM_STR];
        }

        return $this->consultarLivros(implode(' AND ', $condicoes), $parametros);
    }

    public function buscarLivro(int $id): ?Livro
    {
        $this->validarId($id);
        $livros = $this->consultarLivros('b.id = :id', [':id' => [$id, PDO::PARAM_INT]]);

        return $livros[0] ?? null;
    }

    /**
     * @return int[]
     */
    public function listarCapitulos(int $livroId): array
    {
        $this->validarId($livroId);
        $consulta = $this->pdo->prepare(
            'SELECT DISTINCT chapter
             FROM verses
             WHERE book = :book AND chapter > 0
             ORDER BY chapter'
        );
        $consulta->bindValue(':book', $livroId, PDO::PARAM_INT);
        $consulta->execute();

        return array_map('intval', $consulta->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * @return Versiculo[]
     */
    public function listarVersiculos(int $livroId, int $capitulo): array
    {
        $this->validarId($livroId);
        $this->validarId($capitulo);
        $consulta = $this->pdo->prepare(
            'SELECT id, book, chapter, verse, text
             FROM verses
             WHERE book = :book AND chapter = :chapter
             ORDER BY verse, id'
        );
        $consulta->bindValue(':book', $livroId, PDO::PARAM_INT);
        $consulta->bindValue(':chapter', $capitulo, PDO::PARAM_INT);
        $consulta->execute();

        $versiculos = [];
        foreach ($consulta->fetchAll() as $linha) {
            $versiculos[] = Versiculo::fromArray($linha);
        }

        return $versiculos;
    }

    /**
     * @return Livro[]
     */
    private function consultarLivros(string $condicao, array $parametros): array
    {
        $sql = 'SELECT b.id, b.label, b.search_name, b.testament,
                       t.name AS testament_name,
                       COUNT(DISTINCT v.chapter) AS chapter_count,
                       COUNT(v.id) AS verse_count
                FROM books b
                LEFT JOIN testament t ON t.id = b.testament
                LEFT JOIN verses v ON v.book = b.id
                WHERE ' . $condicao . '
                GROUP BY b.id, b.label, b.search_name, b.testament, t.name
                ORDER BY b.id';
        $consulta = $this->pdo->prepare($sql);

        foreach ($parametros as $nome => $parametro) {
            $consulta->bindValue($nome, $parametro[0], $parametro[1]);
        }

        $consulta->execute();
        $livros = [];

        foreach ($consulta->fetchAll() as $linha) {
            $livros[] = Livro::fromArray($linha);
        }

        return $livros;
    }

    private function validarId(int $id): void
    {
        if ($id < 1) {
            throw new InvalidArgumentException('O identificador precisa ser positivo.');
        }
    }
}
