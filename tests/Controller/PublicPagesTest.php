<?php

namespace App\Tests\Controller;

use App\Tests\Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PublicPagesTest extends WebTestCase
{
    public static function publicUrls(): iterable
    {
        foreach (['/', '/about', '/medecin', '/pharmacie', '/rendezvous', '/teleconsultation', '/contact', '/politique', '/panier', '/login', '/inscription'] as $url) {
            yield $url => [$url];
        }
    }

    #[DataProvider('publicUrls')]
    public function testPagePubliqueAccessible(string $url): void
    {
        $client = static::createClient();
        (new Factory(static::getContainer()->get('doctrine.orm.entity_manager')))->medecin();

        $client->request('GET', $url);

        self::assertResponseIsSuccessful();
    }

    public static function privateUrls(): iterable
    {
        foreach (['/profil', '/facture', '/paiement'] as $url) {
            yield $url => [$url];
        }
    }

    #[DataProvider('privateUrls')]
    public function testPagePriveeRedirigeVersLogin(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        self::assertResponseRedirects('/login');
    }

    public function testPagesPharmacieEtMedecinsAfficheLesDonnees(): void
    {
        $client = static::createClient();
        $factory = new Factory(static::getContainer()->get('doctrine.orm.entity_manager'));
        $medicament = $factory->medicament();
        $factory->medecin();

        $client->request('GET', '/pharmacie/'.$medicament->getCategorie()->getId().'/'.$medicament->getId());
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Paracétamol');

        $client->request('GET', '/medecin');
        self::assertSelectorTextContains('body', 'Dr Claire Dubois');
    }

    public function testProduitInexistantRenvoie404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/pharmacie/1/999999');

        self::assertResponseStatusCodeSame(404);
    }
}
