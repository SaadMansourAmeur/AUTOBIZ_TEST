<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\User;
use AppBundle\Entity\Image;
use \Datetime;
use AppBundle\Controller\Action;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
         return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }


    /**
     * @Route("/new", name="newUser")
     */
    public function newUserAction(Request $request)
    {
     
        // Create the form according to the FormType created previously.
        // And give the proper parameters
        $form = $this->createForm('AppBundle\Form\UserType',null,array(
            // To set the action use $this->generateUrl('route_identifier')
            'action' => $this->generateUrl('newUser'),
            'method' => 'POST'
        ));

        if ($request->isMethod('POST')) {
            // Refill the fields in case the form is not valid.
            $form->handleRequest($request);

            if($form->isValid()){
                $entityManager = $this->getDoctrine()->getManager();
                $user = new User();
                $image = new Image();

                $data = $form->getData();

                $user->setGender($data['gender']);
                $user->setFirstname($data['firstname']);
                $user->setLastname($data['lastname']);
                $user->setBirthDate($data['birthDate']);
                $user->setCreatedAt(new Datetime('now'));

                $image->setPath($data['imageId']);

                // tell Doctrine you want to (eventually) save the Product (no queries yet)
                $entityManager->persist($image);
                // actually executes the queries (i.e. the INSERT query)
                $entityManager->flush();
            
                $user->setImageId($image->getId());

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('allUsers');
            }
        }

        // replace this example code with whatever you need
         return $this->render('default/newUser.html.twig', [
            'form' => $form->createView()
        ]);
    }


     /**
     * @Route("/all", name="allUsers")
     */
    public function getAllUserAction()
    {
          $users = $this->getDoctrine()->getRepository(User::class)->findAll();
          foreach ($users as $user) {
               $image = $this->getDoctrine()->getRepository(Image::class)->find($user->getImageId());
                $user->setImagePath($image->getPath());
          }

          return $this->render('default/allUser.html.twig', array('users' =>   $users));
    }



    /**
     * @Route("/update", name="updateUser")
     */
    public function updateUserAction(Request $request)
    {
        $formUpdate = $this->createForm('AppBundle\Form\UserType',null,array(
                    // To set the action use $this->generateUrl('route_identifier')
                    'action' => $this->generateUrl('updateUser'),
                    'method' => 'POST'
        )); 

        $session = new Session();
        
        if ($request->isMethod('POST')) {

            // Step 1  get user by id
            if ($request->request->get('userId') != null) {
                $user = $this->getDoctrine()->getRepository(User::class)->find($request->request->get('userId'));

            
                $image = $this->getDoctrine()->getRepository(Image::class)->find($user->getImageId());
                $user->setImagePath($image->getPath());

                $formUpdate = $this->createForm('AppBundle\Form\UserType',$user,array(
                            // To set the action use $this->generateUrl('route_identifier')
                            'action' => $this->generateUrl('updateUser'),
                            'method' => 'POST'
                ));
                //$session->start();
                $session->set('userId', $request->request->get('userId'));
                $session->set('imageId', $user->getImageId());
            }

            // Step 2 update user
            $formUpdate->handleRequest($request);

            if($formUpdate->isValid()){
                $user = new User();
                $image = new Image();
                $data = $formUpdate->getData();


                // Get connections
                $entityManager = $this->getDoctrine()->getManager();


                // Write your raw SQL
                $rawQuery1 = "update image SET path = '" . $data['imageId'] . "' where id = " . $session->get('imageId');

                // Prepare the query from DATABASE1
                $statementDB1 = $entityManager->getConnection()->prepare($rawQuery1);

                // Execute both queries
                $statementDB1->execute();

                $rawQuery2 = "update user 
                                SET gender = " . $data['gender'] . ", 

                                firstname = '" . $data['firstname'] . "',

                                lastname = '" . $data['lastname'] . "',

                                birthDate = '" . $data['birthDate']->format('Y-m-d H:i:s') . "'

                                where id = " . $session->get('userId');

                $statementDB2 = $entityManager->getConnection()->prepare($rawQuery2);
                
                $statementDB2->execute();

                return $this->redirectToRoute('allUsers');
            }
               
        }

         return $this->render('default/updateUser.html.twig', array('user' => $user, 'form' => $formUpdate->createView()));
    }


     /**
     * @Route("/delete/{id}", name="deleteUser")
     */
    public function deleteUserAction($id, Request $request)
    {
        if($id != null){
            $em = $this->getDoctrine()->getEntityManager();
            $user = $this->getDoctrine()->getRepository(User::class)->find($id);

            $em->remove($user);
            $em->flush();
        }
        return $this->redirectToRoute('allUsers');   
    }
}
