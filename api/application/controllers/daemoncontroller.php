<?php
  //kill -s SIGTERM xxxx
  //kill -s SIGUSR1 xxxx
  //top | grep php
  //php daemoncontroller.php > /dev/null 2>&1

  //sudo su - www-data
  //php daemoncontroller.php -pid /tmp/daemon.pid
  
  //ps -aux | grep php
  //screen -r, ctrl a+d
  define('ERROR_LOG', "/var/log/rigq.log");
  ini_set('display_errors',0);
  
  global $pids;
  global $pid_file;
  
  //Create log file if it does not exist
  if(!file_exists(ERROR_LOG)) {
    if(!$handle = fopen($ERROR_LOG, 'w')) {
       error_log("Daemon: Cannot create log file ".ERROR_LOG);
       exit;
     }

     if(fwrite($handle, "\n") === FALSE) {
       error_log("Daemon: Cannot write to log file ".ERROR_LOG);
       exit;
     }
     fclose($handle);    
  }
  
  //Get pid file location
  $pids = array();
  if(isset($argv[2])) {
   $pid_file = $argv[2];
  }

  // Daemonize
  $pid = pcntl_fork();
  if($pid) {
    //Only the parent will know the PID. Kids aren't self-aware
    //Parent says goodbye!
    exit;
  }

  //Create a pid file from command line argument
  if(isset($pid_file)) {

   if(!$handle = fopen($pid_file, 'w')) {
     error_log("Daemon: Cannot open pid file $pid_file\n", 3, ERROR_LOG);
     exit;
   }

   if(fwrite($handle, getmypid()) === FALSE) {
     error_log("Daemon: Cannot write to pid file $pid_file\n", 3, ERROR_LOG);
     exit;
   }

   error_log("Daemon: Wrote pid ".getmypid()." to file $pid_file\n", 3, ERROR_LOG);

   fclose($handle);
  }  
  
  //error_log('Daemon: Child process pid '.getmypid()."\n", 3, ERROR_LOG);
   
  //Handle signals so we can exit nicely
  declare(ticks = 2);
  function sig_handler($signo) {
    global $pids;
    
    if($signo == SIGTERM || $signo == SIGHUP || $signo == SIGINT){
            
      //If we are being restarted or killed, quit all children

      //Send the same signal to the children which we recieved
      foreach($pids as $p){
        posix_kill($p,$signo);
      }
      
      foreach($pids as $p) {
        pcntl_waitpid($p,$status);
      }
      
      //Remove pid file
      /*if(file_exists($pid_file)) {
        if(unlink($pid_file)) {
          error_log("Daemon: Removed pid file $pid_file\n", 3, ERROR_LOG);
        } else {
          error_log("Daemon: Error, failed to remove pid file $pid_file\n", 3, ERROR_LOG);
        }
      }*/
      

      exit;
      
    } else if($signo == SIGUSR1) {
      error_log('Daemon: There are currently '.count($pids)." children running\n", 3, ERROR_LOG); 
    }
  }

  //setup signal handlers to actually catch and direct the signals
  pcntl_signal(SIGTERM, "sig_handler");
  pcntl_signal(SIGHUP,  "sig_handler");
  pcntl_signal(SIGINT, "sig_handler");
  pcntl_signal(SIGUSR1, "sig_handler");

  //All the daemon setup work is done now. Now do the actual tasks at hand

  //The program to launch
  $program = "queuecontroller.php";
  $arguments = array("");

  while(true){
   //Should we do a separate DB check here?
   //For now cap the number of concurrent grandchildren
   if(count($pids) < 2) {
     $pid=pcntl_fork();
     if(!$pid){
       pcntl_exec($program,$arguments); //takes an array of arguments
       exit;
     } else {
       //We add pids to a global array, so that when we get a kill signal
       //we tell the kids to flush and exit.
       $pids[] = $pid;
     }
   }

   //Collect any children which have exited on their own. pcntl_waitpid will
   //return the PID that exited or 0 or ERROR
   //WNOHANG means we won't sit here waiting if there's not a child ready
   //for us to reap immediately
   //-1 means any child
   $dead_and_gone = pcntl_waitpid(-1,$status,WNOHANG);
   
   while($dead_and_gone > 0) {
     //Remove the gone pid from the array
     unset($pids[array_search($dead_and_gone,$pids)]); 

     //Look for another one
     $dead_and_gone = pcntl_waitpid(-1,$status,WNOHANG);
   }

   //Sleep for 1 second
   sleep(1);
  }
