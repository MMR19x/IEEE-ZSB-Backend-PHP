 <?php require ('partial/head.php') ?>
<?php require ('partial/nav.php') ?>
<?php require ('partial/banner.php') ?>

  <main>
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

    <p class = "mb-6"> <a href ="/notes" class ="text-blue-500 undeline" > go back </a> </p>
         <p> <?=  $note['body'] ?> </p>
    </div>
  </main>
  <?php require ('partial/footer.php') ?>