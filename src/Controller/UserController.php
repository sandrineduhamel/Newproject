<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\GaleryRepository;
use App\Repository\ImagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    /**
     * @Route("/", name="home")
     */
    public function home(GaleryRepository $galeryRepository, ImagesRepository $imagesRepository)
    {
        $galery = $galeryRepository->findImages();
        $images = $imagesRepository->findAll();

        return $this->render('homepage.html.twig', [

            'galery'=> $galery,
            'images'=> $images
        ]);
    }
    
    /**
     * @Route("contact", name="contact")
     */
    public function contact(EntityManagerInterface $entityManager, Request $request, \Swift_Mailer $mailer){
        $contact = new Contact();

        $form = $this->createForm(ContactType::class, $contact);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $message = (new \Swift_Message('Nouveau message'))
                    ->setFrom($contact->getEmail())
                    ->setTo('sandrine.duhamel@lapiscine.pro')
                    ->setBody(
                        $this->renderView(
                            'contact/_mail.html.twig', [
                                'prenom' => $contact->getPrenom(),
                                'nom' => $contact->getNom(),
                                'message' => $contact->getMessage()
                            ]
                        ),
                        'text/html'
                    );

                $mailer->send($message);

                $entityManager->persist($contact);
                $entityManager->flush();

                $this->addFlash('success', 'Votre message a bien été envoyé, merci ! Nous y répondrons dès que possible.');

                return $this->redirect($request->getUri());

            } else {

                $this->addFlash('fail', 'Votre message n\'a pas pu être envoyé.');

                return $this->render('contact/contact.html.twig', [
                    'ContactForm' => $form->createView()
                ]);
            }
        }

        return $this->render('homepage.html.twig', [
            'ContactForm' => $form->createView()
        ]);
    }
}
