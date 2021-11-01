<?php

use function PHPSTORM_META\type;

session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php ");
    exit;
}
require_once "../config.php";

$temps = $theme = $nbr_questions = $type_quiz = $form_value = $file = $texte = "";
$temps_err = $theme_err = $nbr_err = $type_quiz_err = $file_err = $file_err = $texte_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
   
   $file_err2 = $file_err; 
    // Validate quantite
    $input_theme = trim($_POST["theme"]);
    if(empty($input_theme)){
        $theme_err = "Veuillez entrer un theme.";
    } else{
        $theme = $input_theme;
    }
    $input_temps = trim($_POST["temps"]);
    if(empty($input_temps)){
        $temps_err = "Veuillez entrer le temps autorisé.";
    } else{
        $temps = $input_temps;
    }
    $input_nbr = trim($_POST["nbr_questions"]);
    if(empty($input_nbr)){
        $nbr_err = "Veuillez entrer le nombre de questions.";
    } else{
        $nbr_questions = $input_nbr;
    }
    $input_type = trim($_POST["type_quiz"]);
    if(empty($input_type)){
        $type_quiz_err = "Veuillez entrer le type de quiz.";
    }
    else if ($input_type == "select") {
        $type_quiz_err = "Veuillez entrer le type de quiz.";
    } 
     else{
        $type_quiz = $input_type;
    }
    $maxsize = 2 * 1024 * 1024;
    if ($type_quiz == "serie") {
    if (count($_FILES) > 0) {
        $maxsize = 2 * 1024 * 1024;
        if (is_uploaded_file($_FILES['questions']['tmp_name'])) {
            $fileSize = $_FILES["questions"]["size"];
            if ($fileSize > $maxsize) {
                $file_err = "La taille du fichier ne doit pas dépasser 2Mb";
            } } 
            else {
                $file_err = "Une erreur s'est produite";    
            }
        }
        else {
            $file_err = "Veuillez selectionner un fichier .txt";    
        }
    }
    else if ($type_quiz == "texte") {
        if (count($_FILES) > 0) {
            if (is_uploaded_file($_FILES['texte']['tmp_name'])) {
              
                $fileSize = $_FILES["texte"]["size"];
                if ($fileSize > $maxsize) {
                    $texte_err = "La taille du fichier ne doit pas dépasser 2Mb";
                } } 
                else {
                    $texte_err = "Une erreur s'est produite";    
                }
            }
            else {
                $texte_err = "Veuillez selectionner un fichier .txt";    
            }
        }
    if (empty($theme_err) && empty($temps_err) && empty($nbr_err) && empty($type_quiz_err) && empty($file_err)
    && empty($texte_err)) {
       $sql = "insert into questionnaire(nom, temps, nbr_questions, type_quest) values(?,?,?,?)";
       if($stmt = mysqli_prepare($link, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "ssss",$param_theme, $param_temps, $param_nbr, 
        $param_type) ;
        // Set parameters
        $param_theme = $theme;
        $param_temps = $temps;
        $param_nbr = $nbr_questions;
        $param_type = $type_quiz;
        
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_close($stmt);
            $id = "";
            // we get last id then insert questions
            $sqlid = mysqli_query($link, "SELECT id_questionnaire FROM questionnaire
             ORDER BY id_questionnaire DESC LIMIT 1");
            while ($row =mysqli_fetch_array($sqlid)) {
                $id = $row['id_questionnaire'];
            }
                    if ($type_quiz == "serie") {
                 $handle = fopen($_FILES["questions"]["tmp_name"], 'r');
                
                        while(!feof($handle)) {
                            $line = explode("|",fgets($handle));
                            $question = $line[0];
                            $rep = $line[2];
                            $choix = explode(",",$line[1]);
                            $choix0 = $choix[0];
                            $choix1 = $choix[1];
                            $choix2 = $choix[2];
                           
                            $sql2 = "insert into questions(question, choix_1, choix_2, choix_3, reponse, id_questionnaire)
                             values(?,?,?,?,?,?)";
                            
                            if($stmt2 = mysqli_prepare($link, $sql2)){
                               
                             // Bind variables to the prepared statement as parameters
                             mysqli_stmt_bind_param($stmt2, "ssssss",$param_quest, $param_choix0, $param_choix1,
                              $param_choix2, $param_rep, $param_id) ;
                             // Set parameters
                             $param_quest= $question;
                             $param_choix0 = $choix0;
                             $param_choix1 = $choix1;
                             $param_choix2 = $choix2;
                             $param_rep = $rep;
                             $param_id= $id;
                          
                            if(mysqli_stmt_execute($stmt2)){ 
                               mysqli_stmt_close($stmt2);
                             }
                        }    
                        }}
                        echo '<script language="javascript">';
                        echo 'alert("Ajouté avec succès")';
                        echo '</script>';   
                    }
                }
              
             } else{
            echo '<script language="javascript">';
            echo 'alert("Veuillez reessayer plus tard")';
            echo '</script>';
            mysqli_stmt_close($stmt);
        }
    // Close connection
    mysqli_close($link);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.js"></script>
    <link rel="stylesheet" href="../css/admin.css"> 
    <script type ="text/javascript">
