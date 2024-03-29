<?php

namespace App\Controller;

use App\Entity\Donnee;
use App\Entity\Fichier;
use App\Form\AfficherType;
use App\Repository\DonneeRepository;
use App\Repository\FichierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DonneeController extends AbstractController
{
    /**
     * @Route("/tableauDeBord", name="tableauDeBord", methods={"GET","POST"})
     */
    public function tableauDeBord(DonneeRepository $repo, Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $form = $this->createForm(AfficherType::class);

        $form->handleRequest($request);

        $dataAffiche = [];
        $donneesAffichees = [];

        $idF = 0;
        
        if ($form->isSubmitted() && $form->isValid()) {
            $fichierSelect = $form->getData()['fichier'];

            /////// Liste de touts les données qui ont été sélectinné
            $dataAffiche = [];
            for ($i = $fichierSelect->getPremiereDonnee()->getId(); $i <= ($fichierSelect->getPremiereDonnee()->getId())+($fichierSelect->getNbLigne())-2; $i++){
                $dataAffiche[] = $i;
            }
            $donneesAffichees = $repo->findBy(['id' => $dataAffiche]); // 
            $idF = $fichierSelect->getId();

        }

        return $this->render('donnee/tableauDeBord.html.twig', [
            'form' => $form->createView(),
            "listeDonnee" => $donneesAffichees,
            "idF" => $idF

        ]);
    }
    
    /**
     * @Route("/deleteDonnee/{id}", name="deleteDonnee", methods={"GET"})
     */
    public function deleteDonnee(Donnee $donnee, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        //voir si cette fonction est vraiment necessaire dans l'etats aqutuel des chose et 
        // basculer deteData de fichier controller ici
        
        $entityManager->remove($donnee);
        $entityManager->flush();

        return $this->redirectToRoute('tableauDeBord');
    }

    /**
     * @Route("/tableauDeBord/listeDonneesComplete/{idF}", name="listeDonneesComplete", methods={"GET"})
     */
    public function listeDonneesCompleteC(int $idF, DonneeRepository $repo, EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $fichier = $entityManager->getRepository(Fichier::class)->find($idF);

        //$id1 = $fichier->getId();
        $id1 = $fichier->getPremiereDonnee()->getId();
        $nbL = $fichier->getNbLigne();
        $id2 = ($id1+$nbL)-2;

        // $donneesAffichees = $this->listeDonneeFichier($fichier, $repo);

        $donneesAffichees = $paginator->paginate(
            $repo->listeDonneesComplete($id1, $id2),
            $request->query->getInt('page', 1), 
            10
        );

        return $this->render('donnee/listeDonneesComplete.html.twig',[
            "listeDonnee" => $donneesAffichees,
            "fichier" => $fichier,

            // "id" => $id1,
            // "nbl" => $id2
        ]);
    }



    //     /**
    //  * @Route("/tableauDeBord/listeDonneesComplete/{idF}", name="listeDonneesComplete", methods={"GET"})
    //  */
    // public function listeDonneesComplete(int $idF, DonneeRepository $repo, EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    // {
    //     $this->denyAccessUnlessGranted('ROLE_USER');

    //     // Récupération du fichier
    //     $fichier = $entityManager->getRepository(Fichier::class)->find($idF);

    //     // Vérification de l'existence du fichier
    //     if (!$fichier) {
    //         throw $this->createNotFoundException('Fichier non trouvé');
    //     }

    //     // Récupération des données pour le fichier spécifié
    //     $donnees = $repo->findBy(['fichier' => $fichier]);

    //     // Pagination des données
    //     $donneesPaginees = $paginator->paginate(
    //         $donnees,
    //         $request->query->getInt('page', 1), 
    //         10
    //     );

    //     return $this->render('donnee/listeDonneesComplete.html.twig', [
    //         'listeDonnee' => $donneesPaginees,
    //         'fichier' => $fichier,
    //     ]);
    // }







     /**
     * @Route("/tableauDeBord/graphiques/{idF}", name="graphiques", methods={"GET"})
     */
    public function graphiques(int $idF, DonneeRepository $repo, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $fichier = $entityManager->getRepository(Fichier::class)->find($idF);

        $donneesAffichees = $this->listeDonneeFichier($fichier, $repo);

        return $this->render('donnee/graphiques.html.twig',[
            "listeDonnee" => $donneesAffichees,
            "fichier" => $fichier
        ]);
    }


     /**
     * @Route("/tableauDeBord/statistiques/{idF}", name="statistiques", methods={"GET"})
     */
    public function statistiques(int $idF, DonneeRepository $repo, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $fichier = $entityManager->getRepository(Fichier::class)->find($idF);

        $donneesAffichees = $this->listeDonneeFichier($fichier, $repo);

        return $this->render('donnee/statistiques.html.twig',[
            "listeDonnee" => $donneesAffichees,
            "fichier" => $fichier
        ]);
    }


    // cette fonction a pour but de renvoyer le tableau des donnée correspondant au fichier
    public function listeDonneeFichier(Fichier $fichier, DonneeRepository $repo) : array
    {
        $donneesAffichees = [];

        $dataAffiche = [];

        for ($i = $fichier->getPremiereDonnee()->getId(); $i <= ($fichier->getPremiereDonnee()->getId())+($fichier->getNbLigne())-2; $i++){
            $dataAffiche[] = $i;
        }

        $donneesAffichees = $repo->findBy(['id' => $dataAffiche]);

        return $donneesAffichees;
    }
    

}
