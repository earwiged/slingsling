<?php
/**

Copyright 2015 JQueryForm.com
License: http://jqueryform.com/license.php

FormID: jqueryform-c96556
Date: 2015-11-02 19:47:59
Generated by http://www.jqueryform.com

IF your form uses mailgun AND the form has file upload field, PHP 5.5+ is required.

*/

namespace JF;

require_once( __DIR__ . '/phpmailer.php' );
require_once( __DIR__ . '/form.lib.php' );
use JF\Form;

class DataManager
{
    var $dataFile = '';
    var $columns = '';
    var $records = '';

    function __construct(){
        $this->dataFile = Form::getFormDataFile();;
    }

    private function parseFile(){
        $fp = @fopen($this->dataFile, 'rb');
        if( !$fp ) return false;

        $i = 0 ;
        $phpExitLine = 1; // first line is php code
        $colsLine = 2 ; // second line is column headers
        $this->columns = array();
        $this->records = array();
        $sep = chr(0x09);
        while( !feof($fp) ) {
            $line = fgets($fp);
            $line = trim($line);
            if( empty($line) ) continue;
            $line = $this->line2display($line);
            $i ++ ;
            switch( $i ){
                case $phpExitLine:
                    continue;
                    break;
                case $colsLine :
                    $this->columns = explode($sep,$line);
                    break;
                default:
                    $this->records[] = explode( $sep, self::data2record( $line, false ) );
            };
        };
        fclose ($fp);
    }


    public static function data2record( $s, $b=true ){
        $from = array( "\r", "\n");
        $to   = array( "\\r", "\\n" );
        return $b ? str_replace( $from, $to, $s ) : str_replace( $to, $from, $s ) ;
    }


    public function displayRecords(){
        $this->parseFile();
        echo '<table class="table table-striped table-bordered">';
        echo "<tr><td>&nbsp;</td><td><b>" . join( "</b></td><td>&nbsp;<b>", $this->columns ) . "</b></td></tr>\n";
        $i = 1;
        foreach( $this->records as $r ){
            echo "<tr><td align=right>{$i}&nbsp;</td><td>" . join( "</td><td>&nbsp;", $r ) . "</td></tr>\n";
            $i++;
        };
        echo "</table>\n";
    }

    private function line2display( $line ){
        $line = str_replace( array('"' . chr(0x09) . '"', '""'),  array(chr(0x09),'"'),  $line );
        $line = substr( $line, 1, -1 ); // chop first " and last "
        return $line;
    }

}
# end of class


class Admin {

    public static function main(){
        self::init();

        $method = isset($_REQUEST['method'])  ? $_REQUEST['method']  : 'unknown';
        if( !method_exists('\JF\Admin', $method) ){
            self::defaultUI();
            exit;
        };

        $isPublic = false !== strpos('|validateForm|forgotPassword|logout|', "|$method|" );
        if( $isPublic ){
            self::$method();
            exit;
        };

        return self::isLoggedIn() ? self::$method() : self::defaultUI();
    }


    private static function init(){

      error_reporting( E_ERROR );
      ini_set( 'magic_quotes_runtime', 0 );
      ini_set( 'max_execution_time', 0 );
      ini_set( 'max_input_time', 36000 );

      session_start();

      if( !isset($_SESSION['HTTP_REFERER']) )
        $_SESSION['HTTP_REFERER'] = $_SERVER['HTTP_REFERER'] ;

      self::checkReferer();

      if ( get_magic_quotes_gpc() && isset($_POST) ) {
          self::stripSlashes( $_POST );
      };

    }


    private static function checkReferer(){
    }


    private static function getUsers(){
        self::$users = Form::getAdminUsers();
        return self::$users;
    }


    private static function isMySite(){
        $mySites = array('formmail-maker.com', 'jqueryform.com');
        return in_array( strtolower($_SERVER['HTTP_HOST']), $mySites );
    }


    private static function stripSlashes(){
        if(!is_array($var)) {
            $var = stripslashes($var);
        } else {
            array_walk($var, array('self', 'stripSlashes') );
        };
    }

    private static function isLoggedIn(){
        return ( isset($_SESSION['authenticated']) && true === $_SESSION['authenticated'] );
    }


    private static function logout(){
        session_destroy();
        self::redirect();
    }


