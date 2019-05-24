# Movie Search Engine
A movie search engine based on overview developed by php

## Overview
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
In result.php, we firstly connect MySQL database, retrive user typing key words and fetch overview to array collection.
```
$conn = mysqli_connect("localhost","username","password","table_name");
$get_value = $_GET['user_query'];

$query1 = "select overview from movie2";
$result1 = mysqli_query($conn, $query1);

$array1 = array();

while($row_result1=mysqli_fetch_assoc($result1)) {
$array1[] = $row_result1;
}
$collection = array_column($array1, 'overview');
```
The dictionary then is stemmed by calling external script *stemmer.php*.
```
require_once('stemmer.php');

foreach ($collection_raw as $overview) {
$terms_raw = explode(' ', $overview);
$array_terms_raw[] = $terms_raw;

foreach ($array_terms_raw as $ID => $terms_raw1) {

foreach ($terms_raw1 as $termID => $terms_raw2){
  $stemmer = new Stemmer;
  $stem = $stemmer->stem($terms_raw2);
  $stem_space = $stem . "&nbsp";
  $dictionary[$ID] = array($termID => $stem_space);
  }
}
}
```
We define a new function to get index for overview later.
```
function getIndex() {
  global $collection;

  $dictionary = array();
  $docCount = array();

  foreach($collection as $docID => $doc) {
    $terms = explode(' ', $doc);
    $docCount[$docID] = count($terms);

	foreach($terms as $term) {
	  if(!isset($dictionary[$term])) {
	    $dictionary[$term] = array('df' => 0, 'postings' => array());
	    }
	    if(!isset($dictionary[$term]['postings'][$docID])) {
		  $dictionary[$term]['df']++;
		  $dictionary[$term]['postings'][$docID] = array('tf' => 0);
		  }
	    $dictionary[$term]['postings'][$docID]['tf']++;
	}
  }
	return array('docCount' => $docCount, 'dictionary' => $dictionary);
	}
```
Next we calculate similarity between user query vector and document vectors.
```
$query = explode(' ', $get_value);

$index = getIndex();
$matchDocs = array();
$docCount = count($index['docCount']);

foreach($query as $qterm) {
  $entry = $index['dictionary'][$qterm];
  foreach($entry['postings'] as $docID => $posting) {
    $matchDocs[$docID] = $posting['tf'] * log(($docCount + 1) / ($entry['df'] + 1), 2);
  }
}

foreach($matchDocs as $docID => $score) {
  $matchDocs[$docID] = $score/$index['docCount'][$docID];
}

arsort($matchDocs);
```
Finally we output movie title overview and its similarity value(tf-idf score).
```
<?php
foreach ($matchDocs as $id => $cosSim) {
  $array=array();
  $query = "select Title, Overview from movie2 where id = $id";
  $result = mysqli_query($conn, $query);
  while($row_result=mysqli_fetch_assoc($result)){
    $array[] = $row_result;
  }

  $moive_title = $array[0]['Title'];
  $movie_overview = $array[0]['Overview'];
?>

<div>
  <h2><?php echo $moive_title ?></h2>
  <p>Tf-idf Score: <?php echo $cosSim ?></p>
  <?php $showOverview = highlightKeywords($movie_overview, $get_value) ?>
  <p><?php echo $showOverview ?></p>
</div>

<?php
}
?>
```
A highlight key word function is also defined.
```
function highlightKeywords($text, $keyword) {
  $wordsAry = explode(" ", $keyword);
  $wordsCount = count($wordsAry);

  for($i=0;$i<$wordsCount;$i++) {
    $highlighted_text = "<mark>$wordsAry[$i]</mark>";
    $text = str_ireplace($wordsAry[$i], $highlighted_text, $text);
  }
return $text;
}
```
## Methodology on blog
https://chaoweiwang6.wixsite.com/website/blog/the-power-of-open-work-space
