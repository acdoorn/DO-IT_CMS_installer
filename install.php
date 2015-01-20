<?
session_start();

//Variables
$strOk   = '<i class="icon-ok"></i>';
$strFail = '<i class="icon-remove"></i>';
$strUnknown = '<i class="icon-question"></i>';
$htmlmessage = '<p><form method="post" action="install.php">
    <input name="composer" type="submit" value="Check if Composer is installed"/>
</form></p>';
$message = '';
$space = "\r\n";
$requirements = array();
$_SESSION['projectname'] = 'DOITcms';

// PHP Version
$requirements['php_version'] = (version_compare(PHP_VERSION,"5.3.7",">=") >= 0);

// MCrypt PHP Extension
$requirements['mcrypt_enabled'] = extension_loaded("mcrypt");

// Fileinfo PHP Extension
$requirements['fileinfo_enabled'] = extension_loaded("fileinfo");

// mod_rewrite
$requirements['mod_rewrite_enabled'] = null;

if ( function_exists('apache_get_modules') )
{
    $requirements['mod_rewrite_enabled'] = in_array('mod_rewrite',apache_get_modules());
}

//PHP functions 
function checkIfNameISSET() {
    if(!empty($_POST['projectname'])) {
        return $_POST['projectname'];
    }
    else
    {
        return 'DOITcms';
    }
}
function changeDir() {
    if(strpos(getcwd(), '\\'.checkIfNameISSET()) === false && file_exists(getcwd() . '\\'.checkIfNameISSET())){
        chdir('./'.checkIfNameISSET());
    }
}

function changeDirBack() {
    if(strpos(getcwd(), '\\'.checkIfNameISSET()) !== false){
        chdir('../');
    }
}

function laravelInstalled() {
    changeDir();
    exec('composer show -s', $output);
    if(strpos($output['0'], 'laravel/laravel') !== false) {
        changeDirBack();
        return true;
    }
    else {
        changeDirBack();
        return false;
    }
}

function writeLog($logmessage) {
    $log = fopen('install_log.txt', 'a');
    fwrite($log, $logmessage);
    fclose($log);
}

function changeDatabaseInfo($filename, $host, $database, $username, $password) {
    if(!isset($host)) {
        $host = 'localhost';
    }
    if(!isset($database)) {
        $database = 'forge';
    }
    if(!isset($username)) {
        $username = 'root';
    }
    if(!isset($password)) {
        $password = '';
    }

    $mysql = "
        'driver'    => 'mysql',
        'host'      => '".$host."',
        'database'  => '".$database."',
        'username'  => '".$username."',
        'password'  => '".$password."',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ";

    $file = file_get_contents($filename);

    $splitstring = "'mysql' => array(";
    $splitstring2= "),";

    $splitarray = explode($splitstring, $file);
    $splitarray2 = explode($splitstring2, $splitarray['1'], 2);

    $firstpart = $splitarray['0'];
    $lastpart = $splitarray2['1'];

    $newfile = $firstpart . $splitstring . $mysql . $splitstring2 . $lastpart;

    file_put_contents($filename, $newfile);
}

//Check if laravel is installed, otherwise redirect to the modules page
if(laravelInstalled()){
    $_SESSION['projectname2'] = checkIfNameISSET();
    // header('Location: ./modules.php');
}

if(isset($_POST['change_database'])) {
        $host = 'localhost';
        $database = 'DOITcms';
        $username = 'root';
        $password = '';

        changeDatabaseInfo('./'.$_SESSION['projectname2'].'/app/config/database.php', $host, $database, $username, $password);
        $htmlmessage = '<p><i class="icon-ok"></i> Composer is installed</p><br/>
            <p><i class="icon-ok"></i>Laravel is installed successfully</p><br/>
            <p><h3>Database information</h3></p><br/>
            <form method="post" action="modules.php">
                <input name="laravel_installed" type="submit" value="Install the CMS modules"/>
            </form>';
}

//PHP POST-functions, version-checks & database loops together with HTML
if(isset($_POST['composer'])) {
	exec('composer about', $temp, $notinstalled);

	if(!$notinstalled) {
		$htmlmessage = '<p><i class="icon-ok"></i> Composer is installed</p><br/>
		<form method="post" action="install.php">
            <input name="projectname" id="projectname" type="text" placeholder="Projectname(standard '.$_SESSION['projectname'].')" /><br/><br/>
			<input name="install_laravel" id="install_laravel" type="submit" value="Install Laravel project*"/><br/>
			<small><label for="install_laravel">*This might take a couple minutes</label"></small>
		</form>';
	}
	else {
		$htmlmessage = '<p><i class="icon-remove"></i> Composer is not installed correctly</p>
		<p>Try <a href="https://getcomposer.org/download">https://getcomposer.org/download</a><p>';
	}
}