    private static function redirect( $method = '', $url='', $extra = ''){
        $url = self::makeUrl( $method, $url, $extra );
        header("Location: " . $url );
        exit;
    }


    private static function makeUrl( $method = '', $url = '', $extra = ''){
        $url = empty($url) ? $_SERVER['PHP_SELF'] : $url ;
        if( !empty($method) ){
            $url .= '?method=' . $method;
        };
        if( !empty($extra) ){
            $url .= $extra;
        };
        return $url;
    }


    private static function auth(){
        if( self::isLoggedIn() ){
            return true;
        };

        $to = strtolower(Form::getToEmail());
        $email = trim(strtolower($_POST['email']));
        $pass  = trim($_POST['password']);

        self::getUsers();
        foreach( self::$users as $u ){
            if( (strtolower($u['user']) == $email || $to == $email )&& $u['password'] == $pass ){
                $_SESSION['authenticated'] = true ;
                if( !empty($_POST['redirect']) ){
                    $_SESSION['redirect'] = $_POST['redirect'];
                };
                return true;
            };
        }; // foreach

        return false;
    }


    private static function defaultUI(){
        self::auth();

        self::header();
        if( self::isLoggedIn() ){
            self::showAdminPanel();
        }else{
            self::showLoginForm();
        }
        self::footer();
    }


    private static function showAdminPanel(){
?>
<style type="text/css">

#menu {
  max-width: 450px;
  padding: 15px;
  margin: 0 auto;
  font-size: 150%;
}

#menu ul li{
    list-style: none;
    border-bottom: 1px solid #cccccc;
    padding-bottom: 20px;
    margin-top: 20px;
}

#menu a{
    display: inline-block;
    width: 30%;
    text-align: center;
}

#menu ul{
    padding: 0px;
}

</style>

<?php
if (!empty($_SESSION['redirect']) ){
    echo '<iframe src="' . $_SESSION['redirect'] . '" style="display:none;width:1px;height:1px;" ></iframe>';
    $_SESSION['redirect'] = null;
    unset($_SESSION['redirect']);
};
?>

<section id="menu">
<ul>
    <li>
        <h3>Email Traffics</h3>
        <a href="<?php echo self::makeUrl('viewEmailLog'); ?>">view</a>
        <a href="<?php echo self::makeUrl('downloadEmailLog'); ?>">download</a>
        <a href="<?php echo self::makeUrl('deleteEmailLog'); ?>">delete</a>
    </li>

    <li>
        <h3>Form Data</h3>
        <a href="<?php echo self::makeUrl('viewFormData'); ?>">view</a>
        <a href="<?php echo self::makeUrl('downloadFormData'); ?>">download</a>
        <a href="<?php echo self::makeUrl('deleteFormData'); ?>">delete</a>
    </li>

    <li>
        <h3>Your Form</h3>
<?php
$editFormUrl = 'http://www.jqueryform.com/builder.php';
?>
        <form name="frmJQueryForm" action='<?php echo $editFormUrl; ?>' method='post' enctype='multipart/form-data' target="_parent">
        <textarea id="externalFormConfig" name="externalFormConfig" style="display: none;"></textarea>
        </form>

<pre style="display: none;" id="jsonConfig">
<?php echo Form::getConfig(false); ?>
</pre>

        <a href="<?php echo $editFormUrl; ?>"  onclick="document.frmJQueryForm.submit(); return false;" title="Edit your form on jqueryform.com">edit</a>
        <a href="<?php echo $editFormUrl; ?>">create</a>
        <a href="http://www.jqueryform.com/help">help</a>
    </li>

    <li>
        <h3>System</h3>
        <a href="<?php echo self::makeUrl('phpinfo'); ?>">php info</a>
    </li>