function dis(){
  var theme = "<?php echo $theme; ?>" ;    
  var temps = "<?php echo $temps; ?>" ;    
  var nbr = "<?php echo $nbr_questions; ?>" ;    
   if (theme || nbr || temps) {
  var x = document.getElementById("form");
    x.style.display = "block";
   }
}
function showForm() {
  var x = document.getElementById("form");

    x.style.display = "block";
  
}
window.onload = dis;
function checktype(that){
    if (that.value == "serie") {
        document.getElementById("serie").style.display = "block";
        document.getElementById("texte").style.display = "none";
    }
    else if (that.value == "texte") {
        document.getElementById("texte").style.display = "block";
        document.getElementById("serie").style.display = "none";
    }
}
function switchStep(id_step) {
         if(id_step == "list_quiz") {
            document.getElementById("list_quiz").style.display = "block";
            document.getElementById("add_quiz").style.display = "none";
            document.getElementById("list_users").style.display = "none";
         }
         else if(id_step == "add_quiz") {
            document.getElementById("list_quiz").style.display = "none";
            document.getElementById("add_quiz").style.display = "block";
            document.getElementById("list_users").style.display = "none";
         }
         else if(id_step == "list_users") {
            document.getElementById("list_quiz").style.display = "none";
            document.getElementById("add_quiz").style.display = "none";
            document.getElementById("list_users").style.display = "block";
         }
}

    </script>
</head>
<body>
  
<div class="page-header">
<div class="title"> Generateur de quiz </div>
    <div>
        <ul class="mynav nav navbar-nav navbar-left">
        <li>
        <h5 style="color: white;" onclick="switchStep('add_quiz')">  Ajouter Quiz </h5>
        </li>
        <li>
        <h5 style="color: white;" onclick="switchStep('list_quiz')"> Liste quiz </h5>
        </li>
        <li>
        <h5 style="color: white;"  onclick="switchStep('list_users')"> Liste etudiants  </h5>
        </li>
        </ul>
        <div class="logout-section">
        <h5 style="color: white; padding-right: 10px;">Bienvenue, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b></h5>
        <a  href="logout.php" class="btn decx btn-info btn-sm">Deconnexion</a>
        </div>
    </div>
    </div>

    <div class="first_wrapper" id="add_quiz"> 
      
<div class="add">
    
<img src="../Assets/button.png" alt="add_button" class="add_button">
<div class="middle">
    <button class="button" onclick="showForm()">Ajouter quiz</button>
  </div>
</div>

