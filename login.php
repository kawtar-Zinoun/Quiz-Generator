<?php
// Initialize the session
session_start();
 
// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    if ( strcmp($_SESSION["user_type"] ,"admin") == 0) {
        header("location: admin_pages/admin_welcome.php");
        exit;
       }
       else if ( strcmp($_SESSION["user_type"] ,"user") == 0) {
        header("location: user_pages/user_welcome.php");
         exit;
       }
}
 
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Veuillez entrer un nom d'utilisateur";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Veuillez entrer un mot de passe";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT * FROM users WHERE user_name = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if username exists, if yes then verify password
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $email, $db_password, $type);
                    if(mysqli_stmt_fetch($stmt)){
                        if(strcmp($password, $db_password) == 0){
                            // Password is correct, so start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;                            
                            $_SESSION["email"] = $email;     
                            $_SESSION["user_type"] = $type;                       
                           if ( strcmp($_SESSION["user_type"] ,"admin") == 0) {
                            header("location: admin_pages/admin_welcome.php");
                           }
                         else if ( strcmp($_SESSION["user_type"] ,"user") == 0) {
                            header("location: user_pages/user_welcome.php");
                           }
                        } else{
                            // Display an error message if password is not valid
                            $password_err = "Mot de passe non valide.";
                        }
                    }
                } else{
                    // Display an error message if username doesn't exist
                    $username_err = "Cet utilisateur n'existe pas.";
                }
            } else{
                echo "Veuillez reessayer plus tard.";
            }

            // Close statement
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
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <link rel="stylesheet" href="css/login.css">
    
</head>
<body>
    
    <div class="wrapper">
        <div class="title">
          <div class="gen">
              Generateur de Quiz
          </div>
         <img src="Assets/344201-PAOSF6-481.jpg" alt="quiz">
        </div>
    <div class="login">
       <h2 style="text-align: center;"> Connexion </h2>
        <p style=" text-align: center; ">Veuillez entrer vos informations pour se connecter.</p>
        <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Nom d'utlisateur</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Mot de passe</label>
                <input type="password" name="password" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-success cnx" value="Connexion">
            </div>
</div>
        </form>
    </div>    
</body>
</html>