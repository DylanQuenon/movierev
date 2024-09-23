<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;
use Doctrine\ORM\QueryBuilder;

class PaginationService
{
    private $dataSource;
    private string $entityClass;
    private int $limit = 10;
    private int $currentPage = 1;
    private EntityManagerInterface $manager;
    private string $route = '';  // Initialisation de la propriété
    private Environment $twig;
    private string $templatePath;
    private array $order = [];

    public function __construct(EntityManagerInterface $manager, Environment $twig, string $templatePath)
    {
        $this->manager = $manager;
        $this->twig = $twig;
        $this->templatePath = $templatePath;
    }

    public function setEntityClass(string $entityClass): self
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function setPage(int $page): self
    {
        $this->currentPage = $page;
        return $this;
    }

    public function setDataSource($dataSource): self
    {
        $this->dataSource = $dataSource;
        return $this;
    }

    public function setRoute(string $route): self
    {
        $this->route = $route;
        return $this;
    }

    public function getData(): array
    {
        if (empty($this->dataSource)) {
            throw new \Exception("La source de données pour la pagination n'est pas définie !");
        }

        $offset = $this->currentPage * $this->limit - $this->limit;

        if ($this->dataSource instanceof QueryBuilder) {
            return $this->dataSource
                ->setFirstResult($offset)
                ->setMaxResults($this->limit)
                ->getQuery()
                ->getResult();
        }

        if (is_string($this->dataSource)) {
            return $this->manager
                ->getRepository($this->dataSource)
                ->findBy([], $this->order, $this->limit, $offset);
        }

        if (is_array($this->dataSource)) {
            return array_slice($this->dataSource, $offset, $this->limit);
        }

        throw new \Exception("Type de source de données non supporté pour la pagination !");
    }

    public function getPages(): int
    {
        if (empty($this->dataSource)) {
            throw new \Exception("La source de données pour la pagination n'est pas définie !");
        }

        if ($this->dataSource instanceof QueryBuilder) {
            $total = (clone $this->dataSource)
                ->select('COUNT(m.id)')
                ->getQuery()
                ->getSingleScalarResult();
        } elseif (is_string($this->dataSource)) {
            $total = count($this->manager->getRepository($this->dataSource)->findAll());
        } elseif (is_array($this->dataSource)) {
            $total = count($this->dataSource);
        } else {
            throw new \Exception("Type de source de données non supporté pour la pagination !");
        }

        return ceil($total / $this->limit);
    }

    public function display(array $params = []): void
    {
        if (empty($this->route)) {
            throw new \Exception("La route de pagination n'est pas définie !");
        }
    
        // Fusionner les paramètres supplémentaires avec ceux de la pagination
        $params = array_merge([
            'page' => $this->currentPage,
            'pages' => $this->getPages(),
            'route' => $this->route
        ], $params);
    
        // Afficher le template avec les paramètres fusionnés
        $this->twig->display($this->templatePath, $params);
    }
    
}
