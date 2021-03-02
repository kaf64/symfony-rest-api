<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="registration")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {

        $entityManager=$this->getDoctrine()->getManager();
        $user=new User();

        $form=$this->createFormBuilder($user)
        ->add('username', TextType::class, array('attr'=>array('class'=>'form-control')))
        ->add('plain_password', RepeatedType::class, array(
            'row_attr'=>array('class'=>'form-control'),
            'type' => PasswordType::class,
            'mapped'=>false,
            'first_options'  => array('label' => 'Enter password for user','attr'=>array('class'=>'form-control')),
            'second_options' => array('label' => 'Repeat password','attr'=>array('class'=>'form-control')),
        ))
        ->add("save", SubmitType::class,array('attr'=>array('class'=>'btn btn-block btn-primary mt-3'),'label'=>'Register new user'))
        ->getForm();


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $plain_password=$form->get("plain_password")->getData();
            $encoded_password = $passwordEncoder->encodePassword($user,$plain_password);
            $user->setPassword($encoded_password);
            
            try{
                $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            }
            catch(Doctrine\ORM\ORMException $exception){

            }            
            
            return $this->redirectToRoute('app_login');
            }

        return $this->render('registration/register.html.twig',array('form' => $form->createView()));

    }
}
