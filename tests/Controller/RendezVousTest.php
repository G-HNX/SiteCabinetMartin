<?php

namespace App\Tests\Controller;

use App\Entity\Medecin;
use App\Entity\RendezVous;
use App\Tests\Factory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RendezVousTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private Medecin $medecin;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine.orm.entity_manager');
        $factory = new Factory($this->em);
        $this->medecin = $factory->medecin();
        $this->client->loginUser($factory->patient());
    }

    private function reserver(string $slot, string $motif = 'Fièvre'): void
    {
        $crawler = $this->client->request('GET', '/reserver/'.$this->medecin->getId().'/'.$slot);
        $form = $crawler->filter('form[action$="/validation/'.$this->medecin->getId().'/'.$slot.'"]')->form();
        $form['motif'] = $motif;
        $this->client->submit($form);
    }

    private function creneauFutur(): string
    {
        return (new \DateTimeImmutable('monday next week 10:00'))->format('Y-m-d\TH:i');
    }

    public function testReservationCreeLeRendezVous(): void
    {
        $this->reserver($this->creneauFutur());

        self::assertResponseRedirects('/profil');
        $rdv = $this->em->getRepository(RendezVous::class)->findOneBy([]);
        self::assertNotNull($rdv);
        self::assertSame('Fièvre', $rdv->getCommentaireRDV());
        self::assertSame('10:30', $rdv->getDateFinRDV()->format('H:i'));
    }

    public function testDoubleReservationImpossible(): void
    {
        $slot = $this->creneauFutur();
        $this->reserver($slot);
        $this->reserver($slot);

        self::assertSame(1, $this->em->getRepository(RendezVous::class)->count([]));
    }

    public function testCreneauReserveAfficheIndisponible(): void
    {
        $slot = $this->creneauFutur();
        $this->reserver($slot);

        // Affiche la grille à partir de lundi prochain
        $offset = (new \DateTimeImmutable('today'))->diff(new \DateTimeImmutable('monday next week'))->days;
        $crawler = $this->client->request('GET', '/rendezvous/set/'.$offset);
        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('a[href$="/reserver/'.$this->medecin->getId().'/'.$slot.'"]'));
    }

    public function testCreneauPasseOuHorsHorairesRefuse(): void
    {
        foreach (['2020-01-06T10:00', (new \DateTimeImmutable('monday next week 20:00'))->format('Y-m-d\TH:i')] as $slot) {
            $this->client->request('GET', '/reserver/'.$this->medecin->getId().'/'.$slot);
            self::assertResponseRedirects('/rendezvous');
        }
    }

    public function testValidationEnGetInterdite(): void
    {
        $this->client->request('GET', '/validation/'.$this->medecin->getId().'/'.$this->creneauFutur());

        self::assertResponseStatusCodeSame(405);
    }
}
