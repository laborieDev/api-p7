<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Product;
use OpenApi\Annotations as OA;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Contracts\Cache\ItemInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

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
     * @Security("product.isUserProduct(user)")
     * 
     * @OA\Response(
     *     response=200,
     *     description="Return all informations of a product. Price in euros (€)",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class, groups={"detail"}))
     *     )
     * )
     * 
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Product ID.",
     *     @OA\Schema(type="int")
     * )
     * 
     * @OA\Tag(name="Products")
     * 
     * @param Product $product
     * @return Product $product
     */
    public function getProduct(Product $product)
    {
        return $product;
    }


    /**
     * @Rest\Get("/api/user/{id}/products")
     * @View()
     * 
     * @OA\Response(
     *     response=200,
     *     description="Return your product list. Price in euros (€)",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class, groups={"list"}))
     *     )
     * )
     * 
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Your client ID",
     *     @OA\Schema(type="int")
     * )
     * 
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Number of page (10 products per page)",
     *     @OA\Schema(type="int")
     * )
     * 
     * @OA\Tag(name="Products")
     * 
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Object
     */
    public function getProductsList(User $user, Request $request, PaginatorInterface $paginator)
    {
        if ($user != $this->getUser()) {
            throw new Exception('Acces denied');
        }

        $nbPage = (int) $request->get('page');
        
        if ($nbPage == null) {
            $nbPage = 1;
        }

        $cacheName = 'all_products_list_'.$user->getId().'_'.$nbPage;
        $cache = new FilesystemAdapter();

        /* Cached datas */
        $values = $cache->get($cacheName, function (ItemInterface $item) use ($paginator, $nbPage, $user) {
            $item->expiresAfter(3600);
        
            $allDatas = $this->entityManager->getRepository(Product::class)->getUserProducts($this->getUser());
            $allDatas = $this->serialize->serialize($allDatas, 'json', SerializationContext::create()->setGroups(array('list')));
            $allDatas = $this->serialize->deserialize($allDatas, 'array', 'json');

            $data = $paginator->paginate(
                $allDatas,
                $nbPage,
                10
            );
        
            return $data;
        });

        return $values;
    }
}
