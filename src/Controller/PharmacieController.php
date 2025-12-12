<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PharmacieController extends AbstractController
{
    
    /** @var array<int, array<string, mixed>> */
 private array $categories = [
 ['id' => 1, 'nom' => 'Antibiotiques', 'description' => 'Contre les
infections bactériennes.'],
 ['id' => 2, 'nom' => 'Antalgiques', 'description' => 'Réduisent la
douleur.'],
 ['id' => 3, 'nom' => 'Anti-inflammatoires', 'description' => 'Réduisent
inflammation et douleur.'],
 ];
    #[Route('/pharmacie', name: 'app_pharmacie')]
    public function index(): Response
    {
        return $this->render('pharmacie/index.html.twig', [
            'controller_name' => 'PharmacieController',
            'categories'=>$this->categories,
            'products'=>$this->medicaments,
        ]);
    }

    #[Route('/pharmacie/liste/{cat}', name: 'app_pharmacie_cat')]
    public function liste(int $cat): Response
    {
        $medicaments = [];
        foreach ($this->medicaments as $medicament) {
            if ($medicament['cat_id'] === $cat) {
                $medicaments[] = $medicament;
            }
        }

        return $this->render('pharmacie/produitcat.html.twig', [
            'controller_name' => 'PharmacieController',
            'products' => $medicaments,
            'categories' => $this->categories,
            'cat' => $cat, // nécessaire pour afficher le nom de la catégorie dans le template
        ]);
    }

    #[Route('/pharmacie/{cat}/{id}', name: 'app_pharmacie_medicament')]
    public function detail(int $cat, int $id): Response
    {
        $medicament = null;
        foreach ($this->medicaments as $m) {
            if ($m['cat_id'] === $cat && $m['id'] === $id) {
                $medicament = $m;
                break;
            }
        }

        if (!$medicament) {
            throw $this->createNotFoundException('Médicament introuvable.');
        }

        return $this->render('pharmacie/detail.html.twig', [
            'medicament' => $medicament,
            'categories' => $this->categories,
            'cat' => $cat,
        ]);
    }

 /** @var array<int, array<string, mixed>> */
 private array $medicaments = [
 // Cat. 1
 ['id'=>101,'nom'=>'Amoxicilline','forme'=>'Gélule','dosage'=>'500
mg','prix'=>'4.50','description'=>'loremLorem ipsum dolor sit amet, consectetur
adipiscing elit. Donec eu ante at ex mollis molestie ac eget sapien. Sed tempor ipsum
sed pulvinar gravida. Nulla quis diam aliquet sapien maximus fermentum. Nullam fermentum
pharetra tortor, vestibulum fringilla eros vehicula id. Morbi feugiat, sem in convallis
rhoncus, magna dolor placerat quam, quis finibus justo eros et justo. Donec at tortor
vitae quam accumsan suscipit vel tristique est. Cras in velit posuere, dapibus mauris a,
rhoncus erat. ','image'=>'img/meds/amoxicilline.jpg','stock'=>12,'cat_id'=>1],
 ['id'=>102,'nom'=>'Clarithromycine','forme'=>'Comprimé','dosage'=>'250
mg','prix'=>'7.90','description'=>'loremLorem ipsum dolor sit amet, consectetur
adipiscing elit. Donec eu ante at ex mollis molestie ac eget sapien. Sed tempor ipsum
sed pulvinar gravida. Nulla quis diam aliquet sapien maximus fermentum. Nullam fermentum
pharetra tortor, vestibulum fringilla eros vehicula id. Morbi feugiat, sem in convallis
rhoncus, magna dolor placerat quam, quis finibus justo eros et justo. Donec at tortor
vitae quam accumsan suscipit vel tristique est. Cras in velit posuere, dapibus mauris a,
rhoncus erat. ','image'=>'img/meds/clarithromycine.jpg','stock'=>5,'cat_id'=>1],
 // Cat. 2
 ['id'=>201,'nom'=>'Paracétamol','forme'=>'Comprimé','dosage'=>'500
mg','prix'=>'2.10','description'=>'loremLorem ipsum dolor sit amet, consectetur
adipiscing elit. Donec eu ante at ex mollis molestie ac eget sapien. Sed tempor ipsum
sed pulvinar gravida. Nulla quis diam aliquet sapien maximus fermentum. Nullam fermentum
pharetra tortor, vestibulum fringilla eros vehicula id. Morbi feugiat, sem in convallis
rhoncus, magna dolor placerat quam, quis finibus justo eros et justo. Donec at tortor
vitae quam accumsan suscipit vel tristique est. Cras in velit posuere, dapibus mauris a,
rhoncus erat. ','image'=>'img/meds/paracetamol.jpg','stock'=>0,'cat_id'=>2],
 ['id'=>202,'nom'=>'Codéine','forme'=>'Comprimé','dosage'=>'30
mg','prix'=>'5.60','description'=>'loremLorem ipsum dolor sit amet, consectetur
adipiscing elit. Donec eu ante at ex mollis molestie ac eget sapien. Sed tempor ipsum
sed pulvinar gravida. Nulla quis diam aliquet sapien maximus fermentum. Nullam fermentum
pharetra tortor, vestibulum fringilla eros vehicula id. Morbi feugiat, sem in convallis
rhoncus, magna dolor placerat quam, quis finibus justo eros et justo. Donec at tortor
vitae quam accumsan suscipit vel tristique est. Cras in velit posuere, dapibus mauris a,
rhoncus erat. ','image'=>'img/meds/codeine.jpg','stock'=>8,'cat_id'=>2],
 // Cat. 3
 ['id'=>301,'nom'=>'Ibuprofène','forme'=>'Comprimé','dosage'=>'400
mg','prix'=>'3.20','description'=>'loremLorem ipsum dolor sit amet, consectetur
adipiscing elit. Donec eu ante at ex mollis molestie ac eget sapien. Sed tempor ipsum
sed pulvinar gravida. Nulla quis diam aliquet sapien maximus fermentum. Nullam fermentum
pharetra tortor, vestibulum fringilla eros vehicula id. Morbi feugiat, sem in convallis
rhoncus, magna dolor placerat quam, quis finibus justo eros et justo. Donec at tortor
vitae quam accumsan suscipit vel tristique est. Cras in velit posuere, dapibus mauris a,
rhoncus erat. ','image'=>'img/meds/ibuprofene.jpg','stock'=>15,'cat_id'=>3],
 ['id'=>302,'nom'=>'Naproxène','forme'=>'Comprimé','dosage'=>'500
mg','prix'=>'6.30','description'=>'loremLorem ipsum dolor sit amet, consectetur
adipiscing elit. Donec eu ante at ex mollis molestie ac eget sapien. Sed tempor ipsum
sed pulvinar gravida. Nulla quis diam aliquet sapien maximus fermentum. Nullam fermentum
pharetra tortor, vestibulum fringilla eros vehicula id. Morbi feugiat, sem in convallis
rhoncus, magna dolor placerat quam, quis finibus justo eros et justo. Donec at tortor
vitae quam accumsan suscipit vel tristique est. Cras in velit posuere, dapibus mauris a,
rhoncus erat. ','image'=>'img/meds/naproxene.jpg','stock'=>3,'cat_id'=>3],
 ];
}
