<?php

namespace App\Controller;

use App\Entity\User;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
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

class SecurityController extends AbstractFOSRestController
{
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Rest\Post("/api/login")
     * 
     * @OA\Response(
     *     response=200,
     *     description="Retourne les informations sur un produit. Prix en euros (€)",
     * )
     * 
     * @OA\Parameter(
     *     name="username",
     *     in="query",
     *     description="Votre adresse email",
     *     @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *     name="password",
     *     in="query",
     *     description="Votre mot de passe",
     *     @OA\Schema(type="string")
     * )
     * 
     * @OA\Tag(name="Identification")
     */
    public function login()
    {

    }
}
