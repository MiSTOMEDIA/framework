* Framework intro
A framework based on packages

* Folder structure
- packages
 - core
  - database
   - connector.php
   - builder.php
  - config
   - packages.php
   - routes.php
  router.php
 - rocket
  - controllers
   - posts.php
  - models
   - post.php
  - routes.php
  - install.php
- public
 - .htaccess
 - index.php

* GUI idea

The packages are to be "installed" via an web interface. They can then be activated, modified etc.
URI: /appadmin or something

 * Code example

<?php
 public class Posts extends Controller
 {
  public function all()
  {
    $posts = new \Rocket\Blog;
    $config = Core::config ('lists.posts_per_page'); // Core::config(); ' For all

    // Standard packages like view
    $this->view->render ('rocket.blog.all', array (
      'posts' => $posts->all()
    ));
  }
 }
?>

 * An example of a install.php

<?php

// ID, created_at and updated_at are automatically added
DB::createTable ('posts', array (
  'user_id' => 'int'
  'title' => 'string',
  'synopsis' => 'text',
  'body' => 'text',
));

?>