<?php
class Gallery extends Controller {
  public function index() {
    $this->albums();
  }
  
  public function albums() {
    $albums = $this->gallery->albums();
    $title = 'Albums'; 
    //var_dump($albums);
    $albums = $this->add_if_album_owner_at_array($albums, 'id_users' );
    $this->loader->load('albums', ['title' => $title, 'albums' => $albums]);
  }

  private function add_if_album_owner_at_array($albums = [], $keyId){
    foreach ($albums as $album => $value)
      $albums[$album]['album_owner'] = $this->user_is_owner($value[$keyId]);
    return $albums;
  }

  private function add_if_photo_owner_at_array($photos = [], $keyId){
    foreach ($photos as $photo => $value)
      $photos[$photo]['photo_owner'] = $this->user_is_owner($value[$keyId]);
    return $photos;
  }

  public function user_is_owner($id) {
    $id = filter_var($id);
    if ($this->getIdUsers() == $id)
      return true;
    return false;
  }

  private function getIdUsers(){
    return $this->sessions->logged_user()->id;
  }

  public function albums_new() {
    if ($this->redirect_unlogged_user()) return;
    $this->loader->load('albums_new', ['title' => 'albums_new']);
  }
  
  public function albums_create() {
    try{
      $album_name = filter_input( INPUT_POST, 'album_name' );
      /*Création de l'album avec le modèle. */
      $this->gallery->create_album($album_name, $this->getIdUsers());
      /*redirection du client vers la liste des albums */
      header('Location: /index.php/gallery/albums');
    }catch( Exception $e ){
      $this->loader->load('albums_new', ['title'=> 'Création d\'un album', 'error_message' => $e->getMessage()] );
    }
  }
  
  public function albums_delete($album_id) {
    if ($this->redirect_unlogged_user()) return;
    
    try {
      $album_id = filter_var( $album_id );
      $this->gallery->delete_album( $album_id );
    } catch (Exception $e) {}
    header('Location: /index.php/gallery/albums');
  }

  public function albums_show($album_id) {
    try {
      $album_id = filter_var( $album_id );
      $this->gallery->check_if_album_exists($album_id);
      $album = $this->gallery->getAlbum($album_id);
      $photos = $this->gallery->photos($album_id);

      $album = $this->add_if_album_owner_at_array([$album], 'id_users');

      $photos = $this->add_if_photo_owner_at_array($photos, 'id_users');

      //var_dump($album); echo "<br>";
      //var_dump($photos); echo "<br>";
      $this->loader->load('album_show', 
                        ['title' => $album['album_name'],
                         'album' => $album,
                         'photos'=>$photos ]);
      
    } catch (Exception $e) {
      header('Location: /index.php/gallery/albums');
      echo $e->getMessage();
    }
  }
  
  public function photos_new($album_id) {
    if ($this->redirect_unlogged_user()) return;
    $album_id = filter_var( $album_id );
    $album = $this->gallery->getAlbum($album_id);
    $this->loader->load('photos_new', ['album' => $album,
                        'title'=>"Ajout d'une photo dans l'album ${album['album_name']}"]);
  }

  public function photos_add($album_id) {
    try {
      $album_id = filter_var( $album_id );
      $album = $this->gallery->getAlbum($album_id);
      $this->gallery->check_if_album_exists( $album['album_id'] );
    } catch (Exception $e) {
      header("Location: /index.php"); 
     }

    try {
      $photo_name = filter_input(INPUT_POST, 'photo_name');
      if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Vous devez choisir une photo.');
      }
      
      $tmp_file = $_FILES['photo']['tmp_name'];
      $this->gallery->add_photo($album['album_id'], $photo_name, $tmp_file, $this->getIdUsers());
      
      header("Location: /index.php/gallery/albums_show/${album['album_id']}");
    } catch (Exception $e) {
      $this->loader->load('photos_new', ['album_id'=>$album['album_id'],
                          'title'=>"Ajout d'une photo dans l'album ${album['album_name']} ", 
                          'error_message' => $e->getMessage()]);
    }
  }
  
  public function photos_delete($album_id, $photo_id) {
    if ($this->redirect_unlogged_user()) return;
    try {
      $album_id = filter_var($album_id);
      $photo_id = filter_var($photo_id);
      $this->gallery->delete_photo($photo_id);
      header("Location: /index.php/gallery/albums_show/$album_id");
    } catch (Exception $e) {
      header("Location: /index.php");
    }
  }
  
  public function photos_show($album_id, $photo_id) {
    try {
      $album_id = filter_var($album_id);
      $photo_id = filter_var($photo_id);
      $album = $this->gallery->getAlbum($album_id);
      $photo = $this->gallery->photo($photo_id);

      $this->loader->load('photos_show', ['title'=>"${album['album_name']} / ${photo['photo_name']}",
                                          'album'=>$album,
                                          'photo'=>$photo]);
    } catch (Exception $e) {
      header("Location: /index.php");
    }
  }

  public function photos_get($photo_id) {
    try {
        $photo_id = filter_var($photo_id);
        if (isset($_GET['thumbnail'])) { $data = $this->gallery->thumbnail($photo_id); }
        else { $data =  $this->gallery->fullsize($photo_id); }
        header("Content-Type: image/jpeg"); // modification du header pour changer le format des données retourné au client
        echo $data;                          // écriture du binaire de l'image vers le client
      } catch (Exception $e) {}
  }

  private function redirect_unlogged_user() {
    if (!$this->sessions->user_is_logged()) {
      header('Location: /index.php/sessions/sessions_new');
      return true;
    }
    return false;
  }

}