<?php declare(strict_types=1);

namespace SwagMigrationNext\Migration\Run;

use Shopware\Core\Framework\Struct\Struct;

class RunProgress extends Struct
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var EntityProgress[]
     */
    protected $entities;

    /**
     * @var int
     */
    protected $currentCount;

    /**
     * @var int
     */
    protected $total;

    /**
     * @var string
     */
    protected $snippet;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getEntities(): array
    {
        return $this->entities;
    }

    public function setEntities(array $entities): void
    {
        $this->entities = $entities;
    }

    public function getCurrentCount(): int
    {
        return $this->currentCount;
    }

    public function setCurrentCount(int $currentCount): void
    {
        $this->currentCount = $currentCount;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function getSnippet(): string
    {
        return $this->snippet;
    }

    public function setSnippet(string $snippet): void
    {
        $this->snippet = $snippet;
    }
}