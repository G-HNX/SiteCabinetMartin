<?php

namespace App\Controller;

use App\Service\PanierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class PanierController extends AbstractController
{
    /** @var array<int, array<string, mixed>> */
    private array $categories = [
        ['id' => 1, 'nom' => 'Antibiotiques', 'description' => 'Contre les infections bactériennes.'],
        ['id' => 2, 'nom' => 'Antalgiques', 'description' => 'Réduisent la douleur.'],
        ['id' => 3, 'nom' => 'Anti-inflammatoires', 'description' => 'Réduisent inflammation et douleur.'],
    ];

    /** @var array<int, array<string, mixed>> */
    private array $medicaments = [
        // Cat. 1
        ['id'=>101,'nom'=>'Amoxicilline','forme'=>'Gélule','dosage'=>'500 mg','prix'=>4.50,'description'=>'Lorem ipsum...','image'=>'img/meds/amoxicilline.jpg','stock'=>12,'cat_id'=>1],
        ['id'=>102,'nom'=>'Clarithromycine','forme'=>'Comprimé','dosage'=>'250 mg','prix'=>7.90,'description'=>'Lorem ipsum...','image'=>'img/meds/clarithromycine.jpg','stock'=>5,'cat_id'=>1],
        // Cat. 2
        ['id'=>201,'nom'=>'Paracétamol','forme'=>'Comprimé','dosage'=>'500 mg','prix'=>2.10,'description'=>'Lorem ipsum...','image'=>'img/meds/paracetamol.jpg','stock'=>0,'cat_id'=>2],
        ['id'=>202,'nom'=>'Codéine','forme'=>'Comprimé','dosage'=>'30 mg','prix'=>5.60,'description'=>'Lorem ipsum...','image'=>'img/meds/codeine.jpg','stock'=>8,'cat_id'=>2],
        // Cat. 3
        ['id'=>301,'nom'=>'Ibuprofène','forme'=>'Comprimé','dosage'=>'400 mg','prix'=>3.20,'description'=>'Lorem ipsum...','image'=>'img/meds/ibuprofene.jpg','stock'=>15,'cat_id'=>3],
        ['id'=>302,'nom'=>'Naproxène','forme'=>'Comprimé','dosage'=>'500 mg','prix'=>6.30,'description'=>'Lorem ipsum...','image'=>'img/meds/naproxene.jpg','stock'=>3,'cat_id'=>3],
    ];

    public function findOne(int $id): ?array
    {
        foreach ($this->medicaments as $m) {
            if ($m['id'] === $id) return $m;
        }
        return null;
    }

    #[Route('/panier', name: 'app_panier', methods: ['GET'])]
    public function index(PanierService $cart): Response
    {
        $data = $cart->detailed($this->medicaments);
        return $this->render('panier/index.html.twig', $data);
    }

    #[Route('/panier/ajouter/{id}', name: 'app_panier_add', requirements: ['id' => '\d+'], methods: ['POST','GET'])]
    public function add(int $id, Request $request, PanierService $cart): Response
    {
        // Ajoute ou incrémente la quantité
        $cart->add($id);
        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/diminuer/{id}', name: 'app_panier_decrease', requirements: ['id' => '\d+'], methods: ['POST','GET'])]
    public function decrease(int $id, PanierService $cart): Response
    {
        // Diminue la quantité de 1
        $cart->decrease($id);
        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/retirer/{id}', name: 'app_panier_remove', requirements: ['id' => '\d+'], methods: ['POST','GET'])]
    public function remove(int $id, PanierService $cart): Response
    {
        // Supprime complètement l’élément
        $cart->remove($id);
        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/vider', name: 'app_panier_clear', methods: ['POST','GET'])]
    public function clear(PanierService $cart): Response
    {
        // Vide le panier
        $cart->clear();
        return $this->redirectToRoute('app_panier');
    }
}