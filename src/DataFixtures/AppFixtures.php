<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Product;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;
 
    public function __construct(UserPasswordEncoderInterface $encoder) {
        $this->encoder = $encoder;
    }
    
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setName('Orange France');
        $user->setEmail("orange@orange.fr");
        $password = "clientpassword";
        $encoded = $this->encoder->encodePassword($user, $password);
        $user->setPassword($encoded);
        $manager->persist($user);

        $manager->flush();

        for($i = 0; $i < 30; $i++){
            $client = new Client();
            $client->setName('Client '.$i);
            $client->setUser($user);
            $client->setEmail('client'.$i.'@email.fr');
            $manager->persist($client);

            $product = new Product();
            $product->setName('Product '.$i);
            $randomPrice = rand(1000, 10000) / 10;
            $product->setUnitPrice($randomPrice);

            if($i % 2 == 0){
                $product->addUser($user);
            }

            $manager->persist($product);
        }

        $manager->flush();
    }
}
