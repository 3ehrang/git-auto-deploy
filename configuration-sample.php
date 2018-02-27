<?php
class Configuration {
    
    // git path with "which git" command
	public $git_path = '';
	
	// real log path on server like var/www/name/logs/deploy-master.txt
	public $log_path = '';
	
	// real deploy path on server
	public $deploy_path = '';
	
	public $time_zone = 'Asia/Tehran';
	public $branch = 'master';
	
	// prevent script run by command line
	public $prevent_direct = true;
	
	// token for match incoming request with it must define in webhook also
	public $token = '';
	
	public $allowed_ips = [];
	
	public $mailTo = '';
}