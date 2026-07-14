<?php
//Sort le mot de passe crypté, à ensuite copier dans ADMIN/.htpasswd. Il peut y avoir plusieurs mot de passes en même temps, mais pas 2 fois le même login

if (isset($_POST['login']) AND isset($_POST['pass']))
{
    $login = $_POST['login'];
   // $pass_crypte = crypt($_POST['pass'], PASSWORD_DEFAULT); 
    $pass_crypte=password_hash($_POST['pass'], PASSWORD_DEFAULT);// On crypte le mot de passe

    echo '<p>Ligne à copier dans le .htpasswd :<br />' . $login . ':' . $pass_crypte . '</p>';
}

else // On n'a pas encore rempli le formulaire
{
?>

<p>Entrez votre login et votre mot de passe pour le crypter.</p>

<form method="post">
    <p>
        Login : <input type="text" name="login"><br />
        Mot de passe : <input type="text" name="pass"><br /><br />
    
        <input type="submit" value="Crypter !">
    </p>
</form>

<?php
}
?>