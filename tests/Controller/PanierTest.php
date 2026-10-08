<?php

namespace App\Tests\Controller;

use App\Entity\Commande;
use App\Entity\Medicament;
use App\Entity\PanierItem;
use App\Tests\Factory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PanierTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private Factory $factory;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine.orm.entity_manager');
        $this->factory = new Factory($this->em);
    }

    private function ajouterAuPanier(Medicament $medicament, int $quantite = 1): void
    {
        $crawler = $this->client->request('GET', '/pharmacie/'.$medicament->getCategorie()->getId().'/'.$medicament->getId());
        $form = $crawler->filter('form[action$="/panier/ajouter/'.$medicament->getId().'"]')->form();
        if ($form->has('quantite')) {
            $form['quantite'] = (string) $quantite;
        }
        $this->client->submit($form);
    }

    public function testAjoutEnGetInterdit(): void
    {
        $medicament = $this->factory->medicament();
        $this->client->request('GET', '/panier/ajouter/'.$medicament->getId());

        self::assertResponseStatusCodeSame(405);
    }

    public function testAjoutSansJetonCsrfRefuse(): void
    {
        $medicament = $this->factory->medicament();
        $this->client->request('POST', '/panier/ajouter/'.$medicament->getId());

        self::assertResponseRedirects('/panier');
        self::assertSame([], $this->client->getRequest()->getSession()->get('cart_items', []));
    }

    public function testAjoutAnonymeEnSessionPlafonneAuStock(): void
    {
        $medicament = $this->factory->medicament(stock: 3);
        $this->ajouterAuPanier($medicament, 5);

        self::assertResponseRedirects('/panier');
        self::assertSame([$medicament->getId() => 3], $this->client->getRequest()->getSession()->get('cart_items'));
    }

    public function testPaiementCreeLaCommandeEtDecrementeLeStock(): void
    {
        $medicament = $this->factory->medicament(stock: 10, prix: '4.50');
        $user = $this->factory->patient();
        $this->client->loginUser($user);

        $this->ajouterAuPanier($medicament, 2);
        $crawler = $this->client->request('GET', '/paiement');
        $this->client->submit($crawler->filter('form[action$="/paiement/payer"]')->form());

        self::assertResponseRedirects('/profil');

        $this->em->clear();
        $commandes = $this->em->getRepository(Commande::class)->findAll();
        self::assertCount(1, $commandes);
        self::assertSame('9.00', $commandes[0]->getTotal());
        self::assertCount(1, $commandes[0]->getLignesCommande());
        self::assertSame(8, $this->em->find(Medicament::class, $medicament->getId())->getStock());
        self::assertSame(0, $this->em->getRepository(PanierItem::class)->count([]));
    }

    public function testPaiementRefuseSiStockInsuffisant(): void
    {
        $medicament = $this->factory->medicament(stock: 5);
        $user = $this->factory->patient();
        $this->client->loginUser($user);

        $this->ajouterAuPanier($medicament, 5);
        $crawler = $this->client->request('GET', '/paiement');
        $form = $crawler->filter('form[action$="/paiement/payer"]')->form();

        // Le stock baisse entre l'ajout au panier et le paiement
        $this->em->find(Medicament::class, $medicament->getId())->setStock(1);
        $this->em->flush();

        $this->client->submit($form);

        self::assertResponseRedirects('/panier');
        self::assertSame(0, $this->em->getRepository(Commande::class)->count([]));
    }
}