</ul>
</section>
<?php
    } // showAdminPanel()


    private static function doLogFile($logId, $action){
        $files = array(
            'email' => array('file' => Form::getEmailLogFile(), 'as' => 'email-log.txt' ),
            'data' => array('file' => Form::getFormDataFile(), 'as' => 'form-data.csv' )
        );
        $file = $files[$logId];

        if( !is_file($file['file']) ){
            self::header();
            echo "<b>No " . ($logId == 'email' ? 'email traffic log' : 'form data') . " found.</b>";
            self::footer();
            return;
        };

        switch ($action) {
            case 'view':
                self::header();
                if( $logId == 'data' ){
                    $dm = new DataManager();
                    $dm->displayRecords();
                }else{
                    $content = file_get_contents( $file['file'] );
                    echo "<pre>" .htmlspecialchars($content) . "</pre>";
                };
                self::footer();
                break;

            case 'download':
                self::download( $file['file'], $file['as'], true, 1 ); // skip the first line
                break;

            case 'delete':
                self::header();
                $yes = unlink( $file['file'] );
                echo ( $yes ? 'Log file deleted.' : 'Failed to delte log file.' );
                self::footer();
                break;
        }; // switch
    } // doLogFile()


    private static function phpinfo(){
        phpinfo();
    } // phpinfo()

    private static function viewFormData(){
        self::doLogFile('data', 'view');
    } // viewFormData()


    private static function viewEmailLog(){
        self::doLogFile('email', 'view');
    } // viewEmailLog()


    private static function deleteFormData(){
        self::doLogFile('data', 'delete');
    } // viewFormData()


    private static function deleteEmailLog(){
        self::doLogFile('email', 'delete');
    } // viewEmailLog()


    private static function downloadFormData(){
        self::doLogFile('data', 'download');
    } // downloadFormData()


    private static function downloadEmailLog(){
        self::doLogFile('email', 'download');
    } // downloadEmailLog()

    private static function downloadAttachment(){
        $id = $_REQUEST['id'];
        if( empty($id) ){
            return false;
        };

        $file = './data/' . $id;
        return self::download( $file );
    }

    private static function validateForm(){
        $result = array(
            'validated' => false,
            'invalid' => array()
        );

        Form::validate($_POST);
        if( !Form::isValid() ){
            $result['invalid'] = Form::getInvalidFields();
        }else{
            $result['validated'] = true;
            $result['fieldValues'] = Form::getValues();
            unset( $result['fieldValues']['dataTable'] ); // remove sensitive data
            self::sendmail();
        };

        //header('Content-Type: application/json'); // IE 8 treats it as a download
        header('Content-Type: text/html');
        echo json_encode($result);
    } // validateForm()


    private static function sendmail(){
        $method = Form::getDataDelivery();
        if( 'fileOnly' == $method ){
            return;
        };

        $mailer = new Mailer();
        $mailer->validateForm();
        $ok = $mailer->Send();
        self::sendAutoResponseEmail( $mailer );
        return $ok ;
    }


    private static function sendAutoResponseEmail( $mailer ){
        $senderEmail = Form::getSenderEmail();
        $body = Form::getAutoResponseMailBody();
        if( empty($senderEmail) || empty($body) ){
            return false;
        };

        $mailer->From = Form::getFromEmail(true);
        $mailer->FromName = Form::getFromName(true);
        $mailer->TO = $senderEmail;
        $mailer->CC = ''; //Form::getToEmail();
        $mailer->BCC = '';

        $replyTo = Form::getReplyToEmail();
        if( !empty($replyTo) ){
            $mailer->ReplyTo = $replyTo;
            $mailer->ReplyToName = Form::getReplyToName();
        };

        $mailer->Subject = Form::getAutoResponseMailSubject();
        $mailer->Body = $body;

        return $mailer->Send();
    }


    private static function isPost(){
        return 'POST' == strtoupper($_SERVER["REQUEST_METHOD"])  || 'POST' == strtoupper(getEnv('REQUEST_METHOD'))  ;
    } // isPost()


    private static function showLoginForm(){
?>
<style type="text/css">

#signin {
  max-width: 330px;
  padding: 15px;
  margin: 0 auto;
}

#signin input[type=text]{
    font-size: 150%;
}

.submit{
    text-align: center;
}
.submit .btn{
    width: 85%;
}

.submit a{
    display: block;
    margin: 10px;
}

</style>
<!-- ----------------------------------------------- -->
<div class="container fmg-form">
<form name="signin" id="signin" action='admin.php' method='post' enctype='multipart/form-data'>
<?php if( 'downloadAttachment' == $_REQUEST['method'] ) : ?>
<input type="hidden" name="redirect" value="<?php echo Form::requestUri(); ?>" />
<?php endif; ?>
<?php if( !empty($_REQUEST['redirect']) ) : ?>
<input type="hidden" name="redirect" value="<?php echo $_REQUEST['redirect']; ?>" />
<?php endif; ?>

