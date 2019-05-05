# Movie Search Engine
A movie search engine based on overview developed by php

##Overview
It is basicly a movie search engine implemented by Tf-idf algorithm. Also Porter Stemming Algorithm is applied to normalize every single word in dictionary.

## Scripts in detail
- search.html
This script is just a very simple front page for user input key words. It keeps a veriable *user_query* and pass it to the latter php script. Once users click submit button, it calls out the result.php script.
```
<form class="search" action="result.php" method="get" style="margin:auto;max-width:300px">
  <input type="text" placeholder="Search Your Favourite Movie" name="user_query">
  <button type="submit" name="search"><i class="fa fa-search"></i></button>
</form>
```

- result.php
In result.php, we firstly connect MySQL database and retrive user typing key words.
```
$conn = mysqli_connect("localhost:3306","phpmyadmin","wangchaowe","chaoweiw_search");
$get_value = $_GET['user_query'];
