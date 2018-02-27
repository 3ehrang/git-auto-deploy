<?php

require 'configuration.php';

// define some const
$title   = 'Git Auto Deployment';
$version = '1.00';

// get configuration
$config = new Configuration();

$date   = new DateTime("now", new DateTimeZone($config->time_zone));
$date   = $date->format('Y-m-d H:i:s');
$datelog = "####### " . $date . " #######\n";

// get all request headers
if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
        $headers = array ();
        foreach ($_SERVER as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

$headers = getallheaders();

// prevent access this file by url
if ($config->prevent_direct && !isset($headers['X-Gitlab-Token'])) {
    file_put_contents ($config->log_path, $datelog . 'direct access denied!' . PHP_EOL, FILE_APPEND);
    die('direct access denied!'); // remove this for allow direct access to this file
}

// check token send by hook
if (isset($headers['X-Gitlab-Token']) && $headers['X-Gitlab-Token'] != $config->token) {
    file_put_contents ($config->log_path, $datelog . 'token mismatch!' . PHP_EOL, FILE_APPEND);
    die();
}

// Check whether client is allowed to trigger an update
if (count($config->allowed_ips)) {
    $allowed = false;
    $headers = apache_request_headers();
    if (@$headers["X-Forwarded-For"]) {
        $ips = explode(",",$headers["X-Forwarded-For"]);
        $ip  = $ips[0];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    foreach ($config->allowed_ips as $allow) {
        if (stripos($ip, $allow) !== false) {
            $allowed = true;
            break;
        }
    }
    if (!$allowed) {
        file_put_contents ($config->log_path, $datelog . 'IP access is denied!' . PHP_EOL, FILE_APPEND);
     	die('IP access is denied!');
    }
}

// change deploy path
chdir($config->deploy_path);

/*
 *  ignore all local changes and files added and pull from git
 */
$commands = array(
    'echo $PWD',
    'whoami'
);
if (isset($config->branch)) {
    $commands[] = $config->git_path . ' checkout ' . $config->branch;
}
array_push(
    $commands,
    $config->git_path . ' status',
    $config->git_path . ' clean -fd', // remove untracked files and directories
    $config->git_path . ' fetch --all', 
    $config->git_path . ' reset --hard HEAD', // ignore local changes
    $config->git_path . ' pull',
    $config->git_path . ' status',
    $config->git_path . ' submodule sync',
    $config->git_path . ' submodule update',
    $config->git_path . ' submodule status'
);

$log = "####### ". $date . " #######\n";
echo "\e[0;32;40m" . "##  " . $title . " version " . $version . " ##" . "\e[0m\n" . "\n";
$output = '';
foreach($commands AS $command){
    // Run it
    $tmp    = shell_exec("$command 2>&1");
    // Output
    $output .= "\e[0;36;40m" . $command . "\e[0m\n";
    $output .= trim($tmp) . "\r\n\r\n";
    $log    .= "\$ $command\n".trim($tmp)."\n";
}
$log .= "\n";
file_put_contents ($config->log_path, $log,FILE_APPEND);
//echo "\e[0;31;40m" . $output . "\e[0m\n";
echo $output;

// send new demployment email
if (!empty($config->mailTo)) {
	mail($config->mailTo, "New Deployment", $output);
}
