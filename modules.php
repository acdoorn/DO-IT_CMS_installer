<?

$username = 'acdoorn';
$password = 'acdoornpass';


session_start();
if(isset($_POST['changeprojectname'])) {
	$_SESSION['projectname'] = $_POST['projectname'];
}

ini_set('max_execution_time', 3000);
$packagecounter = 0;
$totalpackages = 0;
if(isset($_SESSION['projectname2']) && file_exists(getcwd() . '\\' . $_SESSION['projectname2'])) {
	$GLOBALS['projectname'] = $_SESSION['projectname2'];
}
elseif(isset($_SESSION['projectname']) && file_exists(getcwd() . '\\' . $_SESSION['projectname'])) {
	$GLOBALS['projectname'] = $_SESSION['projectname'];
}
elseif(file_exists(getcwd() . '\DOITcms')) {
	$GLOBALS['projectname'] = 'DOITcms';
}

// Queries
$allmodules = "select * from modules";

// Create connection
$con=mysqli_connect("localhost","root","","doitcmsinstall42");

// Check connection
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

//PHP functions
function isInstalledPackage($packagename) {
	changeDir();
	exec('composer show -i', $output);
	foreach($output as $line) {
		$exploded = explode(' ', $line);
		if($exploded['0'] == $packagename) 
			return true;
	}
}

function execInBackground($cmd) { 
    if (substr(php_uname(), 0, 7) == "Windows"){ 
        pclose(popen("start /B ". $cmd, "r"));  
    } 
    else { 
        exec($cmd . " > /dev/null &");   
    } 
}

function changeDir() {
	if(strpos(getcwd(), '\\'.$GLOBALS['projectname']) === false && file_exists(getcwd() . '\\'.$GLOBALS['projectname']))
		chdir('./'.$GLOBALS['projectname']);
}

function checkIfExists($link) {
	// $file_headers = @get_headers($link);
	// if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
	//     return false;
	// }
	// else {
	    return true;
	// }
}

function addProviders($filename, $providerstoadd) {
	$file = file_get_contents($filename);

	$splitstring = "'providers' => array(";
	$splitstring2= "),";

	$splitarray = explode($splitstring, $file);
	$splitarray2 = explode($splitstring2, $splitarray['1'], 2);

	$firstpart = $splitarray['0'];
	$lastpart = $splitarray2['1'];

	$currentproviders = $splitarray2['0'];
	$providerarray = explode(',', $currentproviders);
	array_pop($providerarray);

	$addproviders = array("\n");
			//database implementation here, added newline for every value so that the output remains readable with a lot of providers
			//the (first added) last string in the array provides another comma at the end so the file is valid
	foreach($providerstoadd as $p) {
		array_unshift($addproviders, "\n'$p'");
	}

	foreach($addproviders as $provider){
		if(!in_array($provider, $providerarray)) {//check if provider is in array
			array_push($providerarray, $provider);
		}
	}

	$newfile = $firstpart . $splitstring . implode(',', $providerarray) . $splitstring2 . $lastpart;

	file_put_contents($filename, $newfile);
}

function removeBaseRoute() {
	file_put_contents('./app/routes.php', '<?php ');//route has to be changed in Laravel 5.0(Entire file changes now, might improve this)
}
?>

<!-- HTML together with PHP POST-functions & database loops-->
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DOIT&dash;Online &dash; CMS Server Requirements</title>
    <link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
    <style>
	    table {
			width:80%;
			float:left;
		}
		table, th, td {
			border: solid black 1px;
		}
        @import url(//fonts.googleapis.com/css?family=Lato:300,400,700);
        body {
            margin:0;
            font-size: 16px;
            font-family:'Lato', sans-serif;
            text-align:center;
            color: #999;
        }
        .wrapper {
           width: 60%;
           height:100%;
           margin: auto;
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
        button {
        	width:130px;
        	height:30px;
        }
        .textdiv {
        	width:20%;
        	float:left;
        }
        input[type=checkbox] {
        	height:25px;
        	width:25px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
    	<h1>DOIT&dash;Online CMS Modules Installer</h1>
    	<p>
    		<?php if(isset($GLOBALS['projectname'])) {?>

    		<form method="post" action="modules.php">
			<table>
				<tr>
					<th>Packagename</th>
					<th>Current version</th>
					<th>Github link</th>
					<th>Select</th>
					<th>Installed</th>
				</tr>
				<?php 
					$result = $con->query($allmodules);
					foreach($result as $row) {
						if(checkIfExists($row['repository'])) {
							$totalpackages++;
							echo '
							<tr>
								<td>'.$row['name'].'</td>
								<td>'.$row['version'].'</td>
								<td><a target="_blank" href="'.$row['repository'].'">'.$row['link'].'</a></td>';
								if(isInstalledPackage($row['link'])) {
									echo '<td><input disabled type="checkbox"/></td>
											<td><i class="icon-ok"></i></td>';
									$packagecounter++;
								}
								elseif ($row['alwaysactivated'] == '1' && !isInstalledPackage($row['link'])) {
									echo '<td><input checked="checked" disabled name="modules[]" value="'.$row['id'].'" type="checkbox"/>
									<input name="modules[]" value="'.$row['id'].'" type="hidden"/></td>
											<td><i class="icon-remove"></i></td>';
								}
								else {
									echo '<td><input name="modules[]" value="'.$row['id'].'" type="checkbox"/></td>
											<td><i class="icon-remove"></i></td>';
								}
							echo '</tr>';
						}
					} ?>
			</table>
			<?php if($packagecounter == $totalpackages) {echo '<button disabled type="submit">Install modules</button>';}
			else {echo '<button type="submit" id="updatecomposer" name="updatecomposer" value="">Install modules</button>';}
			?>
		</form>
<?php } else {?>
	<h2>Select the folder(projectname) where the project resides.</h2>
	<form method="post" action="modules.php">
		<select name="projectname" required>
		  <?php 
		       foreach(glob(dirname(__FILE__) . '/*') as $filename){
			       $filename = basename($filename);
			       if(is_dir($filename))
				       echo "<option value='" . $filename . "'>".$filename."</option>";
			    }
			?>
		</select> 
		<input type="submit" id="changeprojectname" name="changeprojectname" value="Select folder(projectname)"/>
	</form>
<?php
}
echo '</p>';



if(isset($_POST['updatecomposer'])) {
	if(empty($_POST['modules'])) 
	  {
	    echo('<div class="textdiv"><b><br/>No extra modules selected</b></div>');
	  } 
  	else
	  {
		$modules = $_POST['modules'];
		changeDir();
		removeBaseRoute();
		$providers = array();
		exec('composer config http-basic.github.com '.$username.' '.$password);
		//exec('composer config github-oauth.github.com 7c41f0eddad28449d9f761c28f522dc218ea4fda');
		foreach($modules as $module) {
			foreach ($result as $row) {
				if($module == $row['id']) {
					exec('composer config repositories.'.$row['name'].' vcs '.$row['repository']);
					exec('composer require '.$row['link'].' ' .$row['version']);
					execInBackground('composer update');
					array_push($providers, $row['serviceprovider']);
				}
			}
		}
		exec('php artisan asset:publish');
		addProviders('../'.$GLOBALS['projectname'].'/app/config/app.php', $providers);//routing has to be changed in Laravel 5.0
	    echo('<div class="textdiv"><b><br/>The modules are being installed, this might take a couple of minutes</b></div>');
	}
} ?>
	</div>
</body>
</html>