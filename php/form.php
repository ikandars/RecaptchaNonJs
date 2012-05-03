<?php
require_once 'RecaptchaNonJs.php';

$error = false;
$success = false;
$recaptcha = new RecaptchaNonJs('YOUR_PUBLIC_KEY', 'YOUR_PRIVATE_KEY');

if( isset($_POST['submit']) ){
    
    if( ! $success = $recaptcha->verify($_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']) )
        $error = true;
}

$data = $recaptcha->getChallenge();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Recaptcha Form</title>
</head>
<body>
    <?php if($success): ?>
    <p>Validation Success!</p>
    <?php endif; ?>
    <?php if($error): ?>
    <p><?php echo $recaptcha->errorMessage;?></p>
    <?php endif; ?>
<img src="<?php echo $data['image'];?>" />
<form action="" method="post">
    <input type="text" autocapitalize="off" autocorrect="off" id="recaptcha_response_field" name="recaptcha_response_field" autocomplete="off" /><br />
    <input type="hidden" id="recaptcha_challenge_field" name="recaptcha_challenge_field" value="<?php echo $data['challenge'];?>" /><br />
    <input type="submit" name="submit" value="Submit" />
</form>
</body>
</html>