<div class="form-group email">
  <label class="control-label" for="email">Email or Username</label>
  <input type="text" class="form-control input-lg" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email']); ?>" />
</div>

<div class="form-group password">
  <label class="control-label" for="password">Password</label>
  <input type="password" class="form-control input-lg" id="password" name="password" value="" />
</div>

<div class="form-group submit">
    <input class="btn btn-primary btn-lg" type="submit" value="Sign In">
    <?php if(self::isPost()) : ?>
    <p class="error bg-warning" style="padding:8px;margin-top:10px;"> Log In Failed. </p>
<?php endif;

$to = Form::getToEmail();
if( !empty($to) ):
?>
    <br>
    <a href="<?php echo self::makeUrl('forgotPassword'); ?>">Forgot password</a>
<?php
endif;
?>
</div>

</form>
</div>
<!-- ----------------------------------------------- -->

<?php
    } // showLoginForm()


    private static function passwords(){
        self::getUsers();
        $a = array();
        foreach( self::$users as $u ){
            $a[] = "username: " . $u['user'] . " password: " . $u['password'];
        };
        return $a;
    }

    private static function emailPassword(){
        $mailer = new Mailer();
        $body = "All the users information:<br><br>" .
                join( "<br><br>", self::passwords() ) ;
        return $mailer->mail( Form::getToEmail(), 'Your Password of JQueryForm Admin Panel', $body, Form::getToEmail() );
    }


    private static function forgotPassword(){
        self::header('Request Password');
?>
<style type="text/css">

#forgot {
  max-width: 450px;
  padding: 15px;
  margin: 0 auto;
}

#forgot input[type=text]{
    font-size: 150%;
}

.submit{
    text-align: center;
}
.submit .btn{
    width: 85%;
}

.submit a{
    display: block;
    margin: 10px;
}

</style>
<!-- ----------------------------------------------- -->
<div class="container fmg-form">
<form name="forgot" id="forgot" action='admin.php?method=forgotPassword' method='post' enctype='multipart/form-data'>

<?php
$to = Form::getToEmail();
$email = strtolower($_POST['email']);
if( !empty($to) && !empty($email) && strtolower($to) == $email ):
    self::emailPassword();
    echo "Password has been sent to $email." ;
    echo "<p><a href='admin.php'>Sign In Here</a></p>" ;
else:
?>

<div class="form-group email">
  <label class="control-label" for="email">Enter Email (<?php echo self::getEmailHint( $to ); ?>):</label>
  <input type="text" class="form-control input-lg" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email']); ?>" />
  <p>The password will be sent to this email address.</p>
</div>

<div class="form-group submit">
    <input class="btn btn-primary btn-lg" type="submit" value="Verify">
    <?php if(self::isPost()) : ?>
    <p class="error bg-warning" style="padding:8px;margin-top:10px;">Failed to verify your email.</p>
<?php endif; ?>
</div>

<?php
endif;
?>
</form>
</div>
<!-- ----------------------------------------------- -->

<?php
        self::footer();
    } // forgotPassword()


    private static function getEmailHint( $email ){
        $n1 = strpos($email,'@');
        $n2 = strrpos($email,'.');
        $email = substr($email,0,1) . str_repeat('*',$n1-1) .
                '@' . substr($email,$n1+1,1) . str_repeat('*',$n2-$n1-2) .
                '.' . substr($email,$n2+1,1) . str_repeat('*',strlen($email)-$n2-2) ;
        return $email;
    } // getEmailHint()


    private static function header( $title = '' ){
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Form Admin Panel | Generated by JQueryForm.com</title>

  <!-- Bootstrap -->
  <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
  <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css" rel="stylesheet">
  <link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.0/css/bootstrap-datepicker3.min.css" rel="stylesheet">
  <link href="//cdnjs.cloudflare.com/ajax/libs/jquery-handsontable/0.10.2/jquery.handsontable.full.min.css" rel="stylesheet">

<style>
/* Sticky footer styles
-------------------------------------------------- */
html {
  position: relative;
  min-height: 100%;
}
body {
  /* Margin bottom by footer height */
  margin-bottom: 60px;
}
.footer {
  position: absolute;
  bottom: 0;
  width: 100%;
  /* Set the fixed height of the footer here */
  height: 60px;
  background-color: #f5f5f5;
}

.container .text-muted {
  margin: 20px 0;
  text-align: center;
}

#header{
    text-align: center;
    margin-bottom: 20px;
    background-color: #E1E1E0;
    padding-bottom: 10px;
    line-height: 36px;
}

