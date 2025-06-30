<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Partner;
use App\Form\RegistrationForm;
use App\Security\SecurityControllerAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RegistrationForm::class, null, [
            'allow_extra_fields' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $type = $form->get('type')->getData();

            if ($type === 'client') {
                // Validation côté serveur pour client
                if (empty($form->get('firstName')->getData()) || empty($form->get('lastName')->getData())) {
                    $this->addFlash('error', 'Le prénom et le nom sont obligatoires pour un client.');
                    return $this->render('registration/register.html.twig', [
                        'registrationForm' => $form,
                    ]);
                }
                $user = new Client();
                $user->setRoles(['ROLE_CLIENT']);
                $user->setFirstName($form->get('firstName')->getData());
                $user->setLastName($form->get('lastName')->getData());
            } else {
                // Validation côté serveur pour partenaire
                if (empty($form->get('companyName')->getData())) {
                    $this->addFlash('error', 'Le nom de l\'entreprise est obligatoire pour un partenaire.');
                    return $this->render('registration/register.html.twig', [
                        'registrationForm' => $form,
                    ]);
                }
                $user = new Partner();
                $user->setRoles(['ROLE_PARTNER']);
                $user->setCompanyName($form->get('companyName')->getData());
            }

            $user->setEmail($form->get('email')->getData());

            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            return $security->login($user, SecurityControllerAuthenticator::class, 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