<div class="add_form" id="form">
<h2 style="text-align: center;"> Nouveau quiz </h2>
        <p style=" text-align: center; ">Veuillez entrer les informations de quiz.</p>
        <form class="form" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div id = "theme" class="form-group <?php echo (!empty($theme_err)) ? 'has-error' : ''; ?>">
                <label>Theme quiz</label>
                <input type="text" name="theme" class="form-control" value="<?php echo $theme; ?>">
                <span class="help-block"><?php echo $theme_err; ?></span>
            </div>    
            <div id="temps" class="form-group <?php echo (!empty($temps_err)) ? 'has-error' : ''; ?>">
                <label>Temps autorisé en minutes</label>
                <input type="number" name="temps" min="10" max= "120" class="form-control" value="<?php echo $temps; ?>">
                <span class="help-block"><?php echo $temps_err; ?></span>
            </div>
            <div id="quest" class="form-group <?php echo (!empty($nbr_err)) ? 'has-error' : ''; ?>">
                <label>Nombre questions</label>
                <input type="number" name="nbr_questions" min="5" max= "30" class="form-control" value="<?php echo $nbr_questions; ?>">
                <span class="help-block"><?php echo $nbr_err; ?></span>
            </div>
            <div id="type" class="form-group <?php echo (!empty($type_quiz_err)) ? 'has-error' : ''; ?>">
                            <label style="float: left;">Type quiz</label>
                        <select class="form-control" name="type_quiz" onchange="checktype(this);">
                        <option value="select">Selectionnez un type</option>
                         <option value="serie">serie de questions</option>
                         <option value="texte">texte a trous</option>
                        </select>
                        <span class="help-block"><?php echo $type_quiz_err;?></span>
                        </div>
                        <div id= "serie" style="display: none;" class="form-group <?php echo (!empty($file_err)) ? 'has-error' : ''; ?>">
                <label>Ficher questions</label>
                <input type="file" name="questions" accept="text/plain"  class="form-control">
                <span class="help-block"><?php echo $file_err; ?></span>
            </div>
            <div id= "texte" style="display: none;" class="form-group <?php echo (!empty($texte_err)) ? 'has-error' : ''; ?>">
                <label>Ficher texte à trous</label>
                <input type="file" name="texte" accept="text/plain"  class="form-control">
                <span class="help-block"><?php echo $texte_err; ?></span>
            </div>
                          
            <div class="form-group" style="text-align: center;">
                <input type="submit" class="btn btn-success cnx" value="Ajouter">
               
            </div>
</div>

    </div>
   <div class="first-wrapper" style="display: none;" id ="list_quiz">
   <table class="table">
  <thead>
    <tr>
      <th scope="col">Id Quiz</th>
      <th scope="col">Theme</th>
      <th scope="col">Temps autorisé</th>
      <th scope="col">Nombre questions</th>
      <th scope="col">Type questions</th>
    </tr>
  </thead>
  <tbody>
    <?php 
    $result = mysqli_query($link,"SELECT * FROM questionnaire") or die('Error');
    while($row = mysqli_fetch_array($result)) {
    echo'<tr>';
     echo '<th scope="row">'.$row['id_questionnaire'].'</th>
      <td>'.$row['nom'].'</td>
      <td>'.$row['temps'].'</td>
      <td>'.$row['nbr_questions'].'</td>
      <td>'.$row['type_quest'].'</td>';
     echo  '<td>';
      echo "<a href='delete_quiz.php?id=". $row['id_questionnaire'] ."' title='supprimer' data-toggle='tooltip'><span class='glyphicon glyphicon-trash'></span></a>";
     echo'<td>';
     echo '</tr>';
    }
    ?>
  </tbody>
</table>
</div>
     
<div class="first-wrapper" style="display: none;" id ="list_users">
   <table class="table">
  <thead>
    <tr>
      <th scope="col">Identifant</th>
      <th scope="col">Nom d'utilisateur</th>
      <th scope="col">Email</th>
    </tr>
  </thead>
  <tbody>
    <?php 
    $result = mysqli_query($link,"SELECT * FROM users where user_type = 'user'") or die('Error');
    while($row = mysqli_fetch_array($result)) {
    echo'<tr>';
     echo '<th scope="row">'.$row['user_id'].'</th>
      <td>'.$row['user_name'].'</td>
      <td>'.$row['user_email'].'</td>'; 
     echo  '<td>';
      echo "<a href='delete_user.php?id=". $row['user_id'] ."' title='supprimer' data-toggle='tooltip'><span class='glyphicon glyphicon-trash'></span></a>";
     echo'<td>';
     echo '</tr>';
    }
    ?>
  </tbody>
</table>
</div>
</body>
</html>