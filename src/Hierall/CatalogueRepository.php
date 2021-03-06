<?php
/**
 * Простой репозиторий для работы с каталогами.
 */
namespace Hierall;

use PDO;

/**
 * Class CatalogueRepository
 * Репозиторий для работы с каталогами.
 * @package Hierall
 */
class CatalogueRepository
{
    /**
     * Инстанс PDO, используемый для связи с БД.
     * @var PDO
     */
    private $pdo = null;

    /**
     * CatalogueRepository constructor.
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
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

        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
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

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $catalogues[] = $this->cleanCatalogue($row);
        }

        return $catalogues;
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
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$parent) {
            return null;
        } else {
            return $this->cleanCatalogue($parent);
        }
    }

    /**
     * Удаляет каталог с заданным ID.
     * @param int $catalogueId
     * @return bool возвращает true, если удаление действительно было произведено
     */
    public function removeCatalogue($catalogueId)
    {
        $catalogueId = (int)$catalogueId;
        $success = false;

        if ($catalogueId) {
            $sql = "DELETE FROM catalogues WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([$catalogueId]);

            if ($success) {
                $success = $stmt->rowCount() > 0;
            }
        }

        return $success;
    }

    /**
     * Переименовывает каталог с заданным ID.
     * @param int    $catalogueId
     * @param string $name
     * @return bool возвращает true, если переименование действительно было произведено
     */
    public function renameCatalogue($catalogueId, $name)
    {
        $success = false;
        $catalogueId = (int)$catalogueId;
        $name = (string)$name;

        if ($catalogueId) {
            $sql = "UPDATE catalogues SET name = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([$name, $catalogueId]);

            if ($success) {
                $success = $stmt->rowCount() > 0;
            }
        }

        return $success;
    }

    /**
     * Удаляет все данные каталогов.
     */
    public function truncateCatalogues()
    {
        $sql = "TRUNCATE catalogues RESTART IDENTITY";
        $this->pdo->exec($sql);
    }

    /**
     * Добавляет каталог.
     * @param string   $name
     * @param int|null $parentId
     * @return int|false ID созданного каталога или false в случае ошибки
     */
    public function addCatalogue($name, $parentId)
    {
        $result = false;
        $name = (string)$name;

        if (null === $parentId) {
            $sql = "INSERT INTO catalogues (name) VALUES (?)";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$name]);
        } else {
            $parentId = (int)$parentId;

            if ($parentId) {
                $sql = "INSERT INTO catalogues (name, parent_id) VALUES (?, ?)";
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute([$name, $parentId]);
            }
        }

        if ($result) {
            $result = (int)$this->pdo->lastInsertId('catalogues_id_seq');
        }

        return $result;
    }
}
