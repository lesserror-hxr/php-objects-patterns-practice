<?php
declare(strict_types = 1);

namespace popp\ch13\batch03;

use popp\ch13\batch01\Venue;
use popp\ch13\batch01\Collection;
use popp\ch13\batch01\VenueCollection;
use popp\ch13\batch03\Mapper;
use popp\ch13\batch01\DomainObject;
use popp\ch13\batch01\SpaceMapper;

class VenueMapper extends Mapper
{
    private $selectStmt;
    private $selectAllStmt;
    private $updateStmt;
    private $insertStmt;

    public function __construct()
    {
        parent::__construct();
        $this->selectStmt = $this->pdo->prepare(
            "SELECT * FROM venue WHERE id=?"
        );

        $this->selectAllStmt = $this->pdo->prepare(
            "SELECT * FROM venue"
        );

        $this->updateStmt = $this->pdo->prepare(
            "update venue set name=?, id=? where id=?"
        );

        $this->insertStmt = $this->pdo->prepare(
            "insert into venue ( name ) values( ? )"
        );
    }

    // 获取当前正在等待实例化的类的名字。
    protected function targetClass(): string
    {
        return Venue::class;
    }

    public function getCollection(array $raw): Collection
    {
        return new VenueCollection($raw, $this);
    }

    protected function doCreateObject(array $array): DomainObject
    {
        $obj = new Venue((int)$array['id'], $array['name']);
        $spacemapper = new SpaceMapper();
        $spacecollection = $spacemapper->findByVenue($array['id']);
        $obj->setSpaces($spacecollection);

        return $obj;
    }

    protected function doInsert(DomainObject $object)
    {
        $values = [$object->getName()];
        $this->insertStmt->execute($values);
        $id = $this->pdo->lastInsertId();
        $object->setId((int)$id);
    }

    public function update(DomainObject $object)
    {
        $values = array( $object->getName(), $object->getId(), $object->getId() );
        $this->updateStmt->execute($values);
    }

    public function selectStmt(): \PDOStatement
    {
        return $this->selectStmt;
    }

    public function selectAllStmt(): \PDOStatement
    {
        return $this->selectAllStmt;
    }
}
