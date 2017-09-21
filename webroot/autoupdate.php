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
    switch ($_GET['step']) {
        case 1:
            $data = tryTask(
                    $updater,
                    'backupFiles',
                    'Starting database backup...',
                    2
            );
            break;
        
        case 2:
            $data = tryTask(
                    $updater,
                    'backupDatabase',
                    'Downloading update...',
                    3
            );
            break;
        
        case 3:
            $data = tryTask(
                    $updater,
                    'downloadNewestVersion',
                    'Extracting files...',
                    4
            );
            break;
        
        case 4:
            $data = tryTask(
                    $updater,
                    'extractFiles',
                    'Deleting old files...',
                    5
            );
            break;
        
        case 5:
            $data = tryTask(
                    $updater,
                    'deleteOldFiles',
                    'Moving new files...',
                    6
            );
            break;
        
        case 6:
            $errorMsg       = 'Error<br>Please wait, we are trying to restore your old installation. More information on the very bottom.';
            $errorMsgSuffix = '<br>Start restoring old installation from backup... ';
            $data           = tryTask(
                    $updater,
                    'moveFiles',
                    'Deleting temporary files...',
                    7,
                    $errorMsg,
                    201,
                    $errorMsgSuffix
            );
            break;
        
        case 7:
            $errorMsg       = 'Error<br>Please wait, we are trying to restore your old installation. More information on the very bottom.';
            $errorMsgSuffix = '<br>Start restoring old installation from backup... ';
            $data           = tryTask(
                $updater,
                'deleteTempFiles',
                'Updating database...',
                8,
                $errorMsg,
                201,
                $errorMsgSuffix
            );
            break;
        
        case 8:
            $errorMsg       = 'Error<br>Please wait, we are trying to restore your old installation. More information on the very bottom.';
            $errorMsgSuffix = '<br>Start restoring old installation from backup... ';
            $data           = tryTask(
                $updater,
                'updateDatabase',
                'Update successfully terminated!',
                100,
                $errorMsg,
                201,
                $errorMsgSuffix
            );
            break;
            
        case 201:
            // ToDo restore files from backup
            break;
    }
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
            var current_page = '//<?= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] ?>';
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
        
        <?php if ($noupdate): ?>
            <div class="jumbotron">
                <h1>Perfect!</h1>
                <p class="lead">Your installation is up to date. You may now return to the application.</p>
                <p><a class="btn btn-lg btn-success"
                      href="<?= "//" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']); ?>" role="button">Return
                        to application</a></p>
            </div>
        <?php else: ?>
            <div class="jumbotron">
                <h1>Please update</h1>
                <p class="lead">An update is ready to be installed. If the updating process takes longer than 5 minutes,
                    an error occured. You may refresh the page and retry. Please contact <a
                            href="mailto:info@bolliger.tech">info@bolliger.tech</a> if it happens again. Don't forget to
                    include the updating log in the support request.</p>
                <p><a class="btn btn-lg btn-success" id="update_start" role="button">Start updating</a></p>
                <p><a class="btn btn-lg btn-success hidden" id="return_to_application" role="button"
                      href="<?= "//" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']); ?>">Return to
                        application</a></p>
                <p><a class="btn btn-lg btn-success hidden" id="reload" role="button"
                      href="<?= "//" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']; ?>">Reload & Retry</a></p>
            </div>
        <?php endif; ?>

        <div class="panel panel-default hidden" id="update_log">
            <div class="panel-heading">Updating log</div>
            <div class="panel-body">
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
        $(document).ready(function () {
            $('#update_start').click(function (e) {
                e.preventDefault();
                $('#update_log').removeClass('hidden');
                $('#update_start').hide();
                takeStep(1);
            });
        });

        function takeStep(step) {
            $.get(current_page, {step: step, current_version: current_version}, function (data) {
                var resp = $.parseJSON(data);
                $('#update_log .panel-body').append(resp.msg);
                if (resp.status === true) {
                    // on successfully terminated updateprocess
                    if (100 == resp.nextStep) {
                        $('#return_to_application').removeClass('hidden');
                        return;
                    }
                } else {
                    $('#reload').removeClass('hidden');
                }
                takeStep(resp.nextStep);
            });
        }

    </script>
    </body>
    </html>

<?php

function tryTask($updater, $task, $successMsg, $nextSuccess, $errorMsg = null, $nextError = -1, $errorMsgSuffix = '')
{
    $success  = 'successful<br>';
    $status   = false;
    $nextStep = $nextError;
    $errorMsg = empty($errorMsg) ? "Error<br>---<br>Update process stopped. Your application wasn't updated but still works." : $errorMsg;
    try {
        $status   = $updater->$task();
        $msg      = $status ? $success . $successMsg . ' ' : $errorMsg;
        $nextStep = $status ? $nextSuccess : $nextError;
    } catch (\Exception $e) {
        $msg = $errorMsg . '<br>--- BEGIN DEBUG INFORMATION ---<br>' . $e->getMessage() . '<br><pre>' . $e->getTraceAsString() . '</pre><br>--- END DEBUG INFORMATION ---' . $errorMsgSuffix;
    } finally {
        return ['status' => $status, 'msg' => $msg, 'nextStep' => $nextStep];
    }
}

?>