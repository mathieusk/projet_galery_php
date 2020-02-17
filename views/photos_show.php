<div class="row text-center">
  <a href="/index.php/gallery/albums_show/<?=$album['album_id']?>" 
     class="btn btn-danger">Retourner à l'album</a><br>
  <br>
  <div class="col-sm-12 col-md-12">
    <img style="max-width: 100%" src="/index.php/gallery/photos_get/<?=$photo['photo_id']?>"
      alt="<?=$photo['photo_name']?>">
  </div>
</div>