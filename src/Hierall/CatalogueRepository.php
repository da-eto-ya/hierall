<?php
/**
 * Простой репозиторий для работы с каталогами.
 */
namespace Hierall;

/**
 * Class CatalogueRepository
 * Репозиторий для работы с каталогами.
 * @package Hierall
 */
class CatalogueRepository
{
    /**
     * Инстанс PDO, используемый для связи с БД.
     * @var \PDO
     */
    private $pdo = null;

    /**
     * CatalogueRepository constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Возвращает все каталоги верхнего уровня.
     * @return array
     */
    public function fetchRootCatalogues()
    {
        $sql = "SELECT * FROM catalogues WHERE parent_id IS NULL";
        $catalogues = [];
        $res = $this->pdo->query($sql);

        while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
            $catalogues[] = $this->cleanCatalogue($row);
        }

        return $catalogues;
    }

    /**
     * Возвращает дочерние каталоги для указанного.
     * @param int $parentId ID родительского каталога
     * @return array
     */
    public function fetchChildrenCatalogues($parentId)
    {
        $parentId = (int)$parentId;
        $sql = "SELECT * FROM catalogues WHERE parent_id = ?";
        $catalogues = [];
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$parentId]);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $catalogues[] = $this->cleanCatalogue($row);
        }

        return $catalogues;
    }

    /**
     * Возвращает строгие данные каталога (без вспомогательных полей).
     * @param array $catalogue
     * @return array
     */
    private function cleanCatalogue(array $catalogue)
    {
        return [
            'id' => $catalogue['id'],
            'name' => $catalogue['name'],
        ];
    }

    /**
     * Возвращает родительский каталог для заданного.
     * @param int $nodeId
     * @return array|null
     */
    public function fetchParentNode($nodeId)
    {
        $nodeId = (int)$nodeId;
        $sql = "
            SELECT p.*
            FROM catalogues AS c JOIN catalogues AS p ON (c.parent_id = p.id)
            WHERE c.id = ?
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nodeId]);
        $parent = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$parent) {
            return null;
        } else {
            return $this->cleanCatalogue($parent);
        }
    }
}
