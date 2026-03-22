 <?php require ('partial/head.php') ?>
<?php require ('partial/nav.php') ?>
<?php require ('partial/banner.php') ?>

  <main>
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <?php foreach ($notes as $note) : ?>
        <li> 
          <a href="/note?id= <?= $note['id'] ?> " class= "text-blue-500 hover:underline">
          <?=  $note['body'] ?>
         </a>
        </li>
      <?php endforeach ?>
    </div>
  </main>
  <?php require ('partial/footer.php') ?>