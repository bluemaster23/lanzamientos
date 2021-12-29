<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Resource\Security\Encrypt;
use App\Service\spotifyService;

class homeController extends AbstractController {


    /**
     * @Route("/", name="index")
     * @param Request $request
     * @return Response
     */
    public function index() {
        return $this->redirectToRoute('lanzamientos');

    }

    /**
     * @Route("/lanzamientos/{page}", name="lanzamientos")
     * @param Request $request
     * @return Response
     */
    public function lanzamientos(int $page = 0 , Request $request, spotifyService $spotify) {
        
        $this->verificarToken($spotify ,$request);
        
        $data = $spotify->getNewAlbum($page*20);
        return $this->render('home.html.twig', [
            'title' => 'lanzamientos',
            'datas' => $data["albums"]["items"],
            'total' => count($data["albums"]["items"]),
            'page' => $page+1
        ]);
    }

    /**
     * @Route("/artista/{id}", name="artista", requirements={"id"=".+"})
     * @param Request $request
     * @return Response
     */
    public function artista(String $id, Request $request, spotifyService $spotify){
        try{
            $this->verificarToken($spotify ,$request);  
            $artista = $spotify->getArstist($id);
            $track = $spotify->getTopTrackArtist($id);
            return $this->render('artista.html.twig', [
                'title' => 'artista',
                'artista' => count($artista) > 0  ?  $artista : [],
                'top' => count($track) > 0  ?  $track["tracks"] : []
            ]);
        }catch(\Exception $e){
            return $this->render('error.html.twig');
        }

    }


    private function verificarToken($spotify , $request){
        $cookie = $request->cookies->get("SAPISID");
        if(!$cookie){
           $cookie= $this->generateCookie($spotify->getToken());
        }
        $spotify->setToken($cookie);
    }

    private function generateCookie($token){
        $cookie = Cookie::create('SAPISID')
                ->withValue($token["token"])
                ->withExpires(time() +  $token["time"])
                ->withDomain('localhost')
                ->withSecure(true)
                ->WithHttpOnly(true);
        $response = new Response();
        $response->headers->setCookie($cookie);
        $response->send();
        return $token["token"];
    }
}