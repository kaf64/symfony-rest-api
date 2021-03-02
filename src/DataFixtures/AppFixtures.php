<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
		$user=new User();
		$user->setUsername("admin");
		$plain_password="admin";
        $encoded_password = $this->encoder->encodePassword($user,$plain_password);
        $user->setPassword($encoded_password);
		try{
		$manager->persist($user);
        $manager->flush();
		}catch(Doctrine\ORM\ORMException $exception){
			echo ("There is a problem: " . $exception);
		}

    }
}
