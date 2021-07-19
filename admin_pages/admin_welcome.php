<?php
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php ");
    exit;
}
require_once "../config.php";

$temps = $theme = $nbr_questions = $type_quiz = $form_value = $file = "";
$temps_err = $theme_err = $nbr_err = $type_quiz_err = $file_err = $file_err = "";

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
    } else{
        $type_quiz = $input_type;
    }
    function insertQuestions() { // put this with questionnaire insert
    if (count($_FILES) > 0) {
        if (is_uploaded_file($_FILES['questions']['tmp_name'])) {
            $data = file_get_contents($_FILES['questions']['tmp_name']);
            $fileSize = $_FILES["questions"]["size"];
            $maxsize = 2 * 1024 * 1024;
            if ($fileSize > $maxsize) {
                $GLOBALS['file_err'] = "La taille du fichier ne doit pas dépasser 2Mb";
            }
            else {
                while(!feof($data)) {
                    $line = explode("|",fgets($data));
                    $question = $line[0];
                    $rep = $line[2];
                    $choix = explode(",",$line[1]);
                    $choix0 = $choix[0];
                    $choix1 = $choix[1];
                    $choix2 = $choix[2];
                    
                }
            }
        }
        else {
            $GLOBALS['file_err'] = "Une erreur est survenue avec ce fichier.";
             
        
    }
}
    }

    if (empty($theme_err) && empty($temps_err) && empty($nbr_err) && empty($type_quiz_err)) {
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
            echo '<script language="javascript">';
            echo 'alert("Ajouté avec succès!")';
            echo '</script>';
            
           
        } else{
            echo '<script language="javascript">';
            echo 'alert("Veuillez reessayer plus tard")';
            echo '</script>';
        }
        mysqli_stmt_close($stmt);
        
     
    }
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


    </script>
</head>
<body>
  
<div class="page-header">
<div class="title"> Generateur de quiz </div>
    <div class="bar">
        <h5 style="color: white; padding-right: 10px;">Bienvenue, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b></h5>
        <a  href="logout.php" class="btn decx btn-info btn-sm">Deconnexion</a>
        </div>
    </div>
    <div class="first_wrapper"> 
      
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
                <input type="number" name="temps" min="20" max= "120" class="form-control" value="<?php echo $temps; ?>">
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
                         <option value="serie">serie de questions</option>
                         <option value="texte">texte a trous</option>
                        </select>
                        <span class="help-block"><?php echo $type_quiz_err;?></span>
                        <div  class="form-group <?php echo (!empty($nbr_err)) ? 'has-error' : ''; ?>">
                <label>Ficher questions</label>
                <input type="file" name="questions" accept="txt/*" class="form-control" value="<?php echo $nbr_questions; ?>">
                <span class="help-block"><?php echo $nbr_err; ?></span>
            </div>
                        </div>
            <div class="form-group" style="text-align: center;">
                <input type="submit" class="btn btn-success cnx" value="Ajouter">
               
            </div>
</div>

    </div>

</body>
</html>