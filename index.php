<?php

$json = $_SERVER["QUERY_STRING"] ?? '';

$files = scandir("scripts/");

unset($files[0]);
unset($files[1]);
unset($files[2]);
$output = [];
$outputJSON = [];
$data = [];
$passes = 0;
$fails = 0;
foreach ($files as $file) {

    $extension = explode('.', $file);

    switch ($extension[1]) {
        case 'php':
            $startScript = "php";
            break;
        case 'js':
            $startScript = "node";
            break;
        case 'py':
            $startScript = "python";
            break;
        case 'dart':
            $startScript = "dart";
            break;
        case 'java':
            $startScript = "java";

            exec("javac scripts/" . $file);
            break;

        default:
            $startScript = "php";
            break;
    }

    $f = exec($startScript . " scripts/" . $file);

    $newString = str_ireplace(getEmailFromFileContent($f),' ', str_ireplace('and email',' ', $f));

    @$data[$extension[0]]->content = $newString;
    $data[$extension[0]]->status = testFileContent($f);
    $data[$extension[0]]->name = str_replace('-',' ',$extension[0]);
    $data[$extension[0]]->email = getEmailFromFileContent($f);
    $data[$extension[0]]->file = $file;
    $output[] = [$newString, testFileContent($f), $extension[0], $data[$extension[0]]->email];

}
$outputJSON = $data;

function testFileContent($string)
{
    if (preg_match('/^Hello\sWorld[,|.|!]*\sthis\sis\s([a-zA-Z|-]{2,}\s){1,6}with\sHNGi7\sID\s(HNG-\d{3,})\sand\semail\s{1,3}(([\w+\.\-]+)@([\w+\.\-]+)\.([a-zA-Z]{2,5}))\s{1,3}using\s[a-zA-Z|#]{2,}\sfor\sstage\s2\stask.?$/i', trim($string))) {
        return 'Pass';
    }

    return 'Fail';
}

function getEmailFromFileContent($string)
{
    preg_match('/\s?(([\w+\.\-]+)@([\w+\.\-]+)\.([a-zA-Z]{2,5}))/i', trim($string) , $matches, PREG_OFFSET_CAPTURE);

    return @$matches[0][0];
}

foreach ($output as $val) {
    if ($val[1] == 'Pass') {
        $passes++;
    } elseif ($val[1] == 'Fail') {
        $fails++;
    }
}

if (isset($json) && $json == 'json') {
    echo json_encode($outputJSON);
} else {
    ?>
    <html>

    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    </head>

    <body>
    <div class="container-fluid">
        <nav class="navbar navbar-dark bg-dark fixed-top">
                <span class="navbar-text">
                    HNGi7 Team Sentry
                </span>
            <div class="float-right text-white">
                <small>
                    Leader: <span class="btn btn-sm btn btn-outline-primary">@E.U</span>
                </small> &nbsp;
                <small>
                    FrontEnd: <span class="btn btn-sm btn btn-outline-success">@dona</span>
                </small> &nbsp;
                <small>
                    DevOps: <span class="btn btn-sm btn btn-outline-info">@Fidele</span>
                </small> &nbsp;
            </div>
        </nav>
    </div>
    <div class="container">
        <div class="row" style="padding: 6em 0" class="text-center">
            <div class="col-md-4">
                <button type="button" class="btn">
                    Submitted <span class="badge badge-primary"><?php echo ($passes + $fails)  ?></span>
                </button>
            </div>
            <div class="col-md-4">
                <button type="button" class="btn">
                    Passes <span class="badge badge-success"><?php echo ($passes)  ?></span>
                </button>
            </div>
            <div class="col-md-4">
                <button type="button" class="btn">
                    Fails <span class="badge badge-danger"><?php echo ($fails)  ?></span>
                </button>
            </div>
        </div>
        <table class="table table-hover center table-striped">
            <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Message</th>
                <th scope="col">Email</th>
                <th scope="col">Status</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $row = 0;
            foreach ($output as $out) {

                $status = $out[1] == 'Pass' ? 1 : 0;
                $email = 'No Email';
                if(isset($out[3])){
                    $email = $out[3];
                }
                if ($status) {
                    echo <<<EOL
                                <tr class="table-success">
                                <th scope="row">$row</th>
                                <td><b>$out[2]</b></td>
                                <td>$out[0]</td>
                                <td>
                                    $email
                                </td>
                                <td>$out[1] ✅</td>
                                </tr>
                             EOL;
                }
                else {
                    echo <<<EOL
                                <tr class="table-danger">
                                <th scope="row">$row</th>
                                <td><b>$out[2]</b></td>
                                <td>$out[0]</td>
                                <td>
                                    $email
                                </td>
                                <td>$out[1] ❌</td>
                                </tr>
                            EOL;
                }
                $row++;

                flush();
                ob_flush();

                sleep(1); //used this to test the buffering

            }
            ?>

            </tbody>
        </table>


    </div>

    </body>

    </html>
    <?php
}

?>
