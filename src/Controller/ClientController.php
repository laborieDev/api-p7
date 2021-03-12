<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use OpenApi\Annotations as OA;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Model;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ClientController extends AbstractFOSRestController
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
     * @Rest\Get("/api/clients/{id}")
     * @View(serializerGroups={"detail"})
     * 
     * @Security("client.isUserClient(user)")
     * 
     * @OA\Response(
     *     response=200,
     *     description="Retourne les informations sur un utilisateur",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Client::class, groups={"detail"}))
     *     )
     * )
     * 
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Identifiant du client",
     *     @OA\Schema(type="int")
     * )
     * 
     * @OA\Tag(name="Utilisateurs")
     * 
     * @param Client $client
     * @return Response
     */
    public function getClient(Client $client)
    {
        return $client;
    }


    /**
     * @Rest\Get("/api/clients")
     * @View()
     * 
     *@OA\Response(
     *     response=200,
     *     description="Retourne la liste de vos utilisateurs",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Client::class, groups={"list"}))
     *     )
     * )
     * 
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Numéro de la page (10 utilisateurs par page)",
     *     @OA\Schema(type="int")
     * )
     * 
     * @OA\Tag(name="Utilisateurs")
     * 
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Object
     */
    public function getClientsList(Request $request, PaginatorInterface $paginator)
    {
        $nbPage = intval($request->get('page'));

        if($nbPage == null) {
            $nbPage = 1;
        }

        $allDatas = $this->entityManager->getRepository(Client::class)->findBy([
            "user" => $this->getUser()
        ]);
        
        $allDatas = $this->serialize->serialize($allDatas, 'json', SerializationContext::create()->setGroups(array('list')));
        $allDatas = $this->serialize->deserialize($allDatas, 'array', 'json');

        $data = $paginator->paginate(
            $allDatas,
            $nbPage,
            10
        );

        return $data;
    }

    /**
     * @Rest\Post("/api/clients")
     * @View(statusCode=201)
     * @ParamConverter("client", converter="fos_rest.request_body") 
     * 
     * @OA\Response(
     *     response=201,
     *     description="Création d'un utilisateur. Retourne les données sauvegardées.",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Client::class, groups={"list"}))
     *     )
     * )
     * 
     * @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Nom de l'utilisateur",
     *     @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *     name="email",
     *     in="query",
     *     description="Email de l'utilisateur",
     *     @OA\Schema(type="email")
     * )
     * 
     * @OA\Tag(name="Utilisateurs")
     *      
     * @param Client $client
     * @return Client
     */
    public function createClient(Client $client, ValidatorInterface $validator)
    {
        $em = $this->getDoctrine()->getManager();
        $client->setUser($this->getUser());
        $error = $validator->validate($client);

        if(sizeof($error) != 0){
            return $error;
        }

        $em->persist($client);
        $em->flush();

        return $client;
    }

    /**
     * @Rest\Delete("/api/clients/{id}")
     * @View(statusCode=204) 
     * 
     * @Security("client.isUserClient(user)")
     * 
     * @OA\Response(
     *     response=204,
     *     description="Suppression d'un utilisateur."
     * )
     * 
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id de l'utilisateur",
     *     @OA\Schema(type="int")
     * )
     * 
     * @OA\Tag(name="Utilisateurs")
     * 
     * @param Client $client
     */
    public function deleteClient(Client $client)
    {
        $this->entityManager->flush();
        $em = $this->getDoctrine()->getManager();
        $em->remove($client);
        $em->flush();
    }
}
