<?php


namespace App\Controller;


use App\Entity\Galery;
use App\Entity\Images;
use App\Form\GaleryType;
use App\Form\ImagesType;
use App\Repository\GaleryRepository;
use App\Repository\ImagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /*-----------Upload Images-------------*/

    /**
     * @Route("admin/new_image", name="new_image")
     */
    public function imageInsert(Request $request, EntityManagerInterface $entityManager, GaleryRepository $galeryRepository)
    {

        $image = new Images();


        $form = $this->createForm(ImagesType::class, $image);


        /*La méthode Post me permet de cacher les infos contrairement a la méthode GET*/
        if($request->isMethod('POST')) {

            /*Récupère la requête uniquement si la méthode du form est "post" */
            $form->handleRequest($request);

            /** @var UploadedFile $imageFile */
            /*-Image correspond au champ de mon formulaire*/
            $imageFile = $form['images']->getData();

            // Condition nécessaire car le champ 'image' n'est pas requis
            // donc le fichier doit être traité que s'il est téléchargé
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Nécessaire pour inclure le nom du fichier en tant qu'URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                // Déplace le fichier dans le dossier des images de la image
                try {
                    $imageFile->move(
                        $this->getParameter('model_images'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // Met à jour l'image pour stocker le nouveau nom de la image
                $image->setName($newFilename);

                //liaison entre les deux tables
                $galery = $galeryRepository->find($_POST['images']['galery'][0]);
                $image->addGalery($galery);
            }
            // Si le formulaire est envoyé et qu'il est valide (si les champs obligatoires sont remplis...)
            // si ce n'est pas le cas, cette étape est sautée pour arriver directement au return
            // (donc l'affichage de la page avec le formulaire)
            if ($form->isSubmitted() && $form->isValid()) {

                // On envoie la image en base de données grâce aux méthodes persist(objet) + flush
                // persist + flush est l'équivalent de commit + push de Git.

                $entityManager->persist($image);
                $entityManager->flush();
                $this->addFlash('Success', 'L\'image  a bien été enregistré.');

                return $this->redirectToRoute('home');

            } else {
                $this->addFlash('Fail', 'L\'image  n\'a pas été 
            enregistré, veuillez réessayer.');
            }

        }
        return $this->render('admin/admin_images.html.twig', [
            // formView retourne tout le code html correspondant au formulaire
            'imagesForm' => $form->createView(),
            'images' => $image,

        ]);

    }


    /*-------insert Galery Form------------*/

    /**
     * @Route("admin/newGalery", name="new_galery")
     */
    public function create(Request $request, EntityManagerInterface $entityManager){

        $galery = new Galery();

        $form = $this->createForm(GaleryType::class, $galery);
        if($request->isMethod('POST')){
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {

                $entityManager->persist($galery);
                $entityManager->flush();

                return $this->redirectToRoute('home');
            }
        }
        return $this->render('admin/admin_galery.html.twig',[

            'galeryForm' => $form->createView(),
        ]);

    }


    /*--------suppression de l'image---------*/

    /**
     * @Route("admin/images/{id}/delete", name="images_galerie_delete")
     */
    public function ImagesDelete($id, ImagesRepository $imagesRepository, EntityManagerInterface $entityManager){

        $images = $imagesRepository->find($id);

        $entityManager->remove($images);
        $entityManager->flush();

        return $this->redirectToRoute('home');
    }

    /*-----suppression galery-------*/
    /**
     * @Route("admin/galeries/{id}/delete", name="galeries_delete")
     */
    public function GaleriesDelete($id, GaleryRepository $galeryRepository, EntityManagerInterface $entityManager){

        $galery = $galeryRepository->find($id);

        $entityManager->remove($galery);
        $entityManager->flush();

        return $this->redirectToRoute('home');
    }
}