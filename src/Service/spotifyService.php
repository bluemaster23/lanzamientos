<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class spotifyService{

    private $client;
    private $token ;
    private $time;


    public function __construct(HttpClientInterface $client){
        $this->client = $client;
    }

    private function connectionAuth (){
        $response = $this->client->request('POST','https://accounts.spotify.com/api/token',
            [ 
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Basic '. base64_encode($_ENV["ID_CLIENTE"]. ':'. $_ENV["CLIENT_SECRET"]),
                ],
                'body' => ['grant_type' => 'client_credentials']
        ]);
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->toArray();
        $this->token=  $content["token_type"].' '.$content["access_token"];
        $this->time=  $content["expires_in"];
        
       return ['token' => $this->token, 'time' => $this->time ];
    }

    public function getToken(){
        return $this->connectionAuth();
    }

    public function setToken($token){
        $this->token = $token; 
    }


    private function connectionSearch( $url , $param = '', $method = "GET"   ){
        $id =  ($param) ? "/$param" : '' ;
        $response = $this->client->request($method , "https://api.spotify.com/v1/$url".$id ,
            [
                'headers' =>[
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->token
                ]
            ] );
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->toArray();
       return $content;
    }

    public function getNewTrack($id = ''){
        $param = '?q=tag:new&type=album';
        return $this->connectionSearch('search' . $param );	
    }

    public function getNewAlbum($id = ''){
        return $this->connectionSearch('browse' ,"new-releases?offset=$id");	
    }

    public function getArstist($id = ''){
       return $this->connectionSearch('artists' ,$id);
    }

    public function getAlbumArtist($id = ''){
       return $this->connectionSearch('artists' ,$id .'/albums?market=ES');
    }

    public function getTopTrackArtist($id = ''){
        return $this->connectionSearch('artists' ,$id .'/top-tracks?market=ES');
     }
}