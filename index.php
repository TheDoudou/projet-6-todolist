<?
/*
* Début du code vendredi matin, Odoo m'a pris un peu de temps ;) reprise le mardi
* 
* 
*
*/
error_reporting(E_ERROR | E_PARSE);





$type = $_POST;
$unique = true;  // false one file / true session_id file





if ($unique == true)
{
    session_start();
    setcookie(session_name(),session_id(),0);
    $file = 'assets/data/'.session_id().'.json';

}
else
    $file = 'assets/data/test.json';







if ($_GET['clear'] == 1)            // For test 
{ 

    array_map('unlink', glob("assets/data/*.*"));
    rmdir('assets/data/');
    mkdir('assets/data/');
    exit;


}



if ($_GET['view'] == 1)             // For test 
{ 


    var_dump(json_decode(file_get_contents($file)));
    exit;



}








if ($type)
{

    $todoAdd = htmlspecialchars(trim(filter_var($type['todo-add'], FILTER_SANITIZE_STRING)));
    $todoCatAdd = htmlspecialchars(trim(filter_var($type['todo-cat-add'], FILTER_CALLBACK, array('options' => 'parseString'))));
    $todoCat = htmlspecialchars(trim(filter_var($type['todo-cat'], FILTER_CALLBACK, array('options' => 'parseString'))));

    
    if ($todoAdd && ($todoCatAdd || $todoCat))
    {



        $todoCatAdd = ucfirst(strtolower($todoCatAdd));

        if ($todoCatAdd)            // Get one cat (input or select)
            $cat = $todoCatAdd;
        else
            $cat = $todoCat;



        $json = file_get_contents($file);
        $data = json_decode($json, true);

        if ($data == NULL)
            $data = [];

        array_push($data, [uniqid(), $todoAdd, $cat, false]);


        file_put_contents($file, json_encode($data));
    



    }
    else if ($type['save'] == 'to-archive')
    {
        

        $json = file_get_contents($file);
        $data = json_decode($json, true);




        for ($i = 0; $i < count($data); $i++)
        {
            

            if ($data[$i][3] == true && !in_array($data[$i][0], $type['todo-update']))
            {
                $data[$i][3] = false;
            }



            if ($data[$i][3] == false && in_array($data[$i][0], $type['todo-update']))
            {
                $data[$i][3] = true;
            }




        }


        file_put_contents($file, json_encode($data));


    }

}


    $json = file_get_contents($file);
    $data = json_decode($json, true);





function parseString($s) {
    if (preg_match('/^[a-z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ\.\-\'\,]{2,50}$/i', $s) == 1)
        return htmlspecialchars(trim($s)); // Not realy utile but why not it's for sample
    else
        return false;
}


// Define view category (first by default)
($categorie = htmlspecialchars(trim(filter_var($_GET['cat'], FILTER_SANITIZE_STRING)))) ?  true : $categorie = $data[0][2];


// Input json data; output array
function listCat($d)
{
    $ret = [];
    
    foreach ($d as $value)
    {


        if (!in_array($value[2], $ret))
            array_push($ret, $value[2]);




    }

    return $ret;


}



// Input json data and category; output array
function listItemsCat($d, $cat)
{
    $ret = [];


    foreach ($d as $value)
    {


        if (!in_array($value[0], $ret) && $value[2] == $cat && $value[3] == false)
            array_push($ret, $value);



    }

    return $ret;
}

// Input json data output array
function listItemsChecked($d) {

    $ret = [];

    foreach ($d as $value)
    {


        if (!in_array($value[0], $ret) && $value[3] == true)
            array_push($ret, $value);



    }

    return $ret;

}


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>ToDoudou List</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" media="screen" href="assets/css/main.css" />
</head>
<body>


    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <?
        foreach (listCat($data) as $value) {
            if ($value == $categorie)
                echo '<div><h3>['.$value.']</h3></div> ';
            else
                echo '<div><h3>[<a href="?cat='.$value.'">'.$value.'</a>]</h3></div> ';

            /*
                echo '<li class="nav-item"><a class="nav-link active" id="'.$value.'-tab" data-toggle="tab" href="#'.$value.'" role="tab" aria-controls="'.$value.'" aria-selected="true">'.$value.'</a></li>';
            else
                echo '<li class="nav-item"><a class="nav-link" id="'.$value.'-tab" data-toggle="tab" href="#'.$value.'" role="tab" aria-controls="'.$value.'" aria-selected="false">'.$value.'</a></li>';
            */
        }
        ?>
        <div><h3>[<a href="https://github.com/TheDoudou/projet-6-todolist">Source</a>]</h3></div> 
    </ul>
    <div>
        <form action="" method="POST" id="form1">
            <div id="columns">
                <?  foreach (listItemsCat($data, $categorie) as $value) { ?>
                <div class="column" draggable="true">
                    <input type="checkbox" id="todo1" name="todo-update[]" value="<?= $value[0] ?>"/>
                    <label><?= $value[1] ?></label>
                </div>
                <? } ?>
            </div>
            <hr>
            <div>
                <div><h3>[Archive]</h3></div>
                <? foreach (listItemsChecked($data) as $value) { ?>
                <div draggable="true">
                    <input type="checkbox" id="todo2" name="todo-update[]" value="<?= $value[0] ?>" checked/>
                    <label for="todo2"><?= $value[1] ?></label>
                </div>
                <? } ?>
            </div>
        </form>
        <button type="submit" form="form1" name="save" value="to-archive">Enregistrer</button>
        <br><br><br><br>
        <hr>
        <div>
            <div><h3>AJOUTER UNE TACHE</h3></div>
            <form action="" method="POST" id="form2" class="d-flex align-items">
                <div>
                    <label for="todo-add">La tache :</label><div></div>
                    <input type="text" id="todo-add" name="todo-add" />&nbsp;&nbsp;
                </div>
                <div>
                    <label for="todo-cat">Catégorie :</label><div></div>
                    <input type="text" id="todo-cat-add" name="todo-cat-add" /><? if (count(listCat($data)) > 0) { ?> Ou <select name="todo-cat">
                    <? foreach (listCat($data) as $value) { ?>
                        <option value="<?= $value ?>" <? if ($value == $categorie) echo 'selected'; ?>><?= $value ?></option>
                    <? } ?>
                    </select><? } ?>
                </div>
            </form><br>
            <button type="submit" form="form2" name="save" value="">Enregistrer</button>
        </div>
    </div>







    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>