#header h1{
    display: inline-block;
}
#header span{
    display: inline-block;
    text-align: rignt;
}
#header a{
    margin-left: 20px;
}

#mainContent{
  padding-right: 36px;
  padding-left: 36px;
  margin-right: auto;
  margin-left: auto;
}
</style>

</head>
<body>

<section id="header">
    <div class="container">
        <h1><?php echo empty($title) ? 'Form Admin Panel' : $title; ?></h1>
        <span>
        <?php if( self::isLoggedIn() ): ?>
        <a href="<?php echo self::makeUrl(); ?>">Main Menu</a>
        <a href="<?php echo self::makeUrl('logout'); ?>">Logout</a>
        <?php endif; ?>
        </span>
    </div>
</section>
<section id="mainContent">
<?php
    } // header()


    private static function footer(){
?>
</section>

    <footer class="footer">
      <div class="container">
        <p class="text-muted">Generated by <a href="http://www.jqueryform.com" target="_blank">JQueryForm.com</a></p>
      </div>
    </footer>


    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js"></script>
    <script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/additional-methods.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-scrollTo/2.1.0/jquery.scrollTo.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-handsontable/0.10.2/jquery.handsontable.full.min.js"></script>

  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
  <script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
  <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

<script type="text/javascript">
    $('#externalFormConfig').val( $('#jsonConfig').text() );
</script>

</body>
</html>
<?php
    } // footer()


    private static function download($file, $filename='', $toCSV = false, $skipN = 0 ){
        if (!is_file($file)) return false ;

        set_time_limit(0);

        $buffer = "";
        $i = 0 ;
        $fp = @fopen($file, 'rb');
        while( !feof($fp)) {
            $i ++ ;
            $line = fgets($fp);
            if($i > $skipN){ // skip lines
                if( $toCSV ){
                  $line = str_replace( chr(0x09), ',', $line );
                  $buffer .= DataManager::data2record( $line, false );
                }else{
                    $buffer .= $line;
                };
            };
        };
        fclose ($fp);

        /*
            If the Content-Length is NOT THE SAME SIZE as the real conent output, Windows+IIS might be hung!!
        */
        $len = strlen($buffer);
        $filename = basename( '' == $filename ? $file : $filename );
        $file_extension = strtolower(substr(strrchr($filename,"."),1));

        switch( $file_extension ) {
            case "pdf": $ctype="application/pdf"; break;
            case "exe": $ctype="application/octet-stream"; break;
            case "zip": $ctype="application/zip"; break;
            case "doc": $ctype="application/msword"; break;
            case "xls": $ctype="application/vnd.ms-excel"; break;
            case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
            case "gif": $ctype="image/gif"; break;
            case "png": $ctype="image/png"; break;
            case "jpeg":
            case "jpg": $ctype="image/jpg"; break;
            case "mp3": $ctype="audio/mpeg"; break;
            case "wav": $ctype="audio/x-wav"; break;
            case "mpeg":
            case "mpg":
            case "mpe": $ctype="video/mpeg"; break;
            case "mov": $ctype="video/quicktime"; break;
            case "avi": $ctype="video/x-msvideo"; break;
            //The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
            case "php":
            case "htm":
            case "html":
                    $ctype="text/plain"; break;
            default:
                $ctype="application/x-download";
        }


        //Begin writing headers
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        //Use the switch-generated Content-Type
        header("Content-Type: $ctype");
        //Force the download
        header("Content-Disposition: attachment; filename=".$filename.";" );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".$len);

        while (@ob_end_clean()); // no output buffering !
        flush();
        echo $buffer ;

        return true;

    } // download()


    private static $method;
    private static $users;

} // end of class Admin



// ----------------------
Admin::main();
exit;