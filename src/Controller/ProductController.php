<?php

namespace App\Controller;

use App\Entity\Product;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;

class ProductController extends AbstractFOSRestController
{
    private $entityManager;
    private $serialize;

    /**
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serialize
     */
    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serialize)
    {
        $this->entityManager = $entityManager;
        $this->serialize = $serialize;
    }

    /**
     * @Rest\Get("/api/products/{id}")
     * @View(serializerGroups={"detail"})
     * 
     * @param Product $product
     * @return Product $product
     */
    public function getProduct(Product $product)
    {
        return $product;
    }


    /**
     * @Rest\Get("/api/products")
     * @View()
     * 
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Object
     */
    public function getProductsList(Request $request, PaginatorInterface $paginator)
    {
        $nbPage = intval($request->get('page'));
        
        if($nbPage == null) {
            $nbPage = 1;
        }

        $allDatas = $this->entityManager->getRepository(Product::class)->findAll();
        $allDatas = $this->serialize->serialize($allDatas, 'json', SerializationContext::create()->setGroups(array('list')));
        $allDatas = $this->serialize->deserialize($allDatas, 'array', 'json');

        $data = $paginator->paginate(
            $allDatas,
            $nbPage,
            10
        );

        return $data;
    }
}
