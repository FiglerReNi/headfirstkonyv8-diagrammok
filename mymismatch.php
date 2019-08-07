<style>
    * {
        box-sizing: border-box;
    }

    .column {
        float: left;
        width: 20%;
        padding-left: 20px;


    }

    .row:after {
        content: "";
        display: table;
        clear: both;
    }
</style>


<?php
require_once 'kapcs.php';
require_once 'startsession.php';
$title = 'MyMismatch';
require_once 'header.php';

$query = "SELECT * FROM mismatch.mismatch_response WHERE mismatch_response.user_id != '$userid'";
$result = mysqli_query($kapcs, $query);
while($row=mysqli_fetch_array($result)){
    $tomb[] = $row;
}
for ($j = 0; $j < count($tomb); $j++) {
    $felh[] = $tomb[$j]['user_id'];
}
$felh = array_keys(array_flip($felh));

$query1 = "SELECT * FROM mismatch.mismatch_response WHERE mismatch_response.user_id = '$userid'";
$result1 = mysqli_query($kapcs, $query1);
while($row1=mysqli_fetch_array($result1)) {
    $tomb1[] = $row1;
}
$szamlalo = '';
$maxszamlalo = '';
$ki = '';
$maxtopic = array();
for ($j = 0; $j < count($felh); $j++) {
    $topic = array();
    $szamlalo = 0;
    for ($i = 0; $i < count($tomb); $i++) {
        if ($tomb[$i]['user_id'] == $felh[$j]) {
            for ($z = 0; $z < count($tomb1); $z++) {
                if ($tomb[$i]['topic_id'] == $tomb1[$z]['topic_id']) {
                    if (($tomb[$i]['response'] + $tomb1[$z]['response']) == 3) {
                        $szamlalo++;
                        array_push($topic, $tomb[$i]['topic_id']);
                    }
                }
            }
        }
    }
    if ($maxszamlalo < $szamlalo) {
        $maxszamlalo = $szamlalo;
        $ki = $felh[$j];
        $maxtopic = $topic;
    }
}
$query2 = "SELECT * FROM mismatch.mismatch_user WHERE mismatch_user.user_id = '$ki'";
$result2 = mysqli_query($kapcs, $query2);
$row2= mysqli_fetch_array($result2);
$name = $row2['first_name']. " ". $row2['last_name'];
$hely = $row2['city'] .", ". $row2['state'];
$fajl = $row2['picture'];
echo "<div class='row'>";
echo '<div class="col-6 col-sm-2 row align-self-center mr-3"><img src="' . target_dir . $fajl . '" alt="score image" height="80px" width="80px" ></div>';
echo "<div class='w-100'></div>";
echo "<div class=\"col-6 col-sm-2\"><strong>$name</strong></div>";
echo "<div class='w-100'></div>";
echo "<div class=\"col-6 col-sm-2\"><strong>$hely</strong></div>";
echo "<div class='w-100'></div>";
echo "</div>";
echo "<br>";
echo "<strong>You are mismatched on the following $maxszamlalo topics:</strong>";
echo "<br>";

$categories = array();
$a = array();
$b = array();
$c = array();
$d = array();
$e = array();

foreach ($maxtopic as $item) {
    $query3 = "SELECT mismatch.mismatch_topic.name, mismatch.mismatch_category.category, mismatch.mismatch_category.id FROM mismatch_topic 
                LEFT JOIN mismatch_category ON mismatch_category.id = mismatch_topic.category_id
                WHERE mismatch_topic.topic_id = '$item'";
    $result3 = mysqli_query($kapcs, $query3);
    $row3 = mysqli_fetch_array($result3);
    if($row3['id'] == 1){array_push($a, $row3['name']);}
    elseif ($row3['id'] == 2){array_push($b, $row3['name']);}
    elseif ($row3['id'] == 3){array_push($c, $row3['name']);}
    elseif ($row3['id'] == 4){array_push($d, $row3['name']);}
    else{array_push($e, $row3['name']);}
    array_push($categories, $row3['category']);
}

    echo '<div class="row">';
    echo '<div class="column">';
    foreach ($a as $item){echo "<i class='fas fa-splotch' style='font-size:24px; color:red'></i> " . $item; echo'<br>';}
    echo '</div>';
    echo '<div class="column">';
    foreach ($b as $item){echo "<i class='fas fa-splotch' style='font-size:24px; color:red'></i> " . $item; echo'<br>';}
    echo '</div>';
    echo '<div class="column">';
    foreach ($c as $item){echo "<i class='fas fa-splotch' style='font-size:24px; color:red'></i> " . $item; echo'<br>';}
    echo '</div>';
    echo '<div class="column">';
    foreach ($d as $item){echo "<i class='fas fa-splotch' style='font-size:24px; color:red'></i> " . $item; echo'<br>';}
    echo '</div>';
    echo '<div class="column">';
    foreach ($e as $item){echo "<i class='fas fa-splotch' style='font-size:24px; color:red'></i> " . $item; echo'<br>';}
    echo '</div>';
    echo '</div>';
/*
$proba=array(array($categories[0], 0));
for($i=0; $i < count($categories); $i++){
     if($categories[$i] == $proba[count($proba)-1][0]){
         $proba[count($proba)-1][1]++;
     }
     else{
         array_push($proba,(array($categories[$i], 1)));
     }
}
*/

$category_totals = array_count_values($categories);
echo'<h4>Mismatched category breakdown</h4>';
echo '<br>';
$path = target_dir . $userid . '-diagram';
draw_bar_graph(480,240, $category_totals,5, $path);
echo '<img src="'.$path.'" alt="Mismatch category graph"/><br>';
echo '<br>';
echo '<strong>View profile: <a class="underline" href="people.php?id=' . $ki . '" > ' . $name . '</a></strong>';
require_once 'footer.php';

function draw_bar_graph($width, $height, $data, $max_value, $filename ){
    //üres kép amire rajzolhatunk:
   $img = imagecreatetruecolor($width, $height);
   //szinek:
    $bg_color = imagecolorallocate($img,255, 255, 255);
    $text_color = imagecolorallocate($img, 255,0,0);
    $bar_color = imagecolorallocate($img, 0,0,0);
    $border_color = imagecolorallocate($img, 192,192,192);
    //háttér kitöltése:
    imagefilledrectangle($img, 0 ,0 , $width, $height, $bg_color);
    //oszlopok megrajzolása:
    $bar_width = $width/((count($data)*2)+1);
    $keys = array_keys($data);
    for($i=0; $i< count($data); $i++){
        imagefilledrectangle($img, ($i*$bar_width*2)+ $bar_width, $height, ($i*$bar_width*2)+($bar_width*2),  $height-(($height/$max_value))*$data[$keys[$i]],$bar_color);
        imagestringup($img, 5, ($i*$bar_width*2)+ $bar_width+$bar_width/3, $height-5, $keys[$i], $text_color);
    }
    //téglalap az oszlopdiagram köré:
    imagerectangle($img, 10, 0, $width-1, $height-1, $border_color);
    //skála felrajzolása:
    for($i=1; $i<=$max_value; $i++){
        imagestring($img, 5, 0, $height -($height/$max_value)*$i, $i, $bar_color);
    }
    //fájlba írás:
    imagepng($img, $filename, 0);
    imagedestroy($img);
    }

