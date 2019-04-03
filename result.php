<!-- The script developed by Chaowei Wang for CSE 5334. The script inspired by an online technic blog written by Ian Barber (http://phpir.com/simple-search-the-vector-space-model).
-->

<!DOCTYPE html>
<html>
  <head>
    <title>Result page</title>
  </head>

  <body>
        <a href="search.html" style="text-decoration: none"><button style="display: block;">Go Back</button></a>

        <?php
          $conn = mysqli_connect("localhost:3306","phpmyadmin","wangchaowe","chaoweiw_search");

          if(isset($_GET['search'])){
          $get_value = $_GET['user_query'];
          //echo $get_value;

            if($get_value==''){
                  echo "<center><b>Please type in the title of your favourite movie!</b></center>";
                  exit();
            }

            else {
                  $query1 = "select overview from movie2";

                  $result1 = mysqli_query($conn, $query1);

                    $array1 = array();

                    #extract overview data from database and store the data in an one dimensional array
                    while($row_result1=mysqli_fetch_assoc($result1)){
                    $array1[] = $row_result1;
                        }
                        $collection = array_column($array1, 'overview');
                        //print_r($collection);

                        #generate index for overview
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
                        //print_r(getIndex());

                        #calculate TFIDF by a giving term, return TFIDFs of the term for all documents contains the term
                        function getTfidf($term) {
                            $index = getIndex();
                            $docCount = count($index['docCount']);
                            $entry = $index['dictionary'][$term];
                            foreach($entry['postings'] as  $docID => $postings) {
                                echo "Document $docID and term $term give TFIDF: " .
                                ($postings['tf'] * log($docCount / $entry['df'], 2));
                                echo "<br>";
                            }
                        }
                        //echo getTfidf('Ted law');

                        #normallize vectore
                        function normalise($doc) {
                        foreach($doc as $entry) {
                        $total += $entry*$entry;
                        }
                        $total = sqrt($total);
                        foreach($doc as &$entry) {
                        $entry = $entry/$total;
                        }
                        return $doc;
                        }
                        #a function for calculate similarity between user query and document vectors
                        function cosineSim($docA, $docB) {
                        $result = 0;
                        foreach($docA as $key => $weight) {
                        $result += $weight * $docB[$key];
                        }
                return $result;
                        }

                        #calculate similarity between user query and document vectors
                        $query = explode(' ', $get_value);
                        //print_r($query);

                        $index = getIndex();
                        $matchDocs = array();
                        $docCount = count($index['docCount']);

                        foreach($query as $qterm) {
                        $entry = $index['dictionary'][$qterm];
                        //print_r($entry['postings']);
                        foreach($entry['postings'] as $docID => $posting) {
                        $matchDocs[$docID] = 
                                $posting['tf'] * log(($docCount + 1) / ($entry['df'] + 1), 2);
                        }
                        }

                        #length normalise
                        foreach($matchDocs as $docID => $score) {
                        $matchDocs[$docID] = $score/$index['docCount'][$docID];
                        }

                        arsort($matchDocs); // high to low

                        //ar_dump($matchDocs);
                       foreach ($matchDocs as $id => $cosSim) {
                                $array=array();
                                $query = "select Title, Overview from movie2 where id = $id";
                                $result = mysqli_query($conn, $query);
                                while($row_result=mysqli_fetch_assoc($result)){
                                        $array[] = $row_result;
                                }
                                //print_r($array);
                                $moive_title = $array[0]['Title'];
                                $movie_overview = $array[0]['Overview'];
                                echo "<div>
                                <h2>$moive_title</h2>
                                <p>Tf-idf Score: $cosSim</p>
                                <p>$movie_overview</p>
                                </div>";
                        }
            }
          }
        ?>
  </body> 
</html>
