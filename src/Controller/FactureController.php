<?php

namespace App\Controller;

use App\Entity\Commande;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FactureController extends AbstractController
{
    #[Route('/facture', name: 'app_facture')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $commandes = $em->getRepository(Commande::class)->findBy(
            ['personne' => $user->getPersonne()],
            ['dateCommande' => 'DESC']
        );

        return $this->render('facture/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/facture/{id}/pdf', name: 'app_facture_pdf')]
    public function pdf(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $commande = $em->getRepository(Commande::class)->find($id);

        if (!$commande || $commande->getPersonne() !== $user->getPersonne()) {
            throw $this->createNotFoundException('Facture introuvable.');
        }

        $html = $this->renderView('facture/pdf.html.twig', [
            'commande' => $commande,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'facture-' . $commande->getId() . '-' . $commande->getDateCommande()->format('Y-m-d') . '.pdf';

        return new Response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
