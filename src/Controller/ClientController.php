<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Form\ClientType;
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

use Symfony\Component\HttpKernel\Exception\HttpException;
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
     *     description="Return all informations of an user.",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Client::class, groups={"detail"}))
     *     )
     * )
     * 
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="User ID",
     *     @OA\Schema(type="int")
     * )
     * 
     * @OA\Tag(name="Users")
     * 
     * @param Client $client
     * @return Response
     */
    public function getClient(Client $client)
    {
        return $client;
    }


    /**
     * @Rest\Get("/api/user/{id}/clients")
     * @View()
     * 
     *@OA\Response(
     *     response=200,
     *     description="Return your list of users.",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Client::class, groups={"list"}))
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
     *     description="Number of the page (10 users per page)",
     *     @OA\Schema(type="int")
     * )
     * 
     * @OA\Tag(name="Users")
     * 
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Object
     */
    public function getClientsList(User $user, Request $request, PaginatorInterface $paginator)
    {
        if ($user != $this->getUser()) {
            throw new HttpException(403, "Access denied");
        }

        $nbPage = intval($request->get('page'));

        if ($nbPage == null) {
            $nbPage = 1;
        }

        $cacheName = 'all_clients_list_'.$user->getId().'_'.$nbPage;
        $cache = new FilesystemAdapter();

        $values = $cache->get($cacheName, function (ItemInterface $item) use ($paginator, $nbPage, $user) {
            $item->expiresAfter(3600);
        
            $allDatas = $this->entityManager->getRepository(Client::class)->findBy([
                'user' => $user
            ]);
            
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

    /**
     * @Rest\Post("/api/user/{id}/clients")
     * @View(statusCode=201)
     * @ParamConverter("client", converter="fos_rest.request_body")
     * 
     * @OA\Response(
     *     response=201,
     *     description="Create an user. Return saved data.",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Client::class, groups={"list"}))
     *     )
     * )
     * 
     * @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="User's name",
     *     @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *     name="email",
     *     in="query",
     *     description="User's email",
     *     @OA\Schema(type="email")
     * )
     * 
     * @OA\Tag(name="Users")
     *      
     * @param Client $client
     * @return Client
     */
    public function createClient(User $user, Client $client, ValidatorInterface $validator)
    {
        if ($user != $this->getUser()) {
            throw new Exception('Acces denied');
        }

        $em = $this->getDoctrine()->getManager();
        $client->setUser($user);
        $error = $validator->validate($client);

        if(sizeof($error) != 0){
            return $error;
        }

        $em->persist($client);
        $em->flush();

        return $client;
    }

    /**
     * @Rest\Put("/api/clients/{id}")
     * @View(serializerGroups={"detail"})
     * @ParamConverter("newDatas", converter="fos_rest.request_body")
     * 
     * @Security("client.isUserClient(user)")
     * 
     * @OA\Response(
     *     response=200,
     *     description="Update an user. Return saved data.",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Client::class, groups={"detail"}))
     *     )
     * )
     * 
     * @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="User's name",
     *     @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *     name="email",
     *     in="query",
     *     description="User's email",
     *     @OA\Schema(type="email")
     * )
     * 
     * @OA\Tag(name="Users")
     *      
     * @param Client $client
     * @return Client
     */
    public function updateClient(Client $client, Client $newDatas, ValidatorInterface $validator)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(ClientType::class, $client, ['client' => $client]);
        $newDatas = $newDatas->convertToArray();
      
        $form->submit($newDatas);

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
     * @OA\Tag(name="Users")
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