?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DOIT&dash;Online &dash; CMS Server Requirements</title>
    <link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
    <style>
        @import url(//fonts.googleapis.com/css?family=Lato:300,400,700);
        body {
            margin:0;
            font-size: 16px;
            font-family:'Lato', sans-serif;
            text-align:center;
            color: #999;
        }
        .wrapper {
           width: 30%;
           margin: 50px auto;
        }
        p {
            margin:0;
        }
        p small {
            font-size: 13px;
            display: block;
            margin-bottom: 1em;
        }
        .icon-ok {
            color: #27ae60;
        }
        .icon-remove {
            color: #c0392b;
        }
        input {
            width:200px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
    	<h1>DOIT&dash;Online CMS Server Requirements</h1>
    	<p>
            <?php echo $requirements['php_version'] ? $strOk : $strFail; ?> PHP >= 5.3.7 
        </p>
        <p>
            <?php echo $requirements['mcrypt_enabled'] ? $strOk : $strFail; ?> MCrypt PHP Extension
        </p>
        <h2>Optional Server Requirements.</h2>
        <p>
            <?php echo $requirements['fileinfo_enabled'] ? $strOk : $strFail; ?> Fileinfo PHP Extension 
            <small>Required for MIME-type validation</small>
        </p>
        <?php 
        // mod_rewrite Module
        $strModRewriteEnabled = $strUnknown;

        if (isset($requirements['mod_rewrite_enabled'])) {
            $strModRewriteEnabled = $requirements['mod_rewrite_enabled'] ? $strOk : $strFail;
        }
        ?>
        <p>
            <?php echo $strModRewriteEnabled; ?> mod_rewrite Module 
            <small>Required for Pretty URLs</small>
        </p>
    	<?
    	if($requirements['php_version'] && $requirements['mcrypt_enabled']) {
                    if(laravelInstalled()) {
                        $htmlmessage = '<p><i class="icon-ok"></i> Composer is installed</p><br/>
                        <p><i class="icon-ok"></i>Laravel is installed successfully</p><br/>
                        <p><h3>Database information</h3></p><br/>
                        <form method="post" action="install.php">
                            <input type="text" placeholder="Database host" name="host" id="host"/>
                            <input type="text" placeholder="Database name" name="database" id="database"/>
                            <input type="text" placeholder="Database username" name="username" id="username"/>
                            <input type="password" placeholder="Database password" name="password" id="password"/>
                            <input name="change_database" type="submit" value="Change Database information"/>
                        </form>
                        <form method="post" action="modules.php">
                            <input name="laravel_installed" type="submit" value="Install the CMS modules"/>
                        </form>';
                    }
            if(isset($_POST['install_laravel'])) {
                $cmd = 'composer create-project laravel/laravel '. checkIfNameISSET() . ' --prefer-dist --stability dev';
                while (@ ob_end_flush()); // end all output buffers if any
                $proc = popen($cmd, 'r');
                echo '<pre>';
                while (!feof($proc))
                {
                    echo fread($proc, 4096);
                    writeLog(fread($proc, 4096));
                    @ flush();
                    $_SESSION['projectname'] = 'DOITcms';
                    echo '<script>window.scrollTo(0,document.body.scrollHeight);</script>';
                      if(laravelInstalled()) {
                        $_SESSION['projectname2'] = checkIfNameISSET();
                        $htmlmessage = '<p><i class="icon-ok"></i> Composer is installed</p><br/>
                        <p><i class="icon-ok"></i>Laravel is installed successfully</p><br/>
                        <form method="post" action="install.php">
                            <input type="text" placeholder="Host" name="host" id="host"/>
                            <input type="text" placeholder="Database" name="database" id="database"/>
                            <input type="text" placeholder="Username" name="username" id="username"/>
                            <input type="password" placeholder="Password" name="password" id="password"/>
                            <input name="change_database" type="submit" value="Change Database information"/>
                        </form>
                        <form method="post" action="modules.php">
                            <input name="laravel_installed" type="submit" value="Install the CMS modules"/>
                        </form>';
                    }
                }
                echo '</pre>';
                pclose($proc);
            }
            if(isset($htmlmessage)) 
                echo '<h2 name="bottom">Installing the CMS.</h2>'.$htmlmessage;
                echo '<script>window.scrollTo(0,document.body.scrollHeight);</script>';
        }
	    ?>
    </div>
</body>

