<?php
  $error = "";
  if ( isset($_POST["db"]) ) {
    if ( c_psql::connect($_POST["db"]["hostname"],$_POST["db"]["root"],$_POST["db"]["root_password"],"postgres") ) {
      $query = "SELECT usesuper FROM pg_user WHERE usename = CURRENT_USER";
      if ( ($obj = c_psql::get_first_object($query)) ) {
        if ( $obj->usesuper == "t" ) {
          die();
        }
      }
    } else {
      $error =  "Unable to connect to database with provided information";
    }
  }
?>
<html>
  <head>
    <title>Infinie Configuration Wizard</title>
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <style type="text/css">
      * { font-family: Verdana; }
      form { display: block; }
    </style>
  </head>
  <body>
    <p>This is the configuration wizard for Infinie Framework. Please provide the following information to start your joyfull experience</p>
    <form method='post' action='/'>
      <table align='left' border='0'>
        <tr>
          <td colspan='2'><p style='border-bottom: solid 1px'>Database Access Information</p></td>
        </tr>
        <tr>
          <td>hostname:</td>
          <td><input size='60' type='text' name='db[hostname]' value='<?php echo @$_POST['db']['hostname']; ?>' /></td>
        </tr>
        <tr>
          <td>admin account:</td>
          <td><input size='60' type='text' name='db[root]' value='<?php echo @$_POST['db']['root']; ?>' /></td>
        </tr>
        <tr>
          <td>admin password:</td>
          <td><input size='60' type='password' name='db[root_password]' value='' /></td>
        </tr>
        <tr>
          <td colspan='2'><br /><p style='border-bottom: solid 1px'>Database Creation Information</p></td>
        </tr>
        <tr>
          <td>database name:</td>
          <td><input size='60' type='text' name='db[name]' value='' /></td>
        </tr>
        <tr>
          <td>user:</td>
          <td><input size='60' type='text' name='db[user]' value='' /></td>
        </tr>
        <tr>
          <td>password:</td>
          <td><input size='60' type='password' name='db[password]' value='' /></td>
        </tr>
        <tr>
          <td colspan='2' ><br /><p style='border-bottom: solid 1px'>Master Account</p></td>
        </tr>
        <tr>
          <td>user:</td>
          <td><input size='60' type='text' name='adm[user]' value='' /></td>
        </tr>
        <tr>
          <td>password:</td>
          <td><input size='60' type='password' name='adm[password]' value='' /></td>
        </tr>
        <tr>
          <td colspan='2' align='center'><input type='submit' value='OK'><p><?php echo $error?></p></td>
        </tr>
      </table>
    </form>
  </body>
</html>
