<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RegistrationTest extends WebTestCase
{
    private function soumettre(string $email): void
    {
        $client = static::getClient();
        $crawler = $client->request('GET', '/inscription');
        $form = $crawler->filter('form[name="registration_form"]')->form([
            'registration_form[nom]' => 'Lambert',
            'registration_form[prenom]' => 'Sophie',
            'registration_form[email]' => $email,
            'registration_form[plainPassword]' => 'motdepasse',
        ]);
        $form['registration_form[agreeTerms]']->tick();
        $client->submit($form);
    }

    public function testInscriptionCreeUnPatient(): void
    {
        static::createClient();
        $this->soumettre('nouveau@test.fr');

        self::assertResponseRedirects();
        $user = static::getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)->findOneBy(['email' => 'nouveau@test.fr']);
        self::assertNotNull($user?->getPersonne()?->getPatient());
    }

    public function testEmailDejaUtiliseAfficheUneErreur(): void
    {
        static::createClient();
        (new Factory(static::getContainer()->get('doctrine.orm.entity_manager')))->patient('pris@test.fr');

        $this->soumettre('pris@test.fr');

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('body', 'Cette adresse email est déjà utilisée.');
    }
}
