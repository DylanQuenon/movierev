<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Symfony\Component\Mime\Email;
use Symfony\Component\Dotenv\Dotenv;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;



class ContactController extends AbstractController
{
    /**
     * Page contact
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param MailerInterface $mailer
     * @return Response
     */
    #[Route('/contact', name: 'contact')]
    public function index(Request $request, EntityManagerInterface $manager, MailerInterface $mailer): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($contact);
            $manager->flush();

            $email = (new Email())
                ->from('contact@laligzz.dylanquenon.com')  // E-mail de l'expéditeur
                ->to('dylan.quenon.04@gmail.com')  // Utilisation de l'e-mail du destinataire récupéré depuis .env
                ->replyTo($contact->getMail())
                ->subject($contact->getObject())
                ->html("
                <html>
                    <body>
                        <h1>Titre de l'e-mail</h1>
                        <p>{$contact->getMessage()}</p>
                    </body>
                </html>
            ");
        
            // Envoi de l'e-mail
            $mailer->send($email);

            $this->addFlash('success', 'Votre message a bien été envoyé et nous vous répondrons sous peu.');
            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
