<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\User;
use AppBundle\Entity\Image;
use \Datetime;
use AppBundle\Controller\Action;

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

            }
        }

        // replace this example code with whatever you need
         return $this->render('default/newUser.html.twig', [
            'form' => $form->createView()
        ]);
    }


     /**
     * @Route("/all", name="AllUsers")
     */
    public function getAllUserAction()
    {
          $users = $this->getDoctrine()->getRepository(User::class)->findAll();
          foreach ($users as $user) {
               $image = $this->getDoctrine()->getRepository(Image::class)->find($user->getImageId());
                $user->setImagePath($image->getPath());
          }

          return $this->render('default/AllUser.html.twig', array('users' =>   $users));
    }
}
