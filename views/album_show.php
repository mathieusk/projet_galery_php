<div class="row">
  <?php foreach ($photos as $photo) { ?>
    <div class="col-sm-3 col-md-3">
      <div class="thumbnail">
        <?php if( $album[0]['album_owner'] || $photo['photo_owner'] ){ ?>
        <a class="close" href="/index.php/gallery/photos_delete/<?=$album[0]['album_id']?>/<?=$photo['photo_id']?>">×</a>
        <?php } ?>
        <br> <br> 
        <a style="height: 100px; width: 200px; display: table-cell; vertical-align: middle; text-align: center;"
           href="/index.php/gallery/photos_show/<?=$album[0]['album_id']?>/<?=$photo['photo_id']?>">
          <img src="/index.php/gallery/photos_get/<?=$photo['photo_id']?>?thumbnail" alt="<?=$photo['photo_name']?>">
        </a>
        <div class="caption text-center"><?=$photo['photo_name']?><br></div>
      </div>
    </div>
  <?php } ?>
</div>

<a href="/index.php/gallery/photos_new/<?=$album[0]['album_id']?>"  class="btn btn-primary" role="button">Ajouter une photo</a>
<a href="/index.php" class="btn btn-danger" role="button">Revenir à la liste des albums</a>