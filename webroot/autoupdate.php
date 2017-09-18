<?php
/**
 * Created by PhpStorm.
 * User: cyrillbolliger
 * Date: 17.09.17
 * Time: 10:18
 */

require_once dirname(dirname(__FILE__)) . '/autoupdate/Updater.php';

$updater = new \Autoupdate\Updater();

if (isset($_GET['current_version'])) {
    $updater->setCurrentVersion($_GET['current_version']);
}

if (isset($_GET['step'])) {
    $debug_start = '<br>--- BEGIN DEBUG INFORMATION ---<br>';
    $debug_end = '</pre><br>--- END DEBUG INFORMATION ---';
    $status = false;
    $msg = 'Error<br>Update process stopped. Your unupdated application still works.'.$debug_start;
    $nextStep = -1;
    switch ($_GET['step']) {
        case 1:
            try {
                $status = $updater->backupFiles();
                $msg = 'successful<br>Starting database backup... ';
                $nextStep = 2;
            } catch (\Exception $e) {
                $msg .= $e->getMessage().'<br><pre>'.$e->getTraceAsString().$debug_end;
            }
            break;
            
        case 2:
            try {
                $status = $updater->backupDatabase();
                $msg = 'successful<br>Downloading update... ';
                $nextStep = 3;
            } catch (\Exception $e) {
                $msg .= $e->getMessage().'<br><pre>'.$e->getTraceAsString().$debug_end;
            }
            break;
            
        case 3:
            try {
                $status = $updater->downloadNewestVersion();
                $msg = 'successful<br>Extracting files... ';
                $nextStep = 4;
            } catch (\Exception $e) {
                $msg .= $e->getMessage().'<br><pre>'.$e->getTraceAsString().$debug_end;
            }
            break;
            
        case 4:
            try {
                $status = $updater->extractFiles();
                $msg = 'successful<br>Deleting old files... ';
                $nextStep = 5;
            } catch (\Exception $e) {
                $msg .= $e->getMessage().'<br><pre>'.$e->getTraceAsString().$debug_end;
            }
            break;
            
        case 5:
            try {
                $status = $updater->deleteOldFiles();
                $msg = 'successful<br>Moving new files... ';
                $nextStep = 6;
            } catch (\Exception $e) {
                $nextStep = 201;
                $msg = 'Error<br>Please wait, we are trying to restore your old installation. More information on the very bottom.'.$debug_start.$e->getMessage().'<br><pre>'.$e->getTraceAsString().$debug_end.'<br>Start restoring old installation from backup... ';
            }
            break;
            
        case 6:
            try {
                $status = $updater->moveFiles();
                $msg = 'successful<br>Deleting temporary files... ';
                $nextStep = 7;
            } catch (\Exception $e) {
                $nextStep = 201;
                $msg = 'Error<br>Please wait, we are trying to restore your old installation. More information on the very bottom.'.$debug_start.$e->getMessage().'<br><pre>'.$e->getTraceAsString().$debug_end.'<br>Start restoring old installation from backup... ';
            }
            break;
            
        case 7:
            try {
                $status = $updater->deleteTempFiles();
                $msg = 'successful<br>Updating database... ';
                $nextStep = 8;
            } catch (\Exception $e) {
                $nextStep = 201;
                $msg = 'Error<br>Please wait, we are trying to restore your old installation. More information on the very bottom.'.$debug_start.$e->getMessage().'<br><pre>'.$e->getTraceAsString().$debug_end.'<br>Start restoring old installation from backup... ';
            }
            break;

        case 8:
            try {
                $status = $updater->updateDatabase();
                $msg = 'successful<br>---<br>Update successfully terminated!';
                $nextStep = 100;
            } catch (\Exception $e) {
                $nextStep = 201;
                $msg = 'Error<br>Please wait, we are trying to restore your old installation. More information on the very bottom.'.$debug_start.$e->getMessage().'<br><pre>'.$e->getTraceAsString().$debug_end.'<br>Start restoring old installation from backup... ';
            }
            
            break;
        case 201:
            // ToDo restore files from backup
            break;
    }
    $data = ['status'=>$status, 'msg'=>$msg, 'nextStep'=>$nextStep];
    $return = json_encode($data);
    die($return);
}


$updater->detectCurrentVersion();

// if no update is aviable
$noupdate = false;
if ( ! $updater->isUpdateAvailable()) {
    $noupdate = true;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Updater</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script>
        var current_version = '<?= $updater->getCurrentVersion() ?>';
        var current_page = '<?= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] ?>';
    </script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
          integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp"
          crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous"></script>
</head>
<body style="padding-top: 20px; padding-bottom: 20px;">
<div class="container">
    <div class="header clearfix">
        <nav>
            <ul class="nav nav-pills pull-right">
                <li role="presentation"><a class="btn btn-default"
                            href="<?= "//" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']); ?>">Back</a>
                </li>
            </ul>
        </nav>
        <h3 class="text-muted">Update Breeders Database</h3>
    </div>
    
    <?php if($noupdate): ?>
        <div class="jumbotron">
            <h1>Perfect!</h1>
            <p class="lead">Your installation is up to date. You may now return to the application.</p>
            <p><a class="btn btn-lg btn-success" href="<?= "//" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']); ?>" role="button">Return to application</a></p>
        </div>
    <?php else: ?>
        <div class="jumbotron">
            <h1>Please update</h1>
            <p class="lead">An update is ready to be installed. If the updating process takes longer than 5 minutes, an error occured. You may refresh the page and retry. Please contact <a href="mailto:info@bolliger.tech">info@bolliger.tech</a> if it happens again. Don't forget to include the updating log in the support request.</p>
            <p><a class="btn btn-lg btn-success" id="update_start" role="button">Start updating</a></p>
            <p><a class="btn btn-lg btn-success hidden" id="return_to_application" role="button" href="<?= "//" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']); ?>">Return to application</a></p>
        </div>
    <?php endif; ?>

    <div class="panel panel-default hidden">
        <div class="panel-heading">Updating log</div>
        <div class="panel-body" id="update_log">
            Update started. This should not take more than 5 minutes...<br>
            ---<br>
            Starting file backup...
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> bolliger.tech | <a href="mailto:info@bolliger.tech">info@bolliger.tech</a></p>
    </footer>

</div> <!-- /container -->
<script>
    $(document).ready(function() {
        $('#update_start').click(function(e) {
            e.preventDefault();
            $('#update_log').show();
            $('#update_start').hide();
            takeStep(1);
        });
    });
    
    function takeStep(step) {
        $.get(current_page, {step:step, current_version:current_version}, function(data) {
            var resp = $.parseJSON(data);
            if (resp.status) {
                $('#update_log').append(resp.msg);
                
                // on successfully terminated updateprocess
                if (100 == resp.nextStep) {
                    $('#return_to_application').show();
                    return;
                }
            }
            takeStep(resp.nextStep);
        });
    }
    
</script>
</body>
</html>


<?php
//echo "Starting update process...\n";
//echo "Backing up files... " . $updater->backupFiles() . "\n";
//echo "Backing up database... " . $updater->backupDatabase() . "\n";
//echo "Downloading newest version... " . $updater->downloadNewestVersion() . "\n";
//echo "Extracting files... " . $updater->extractFiles() . "\n";
//echo "Deleting old files... " . $updater->deleteOldFiles() . "\n";
//echo "Moving new files... " . $updater->moveFiles() . "\n";
//echo "Deleting temp files... " . $updater->deleteTempFiles() . "\n";
//echo "Updating database... " . $updater->updateDatabase() . "\n";
// run db migration routine

die();