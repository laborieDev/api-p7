<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

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
     * @param Client $client
     * @return Client
     */
    public function createClient(Client $client)
    {
        $em = $this->getDoctrine()->getManager();
        $client->setUser($this->getUser());
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
