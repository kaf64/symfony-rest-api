<?php

namespace App\Controller;

use App\Entity\User;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

use \Doctrine\DBAL\DBALException;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


use Symfony\Component\Serializer\Encoder\XmlEncoder;


use Symfony\Component\HttpFoundation\Response;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/user", name="api_index", methods={"GET"})
     */
    public function index(Request $request)
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
      
        $repo=$this->getDoctrine()->getRepository(User::class);
        $users = $repo->findAll();
        
        $data=$serializer->normalize($users,null,['attributes'=>['id','username','password','roles']]);
        $response = new Response(
            $serializer->serialize($data, 'json'),
           // $this->json($users),
            Response::HTTP_OK,
            ['Content-type' => 'application/json']
        );

        return $response;
    }    

    /**
     * @Route("/api/user/{id}", name="api_get_user", methods={"GET"})
     */
    public function getSpecificUser(Request $request, $id)
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
      
        $repo = $this->getDoctrine()->getRepository(User::class);
        $user = $repo->find($id);
        
        if($user){
            $data = $serializer->normalize($user,null,['attributes'=>['id','username','password','roles']]);
            $response = new Response(
                $serializer->serialize($data, 'json'),
                Response::HTTP_OK,
                ['Content-type' => 'application/json']
                );
    
            return $response;
        }else{
            $data=array(
                'status'=>'User not found'
            );
            $response= new Response(
                $serializer->serialize($data, 'json'),
                Response::HTTP_NOT_FOUND,
                ['Content-type' => 'application/json']
                );
    
            return $response;

        }
        
    }    
	
    /**
     * @Route("/api/user", name="api_add_post", methods={"POST"})
     */
    public function addUser(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $content=$request->getContent();
        $receivedJson=json_decode($content, true);
        if(isset($receivedJson[0]))$receivedJson=$receivedJson[0];
        if(empty($receivedJson['username'])||empty($receivedJson['password'])){
            $res=array(
                'status'=>'error',
                'message'=>'incomplete data',
                );
            $response= new Response(
                $serializer->serialize($res, 'json'),
                Response::HTTP_BAD_REQUEST,
                ['Content-type' => 'application/json']
                );
            return $response;
        }
        $new_user=new User();
        $plain_password=$receivedJson['password'];
        $new_user->setUsername($receivedJson['username']);
        $encoded_password = $passwordEncoder->encodePassword($new_user,$plain_password);
        $new_user->setPassword($encoded_password);
        
        $repo=$this->getDoctrine()->getRepository(User::class);
        $entityManager=$this->getDoctrine()->getManager();
        
        $entityManager->getConnection()->beginTransaction();
        try{
        $entityManager->persist($new_user);
        $entityManager->flush();
        $entityManager->getConnection()->commit();
        }catch(UniqueConstraintViolationException  $e){
            $entityManager->getConnection()->rollBack();
            $res['status']='error';
            $res['error_message']="Can't create user - login incorrect, use different login and try again";
            $response= new Response(
                $serializer->serialize($res, 'json'),
                Response::HTTP_BAD_REQUEST,
                ['Content-type' => 'application/json']
                );
            return $response;
        }catch(DBALException $e){
            $entityManager->getConnection()->rollBack();
            $res['status']='error';
            $res['error_message']=$e->getMessage();
            
            $response= new Response(
                $serializer->serialize($res, 'json'),
                Response::HTTP_BAD_REQUEST,
                ['Content-type' => 'application/json']
                );
                return $response;
            }
            $response= new Response(
                $serializer->serialize(array("status"=>"User created"), 'json'),
                Response::HTTP_CREATED,
                ['Content-type' => 'application/json']
            );

    return $response;            
    }

    /**
     * @Route("/api/user/{id}", name="api_edit_user", methods={"PUT"})  
     */
    public function editUser(Request $request, $id, UserPasswordEncoderInterface $passwordEncoder){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
         $serializer = new Serializer($normalizers, $encoders);
        
        $content=$request->getContent();
        $receivedJson=json_decode($content,true);
        if(isset($receivedJson[0]))$receivedJson=$receivedJson[0];
        $repo=$this->getDoctrine()->getRepository(User::class);
        $user=$repo->find($id);

        if($user){
        if(isset($receivedJson['username']) && $receivedJson['username']) $user->setUsername($receivedJson['username']);
        if(isset($receivedJson['password'])&& $receivedJson['password']){
            $encoded_password = $passwordEncoder->encodePassword($user,$receivedJson['username']);
            $user->setPassword($encoded_password);
        }
        
        $entityManager=$this->getDoctrine()->getManager();
        $entityManager->getConnection()->beginTransaction();
        try{
        $entityManager->flush();
        $entityManager->getConnection()->commit();
        }catch(UniqueConstraintViolationException  $e){
            $entityManager->getConnection()->rollBack();
            $res['status']='error';
            $res['error_message']="Can't edit user - new login incorrect, use different login and try again";
            $response= new Response(
                $serializer->serialize($res, 'json'),
                Response::HTTP_BAD_REQUEST,
                ['Content-type' => 'application/json']
            );
            return $response;
        }catch(DBALException $e){
            $entityManager->getConnection()->rollBack();
            $res['status']='error';
            $res['error_message']=$e->getMessage();
            
            $response= new Response(
                $serializer->serialize($res, 'json'),
                Response::HTTP_BAD_REQUEST,
                ['Content-type' => 'application/json']
                );
            return $response;
        }
        $res['status']='User successfully edited';
        $response= new Response(
            $serializer->serialize($res, 'json'),
            Response::HTTP_OK,
            ['Content-type' => 'application/json']
            );
        return $response;

    }else{
        $res=array(
            'status'=>"Cannot edit - user not found"
        );
        $response= new Response(
            $serializer->serialize($res, 'json'),
            Response::HTTP_NOT_FOUND,
            ['Content-type' => 'application/json']
        );
        return $response;
    }
    }

     /**
     * @Route("/api/user/{id}", name="api_delete_post", methods={"DELETE"})
     */
    public function deleteUser($id){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        
        $repo=$this->getDoctrine()->getRepository(User::class);
        $user=$repo->find($id);

        if($user){
            $manager=$this->getDoctrine()->getManager();
            $manager->remove($user);
            $manager->flush();
    
            $res=array(
                'status'=>"User deleted"
            );
            $response= new Response(
                $serializer->serialize($res, 'json'),
                Response::HTTP_OK,
                ['Content-type' => 'application/json']
            );            
        return $response;
        }else{
            $res=array(
                'status'=>"Cannot delete - user not found"
            );
            $response= new Response(
                $serializer->serialize($res, 'json'),
                Response::HTTP_NOT_FOUND,
                ['Content-type' => 'application/json']
            );
            return $response;
        }
    
    }
}
