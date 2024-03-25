<?php
// / -----------------------------------------------------------------------------------
// / COPYRIGHT INFORMATION ...
// / ScanCore, Copyright on 3/24/2024 by Justin Grimes, www.github.com/zelon88 
// / 
// / LICENSE INFORMATION ...
// / This project is protected by the GNU GPLv3 Open-Source license.
// / https://www.gnu.org/licenses/gpl-3.0.html
// / 
// / APPLICATION INFORMATION ...
// / This application is designed to scan files & folders for viruses.
// / 
// / FILE INFORMATION ...
// / v1.1.
// / This file contains the core logic of the ScanCore application.
// /
// / HARDWARE REQUIREMENTS ...
// / This application requires at least a Raspberry Pi Model B+ or greater.
// / This application will run on just about any x86 or x64 computer.
// / 
// / DEPENDENCY REQUIREMENTS ... 
// / This application should run on Linux or Windows systems with PHP 8.0 (or later).
// / 
// / VALID SWITCHES / ARGUMENTS / USAGE ...
// / Quick Start Example:
// /  C:\Path-To-PHP-Binary.exe C:\Path-To-ScanCore.php C:\Path-To-Scan\ -m [integer] -c [integer] -v -d
// / 
// / Start by opening a command-prompt.
// / Type the absolute path to a portable PHP 7.0+ binary. Don't press enter just yet.
// / Now type the absolute path to this PHP file as the only argument for the PHP binary.
// / Everything after the path to this script will be passed to this file as an argument.
// / The first Argument Must be a valid absolute path to the file or folder being scanned.
// / Optional arguments can be specified after the scan path. Separate them with spaces.
// / 
// / Optional Arguments Include:
// /   Force recursion:                        -recursion
// /                                           -r
// / 
// /   Force no recursion:                     -norecursion
// /                                           -nr
// / 
// /   Specify memory limit (in bytes):        -memorylimit ####
// /                                           -m ####
// / 
// /   Specify chunk size (in bytes);          -chunksize ####
// /                                           -c ####
// / 
// /   Enable "debug" mode (more logging):     -debug
// /                                           -d
// / 
// /   Enable "verbose" mode (more console):   -verbose
// /                                           -v
// / 
// /   Force a specific log file:              -logfile /path/to/file
// /                                           -lf path/to/file
// / 
// /   Force a specific report file:           -reportfile /path/to/file
// /                                           -rf path/to/file
// / 
// /   Force maximum log size (in bytes):      -maxlogsize ###
// /                                           -ml ###
// / 
// /   Perform definition update:              -updatedefinitions
// /                                           -ud
// / 
// /   Perform application update:             -updateapplication
// /                                           -ua
// / 
// / <3 Open-Source
// / -----------------------------------------------------------------------------------



// / -----------------------------------------------------------------------------------
// / The following code sets global variables for the session.
function verifySCInstallation() {
  // / Set variables.
  global $Versions, $Date, $Time, $SEP, $ReportFile, $Logfile, $ConfigFile, $RequiredDirs, $Version, $Versions, $argv, $EOL, $MaxLogSize, $Debug, $Verbose, $DefaultMemoryLimit, $DefaultChunkSize, $DefaultMaxLogSize, $ReportFileName, $ConfigVersion, $DefsFile, $DefsFileName, $FileCount, $ApplicationUpdates, $ApplicationUpdateURL, $DefinitionUpdates, $DefinitionUpdateURL, $DefinitionsUpdateSubscriptions, $ApplicationRepositoryName, $DefinitionRepositoryName, $InstallDir, $AppInstallDir, $DefInstallDir, $DefGitDir, $AppGitDir;
  // / Application related variables.
  $InstallationVerified = $ConfigLoaded = $ReportFile = $Logfile = $RequiredDirs = FALSE;
  $EOL = PHP_EOL;
  $RequiredDirs = array();
  $SEP = DIRECTORY_SEPARATOR;
  $ConfigFile = 'ScanCore_Config.php';
  $Version = 'v1.1';
  $Versions = $ConfigVersion;
  $rp = realpath(dirname(__FILE__));
  $FileCount = 0;
  // / Time related variables.
  $Date = date("m_d_y");
  $Time = date("F j, Y, g:i a");
  // / Initialize an empty array if no arguments are set.
  if (!isset($argv)) $argv = array();
  // / Load the configuration file located at $ConfigFile.
  if (file_exists($rp.$SEP.$ConfigFile)) $ConfigLoaded = require_once ($rp.$SEP.$ConfigFile);
  // / Check to make sure the configuration file was loaded & the configuration version is compatible with the core.
  if (isset($ScanLoc) && isset($DefsFile) && isset($ConfigVersion) && $ConfigVersion === $Version && $ConfigLoaded) {
    // / Configuration related variables.
    $InstallationVerified = TRUE;
    $ReportFile = $ReportDir.$SEP.$ReportFileName;
    $Logfile = $ReportDir.$LogFileName;
    $RequiredDirs = array($ReportDir);
    $MaxLogSize = $DefaultMaxLogSize; 
    $Debug = $Debug;
    $Verbose = $Verbose;
    $MemoryLimit = $DefaultMemoryLimit;
    $ChunkSize = $DefaultChunkSize; 
    $DefInstallDir = $InstallDir.DIRECTORY_SEPARATOR.$DefinitionRepositoryName;
    $AppInstallDir = $InstallDir.DIRECTORY_SEPARATOR.$ApplicationRepositoryName;
    $DefGitDir = $DefInstallDir.DIRECTORY_SEPARATOR.'.git';
    $AppGitDir = $AppInstallDir.DIRECTORY_SEPARATOR.'.git'; }
  // / Manually clean up sensitive memory. Helps to keep track of variable assignments.
  $rp = NULL;
  unset($rp); 
  return array($InstallationVerified, $ConfigLoaded); }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / A function to create required directories when they do not already exist.
function createDirs($RequiredDirs) { 
  // / Set variables.
  global $Time;
  $RequiredDirsExist = TRUE;
  // / Iterate through each required directory.
  foreach ($RequiredDirs as $reqdDir) {
    // / Detect if the directory already exists & create it if required.
    if (!file_exists($reqdDir)) mkdir($reqdDir);
    // / If an index.html file is present in the installation directory, copy it to the newly created dictory.
    if (!file_exists('index.html')) copy('index.html', $reqdDir.DIRECTORY_SEPARATOR.'index.html');
    if (!file_exists($reqdDir)) $RequiredDirsExist = FALSE; }
  // / Manually clean up sensitive memory. Helps to keep track of variable assignments.
  $reqdDir = NULL;
  unset($reqdDir); 
  return array($RequiredDirsExist); }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / A function to add an entry to the logs.
function addLogEntry($entry, $error, $errorNumber) {
  // / Set variables.
  global $ReportFile, $Time, $EOL;
  if (!is_numeric($errorNumber)) $errorNumber = 0;
  if ($error === TRUE) $preText = 'ERROR!!! ScanCore-'.$errorNumber.' on '.$Time.', ';
  else $preText = $Time.', ';
  $LogCreated = file_put_contents($ReportFile, $preText.$entry.$EOL, FILE_APPEND);
  // / Manually clean up sensitive memory. Helps to keep track of variable assignments.
  $preText = $error = $entry = $errorNumber = NULL;
  unset($preText, $error, $entry, $errorNumber); 
  return array($LogCreated); }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / A function to handle important messages to the console & log file.
function processOutput($txt, $error, $errorNumber, $requiredLog, $requiredConsole, $fatal) {
  global $EOL, $Debug, $Verbose;
  $OutputProcessed = FALSE;
  // / Verify that all inputs are of the correct type.
  if (!is_string($txt)) $txt = '';
  if (!is_bool($error)) $error = FALSE;
  if (!is_int($errorNumber)) $errorNumber = 0;
  if (!is_bool($requiredLog)) $requiredLog = FALSE;
  if (!is_bool($requiredConsole)) $requiredConsole = FALSE;
  // / Log the provided text if $Debug variable (-d switch) is set.
  if ($Debug or $requiredLog) list ($OutputProcessed) = addLogEntry($txt, $error, $errorNumber);
  // / Output the summary text to the terminal if the $Verbose (-v switch) variable is set.
  if ($Verbose or $requiredConsole) echo $txt.$EOL;
  // / Manually clean up sensitive memory. Helps to keep track of variable assignments.
  $txt = $error = $errorNumber = $requiredLog = $requiredConsole = NULL;
  unset($txt, $error, $errorNumber, $requiredLog, $requiredConsole); 
  // / Stop execution as needed.
  if ($fatal) die();
  return array($OutputProcessed); }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / A function to parse supplied command-line arguments.
function parseArgs($argv) {
  // / Set variables.
  // / Most of these should already be set to the values contained in the configuration file.
  global $ArgsParsed, $ReportFile, $Logfile, $MaxLogSize, $Debug, $Verbose, $EOL, $ChunkSize, $MemoryLimit, $DefaultMemoryLimit, $DefaultChunkSize, $PerformDefUpdate, $PerformAppUpdate;
  $Recursion = FALSE;
  $ArgsParsed = $PathToScan = $PerformDefUpdate = $PerformAppUpdate = FALSE;
  foreach ($argv as $key => $arg) {
    $arg = htmlentities(str_replace(str_split('~#[](){};:$!#^&%@>*<"\''), '', $arg));
    if ($arg == '-memorylimit' or $arg == '-m') $MemoryLimit = $argv[$key + 1];
    if ($arg == '-chunksize' or $arg == '-c') $ChunkSize = $argv[$key + 1];
    if ($arg == '-debug' or $arg == '-d') $Debug = TRUE;
    if ($arg == '-verbose' or $arg == '-v') $Verbose = TRUE;
    if ($arg == '-recursion' or $arg == '-r') $Recursion = TRUE;
    if ($arg == '-norecursion' or $arg == '-nr') $Recursion = FALSE;
    if ($arg == '-updatedefinitions' or $arg == '-ud') $PerformDefUpdate = TRUE;
    // The update application feature is not ready yet, so don't uncomment this.
    //if ($arg == '-updateapplication' or $arg == '-ua') $PerformAppUpdate = TRUE;
    if ($arg == '-reportfile' or $arg == '-rf') $ReportFile = $argv[$key + 1];
    if ($arg == '-logfile' or $arg == '-lf') $Logfile = $argv[$key + 1];
    if ($arg == '-maxlogsize' or $arg == '-ml') $MaxLogSize = $argv[$key + 1]; }
  // / Detect if an update is being requested.
  if ($PerformDefUpdate or $PerformAppUpdate) {
    $ArgsParsed = TRUE;
    processOutput('Starting ScanCore updater!', FALSE, 0, TRUE, FALSE, FALSE); }
  else {
    // / Detect if no arguments were supplied.
    if (!isset($argv[1])) processOutput('There were no arguments set!', TRUE, 100, TRUE, TRUE, FALSE);
    else {
      // / Detect if a valid path to scan was supplied.
      if (!file_exists($argv[1])) processOutput('The specified file was not found! The first argument must be a valid file or directory path!', TRUE, 300, TRUE, TRUE, FALSE);
      else {
        $PathToScan = $argv[1];
        // / Detect if the MemoryLimit and ChunkSize variables are valid.
        if (!is_numeric($MemoryLimit) or !is_numeric($ChunkSize)) {
          processOutput('Either the chunkSize argument or the memoryLimit argument is invalid. Attempting to use default values.', TRUE, 200, TRUE, TRUE, FALSE);
          $MemoryLimit = $DefaultMemoryLimit;
          $ChunkSize = $DefaultChunkSize; } 
        $ArgsParsed = TRUE;
        processOutput('Starting ScanCore!', FALSE, 0, TRUE, FALSE, FALSE); } } }
  // / Manually clean up sensitive memory. Helps to keep track of variable assignments.
  $key = $arg = NULL;
  unset($key, $arg);
  return array($ArgsParsed, $PathToScan, $MemoryLimit, $ChunkSize, $Debug, $Verbose, $Recursion, $ReportFile, $Logfile, $MaxLogSize, $PerformDefUpdate, $PerformAppUpdate); }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / A function to remove files & folders.
function clean($Location) {
  // / Set variables.
  $LocationCleaned = FALSE;
  $f = FALSE;
  $i = array();
  // / Detect if the location is a folder.
  if (is_dir($Location)) {
    // / Scan the folder for contents.
    $i = array_diff(scandir($Location), array('..', '.'));
    // / Iterate through the contents of the folder.
    foreach ($i as $f) {
      // / If this object is a folder, run this function on it.
      if (is_dir($Location.DIRECTORY_SEPARATOR.$f)) clean($Location.DIRECTORY_SEPARATOR.$f);
      // / If this object is a file, delete it.
      else unlink($Location.DIRECTORY_SEPARATOR.$f); }
    // / Try to delete the folder now that we've deleted the contents.
    if (is_dir($Location)) rmdir($Location); }
  // / If the location is a file, delete it.
  if (file_exists($Location) && !is_dir($Location)) unlink($Location);
  // / Check if the location was deleted.
  if (!is_dir($Location) && !file_exists($Location)) $LocationCleaned = TRUE; 
  // / Manually clean up sensitive memory. Helps to keep track of variable assignments.
  $f = $i = NULL;
  unset($f, $i); 
  return array($LocationCleaned); }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / A function to install definition updates.
function updateDefinitions() {
  // / Set variables.
  global $DefinitionUpdates, $DefinitionUpdateURL, $DefinitionsUpdateSubscriptions, $InstallDir, $DefsFile, $DefinitionRepositoryName, $DefInstallDir, $DefGitDir;
  $UpdateDefininitionsComplete = $UpdateDefinitionsErrors = $defSubs = $writeCheck = $defInstallDirCleaned = $defGitDirCleaned = $cleanCheck = FALSE;
  $subData = $returnData = '';
  $subCount = 0;
  $subCount1 = count($DefinitionsUpdateSubscriptions);
  // / Only perform definition updates if they are enabled in $ConfigFile.
  if ($DefinitionUpdates) {
    // / If a definition install directory already exists, remove all the files inside & then remove the folder.
    list($defInstallDirCleaned) = clean($DefInstallDir);
    list($defGitDirCleaned) = clean($DefGitDir);
    // / Continue only if the definition install directory was able to be cleaned.
    if ($defGitDirCleaned && $defInstallDirCleaned) {
      // / Download the latest definitions from the $DefinitionUpdateURL.
      $returnData = shell_exec('git clone '.$DefinitionUpdateURL);
      // / Only continue with the update if Git was able to create a folder.
      if (is_dir($DefInstallDir)) {
        // / Copy an index.html file to the newly created folder as document root protection, incase this application is in a hosted location.
        if (file_exists('index.html')) copy('index.html', $DefInstallDir.DIRECTORY_SEPARATOR.'index.html');
        // / Remove the .git directory, just in case this is installed in a hosted location we don't want to maintin that many directories.
        list($cleanCheck) = clean($DefGitDir);
        if ($cleanCheck) {
          // / Iterate through the list of susbscribed definitions.
          foreach ($DefinitionsUpdateSubscriptions as $defSubs) {
            $defSubFile = $InstallDir.DIRECTORY_SEPARATOR.'ScanCore_'.$defSubs.'.def';
            // / Build the new definitions in memory from the subscriptions that apply to this installation.
            if (file_exists($defSubFile)) {
              $subCount++;
              $subData = trim($subData.PHP_EOL.trim(file_get_contents($defSubFile))); } }
          // / Write the new definition data to a new definition file.
          $writeCheck = file_put_contents($DefsFile, $subData); } } } 
    // / If a definition install directory already exists, remove all the files inside & then remove the folder.
    list($defInstallDirCleaned) = clean($DefInstallDir);
    list($defGitDirCleaned) = clean($DefGitDir); }
  // / Check if the subscription file was written successfully.
  if ($writeCheck) $UpdateDefininitionsComplete = TRUE;
  if ($subCount !== $subCount1) $UpdateDefininitionsErrors = TRUE;
  // / Manually clean up sensitive memory. Helps to keep track of variable assignments.
  $defSubs = $defSubFile = $subData = $writeCheck = $subCount = $subCount1 = $returnData = $f = $i = $cleanCheck = NULL;
  unset($defSubs, $defSubs, $subData, $writeCheck, $subCount, $subCount1, $returnData, $f, $i, $cleanCheck);
  return array($UpdateDefininitionsComplete, $UpdateDefininitionsErrors); }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / A function to install application updates.
function updateApplication() {
  global $ApplicationUpdates, $ApplicationUpdateURL, $InstallDir, $ApplicationRepositoryName, $AppInstallDir, $AppGitDir, $ConfigFile;
  $UpdateApplicationComplete = FALSE;
  $returnData = '';
  $configInc = 0;
  $backupConfigFile = $ConfigFile.'_'.$configInc.'_'.'.bak';
  // / Only perform application updates if they are enabled in $ConfigFile.
  // / If application updates are enabled, download the latest application update from the $ApplicationUpdateURL.
  if ($ApplicationUpdates) {
    // / Check if an existing backup configuration file exists, & set a path to a new one with an unused name.
    while (file_exists($backupConfigFile)) {
      $configInc++;
      $backupConfigFile = $ConfigFile.'_'.$configInc.'_'.'.bak'; }
    // / Copy the configuration file to a backup.
    copy($ConfigFile, $backupConfigFile);
    //$returnData = shell_exec('git clone '.$ApplicationUpdateURL.' '.$InstallDir);
  }
  // / Check if the git operation returned some output.
  if ($returnData !== '') $UpdateApplicationComplete = TRUE;
  // / Manually clean up sensitive memory. Helps to keep track of variable assignments.
  $returnData = $configInc = $backupConfigFile = NULL;
  unset($sreturnData, $configInc, $backupConfigFile);
  return array($UpdateApplicationComplete); }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// Hunts files/folders recursively for scannable items.
function file_scan($folder, $Defs, $DefsFile, $DefData, $Debug, $Verbose, $MemoryLimit, $ChunkSize, $Recursion) {
  // / Set variables.
  global $SEP, $EOL, $FileCount;
  $ScanComplete = FALSE;
  $DirCount = 1;
  $Infected = 0;
  if (is_dir($folder)) {
    $files = scandir($folder);
    foreach ($files as $file) {
      if ($file === '' or $file === '.' or $file === '..') continue;
      $entry = str_replace($SEP.$SEP, $SEP, $folder.$SEP.$file);
      if (!is_dir($entry)) list($checkComplete, $Infected) = virus_check($entry, $Defs, $DefsFile, $DefData, $Debug, $Verbose, $MemoryLimit, $ChunkSize);
      else if (is_dir($entry) && $Recursion) {
        processOutput('Scanning folder "'.$entry.'" ... ', FALSE, 0, TRUE, TRUE, FALSE);
        $DirCount++; 
        list ($scanComplete, $DirCount, $FileCount, $Infected) = file_scan($entry, $Defs, $DefsFile, $DefData, $Debug, $Verbose, $MemoryLimit, $ChunkSize, $Recursion); 
        $entry = ''; } } }
  if (!is_dir($folder) && $folder !== '.' && $folder !== '..') {
    $FileCount++;
    list($checkComplete, $Infected) = virus_check($folder, $Defs, $DefsFile, $DefData, $Debug, $Verbose, $MemoryLimit, $ChunkSize); }
  $ScanComplete = TRUE;
  // / Manually clean up sensitive memory. Helps to keep track of variable assignments.
  $files = $file = $entry = $folder = NULL;
  unset($files, $file, $entry, $folder); 
  return array($ScanComplete, $DirCount, $FileCount, $Infected); }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// Reads tab-delimited definitions file. Also hashes the file to avoid self-detection.
function load_defs($DefsFile) {
  // / Set variables.
  global $EOL, $Debug, $Verbose;
  $DefsLoaded = $Defs = $DefData = FALSE;
  if (!file_exists($DefsFile)) processOutput('Could not load the virus definition file located at "'.$DefsFile.'"! File either does not exist or cannot be read!', TRUE, 400, TRUE, TRUE, FALSE);
  else { 
    $Defs = file($DefsFile);
    $DefData = hash_file('sha256', $DefsFile);
    $counter = 0;
    $counttop = sizeof($Defs);
    while ($counter < $counttop) {
      $Defs[$counter] = explode('  ', $Defs[$counter]);
      $counter++; }
    processOutput('Loaded '.sizeof($Defs).' virus definitions.', FALSE, 0, FALSE, FALSE, FALSE);
    $DefsLoaded = TRUE; }
  // / Manually clean up sensitive memory. Helps to keep track of variable assignments.
  $counter = $counttop = NULL;
  unset($counter, $counttop); 
  return array($DefsLoaded, $Defs, $DefData); }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// Hashes and checks files/folders for viruses against static virus defs.
function virus_check($file, $Defs, $DefsFile, $DefData, $Debug, $Verbose, $MemoryLimit, $ChunkSize) {
  // / Set variables.
  global $Infected, $DefsFileName, $EOL;
  $CheckComplete = FALSE;
  if ($file !== $DefsFileName) {
    if (file_exists($file)) {
      processOutput('Scanning file ... ', FALSE, 0, TRUE, TRUE, FALSE);
      $filesize = filesize($file);
      $data1 = hash_file('md5', $file);
      $data2 = hash_file('sha256', $file);
      $data3 = hash_file('sha1', $file);
      // / Scan files larger than the memory limit by breaking them into chunks.
      if ($filesize >= $MemoryLimit && file_exists($file)) { 
        processOutput('Chunking file ... ', FALSE, 0, FALSE, FALSE, FALSE);
        $handle = @fopen($file, "r");
        if ($handle) {
          while (($buffer = fgets($handle, $ChunkSize)) !== FALSE) {
            $data = $buffer;
            processOutput('Scanning chunk ... ', FALSE, 0, FALSE, FALSE, FALSE);
            foreach ($Defs as $virus) {
              $virus = explode("\t", $virus[0]);
              if (isset($virus[1]) && !is_null($virus[1]) && $virus[1] !== '' && $virus[1] !== ' ') {
                if (strpos(strtolower($data), strtolower($virus[1])) !== FALSE or strpos(strtolower($file), strtolower($virus[1])) !== FALSE) { 
                  // File matches virus defs.
                  processOutput('Infected: '.$file.' ('.$virus[0].', Data Match: '.$virus[1].')', FALSE, 0, TRUE, TRUE, FALSE);
                  $Infected++; } } } }
          if (!feof($handle)) {
            processOutput('Unable to open "'.$file.'"!', TRUE, 500, TRUE, TRUE, FALSE);
            fclose($handle); } 
          if (isset($virus[2]) && !is_null($virus[2]) && $virus[2] !== '' && $virus[2] !== ' ') {
            if (strpos(strtolower($data1), strtolower($virus[2])) !== FALSE) {
              // File matches virus defs.
              processOutput('Infected: '.$file.' ('.$virus[0].', MD5 Hash Match: '.$virus[2].')', FALSE, 0, TRUE, TRUE, FALSE);
              $Infected++; } }
            if (isset($virus[3]) && !is_null($virus[3]) && $virus[3] !== '' && $virus[3] !== ' ') {
              if (strpos(strtolower($data2), strtolower($virus[3])) !== FALSE) {
                // File matches virus defs.
                processOutput('Infected: '.$file.' ('.$virus[0].', SHA256 Hash Match: '.$virus[3].')', FALSE, 0, TRUE, TRUE, FALSE);
                $Infected++; } } 
            if (isset($virus[4]) && !is_null($virus[4]) && $virus[4] !== '' && $virus[4] !== ' ') {
              if (strpos(strtolower($data3), strtolower($virus[4])) !== FALSE) {
                // File matches virus defs.
                processOutput('Infected: '.$file.' ('.$virus[0].', SHA1 Hash Match: '.$virus[4].')', FALSE, 0, TRUE, TRUE, FALSE);
                $Infected++; } } } }
      // / Scan files smaller than the memory limit by fitting the entire file into memory.
      if ($filesize < $MemoryLimit && file_exists($file)) {
        $data = file_get_contents($file); }
      if ($DefData !== $data2) {
        foreach ($Defs as $virus) {
          $virus = explode("\t", $virus[0]);
          if (isset($virus[1]) && !is_null($virus[1]) && $virus[1] !== '' && $virus[1] !== ' ') {
            if (strpos(strtolower($data), strtolower($virus[1])) !== FALSE or strpos(strtolower($file), strtolower($virus[1])) !== FALSE) {
              // File matches virus defs.
              processOutput('Infected: '.$file.' ('.$virus[0].', Data Match: '.$virus[1].')', FALSE, 0, TRUE, TRUE, FALSE);
              $Infected++; } }
          if (isset($virus[2]) && !is_null($virus[2]) && $virus[2] !== '' && $virus[2] !== ' ') {
            if (strpos(strtolower($data1), strtolower($virus[2])) !== FALSE) {
              // File matches virus defs.
              processOutput('Infected: '.$file.' ('.$virus[0].', MD5 Hash Match: '.$virus[2].')', FALSE, 0, TRUE, TRUE, FALSE);
              $Infected++; } }
            if (isset($virus[3]) && !is_null($virus[3]) && $virus[3] !== '' && $virus[3] !== ' ') {
              if (strpos(strtolower($data2), strtolower($virus[3])) !== FALSE) {
                // File matches virus defs.
                processOutput('Infected: '.$file.' ('.$virus[0].', SHA256 Hash Match: '.$virus[3].')', FALSE, 0, TRUE, TRUE, FALSE);
                $Infected++; } } 
            if (isset($virus[4]) && !is_null($virus[4]) && $virus[4] !== '' && $virus[4] !== ' ') {
              if (strpos(strtolower($data3), strtolower($virus[4])) !== FALSE) {
                // File matches virus defs.
                processOutput('Infected: '.$file.' ('.$virus[0].', SHA1 Hash Match: '.$virus[4].')', FALSE, 0, TRUE, TRUE, FALSE);
                $Infected++; } } } } } }
  $CheckComplete = TRUE;
  // / Manually clean up sensitive memory. Helps to keep track of variable assignments.
  $file = $filesize = $data = $buffer = $handle = $virus = $data1 = $data2 = $data3 = NULL;
  unset($file, $filesize, $data, $buffer, $handle, $virus, $data1, $data2, $data3);
  return array($CheckComplete, $Infected); }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The main logic of the program.

// / Verify the installation.
list($InstallationVerified, $ConfigLoaded) = verifySCInstallation();
if (!$InstallationVerified or !$ConfigLoaded) die('ERROR!!! ScanCore-1, Cannot verify the ScanCore installation!'.PHP_EOL);

// / Create required directories if they don't already exist.
list($RequiredDirsExist) = createDirs($RequiredDirs);
if (!$InstallationVerified or !$ConfigLoaded) die('ERROR!!! ScanCore-2, Cannot create required directories!'.PHP_EOL);

// / Process supplied command-line arguments.
// / Example:  C:\Path-To-PHP-Binary.exe C:\Path-To-ScanCore.php C:\Path-To-Scan\ -m [integer] -c [integer] -v -d
list($ArgsParsed, $PathToScan, $MemoryLimit, $ChunkSize, $Debug, $Verbose, $Recursion, $ReportFile, $Logfile, $MaxLogSize, $PerformDefUpdate, $PerformAppUpdate) = parseArgs($argv);
if (!$ArgsParsed) processOutput('Cannot verify supplied arguments!', TRUE, 3, TRUE, TRUE, TRUE);
else processOutput('Verified supplied arguments.', FALSE, 0, FALSE, FALSE, FALSE);

// / If scanning operations are required
if (!$PerformDefUpdate && !$PerformAppUpdate) {
  // / Load the virus definitions into memory and calculate it's hash (to avoid detecting our own definitions as an infection).
  list($DefsLoaded, $Defs, $DefData) = load_defs($DefsFile);
  if (!$DefsLoaded) processOutput('Cannot load definitions!', TRUE, 6, TRUE, TRUE, TRUE);
  else processOutput('Loaded definitions.', FALSE, 0, FALSE, FALSE, FALSE);

  // / Start the scanner!
  list($ScanComplete, $DirCount, $FileCount, $Infected) = file_scan($PathToScan, $Defs, $DefsFile, $DefData, $Debug, $Verbose, $MemoryLimit, $ChunkSize, $Recursion);
  if (!$ScanComplete) processOutput('Could not complete requested scan!', TRUE, 7, TRUE, TRUE, TRUE);
  else processOutput('Scanned '.$FileCount.' files in '.$DirCount.' folders and found '.$Infected.' potentially infected items.', FALSE, 0, TRUE, FALSE, TRUE); }

else {
  // / Perform definition update, when required.
  if ($PerformDefUpdate) {
    list($UpdateDefininitionsComplete, $UpdateDefinitionsErrors) = updateDefinitions();
    if (!$UpdateDefininitionsComplete) processOutput('Cannot install definition update!', TRUE, 4, TRUE, TRUE, TRUE); 
    else processOutput('Installed definition update.', FALSE, 0, FALSE, FALSE, FALSE); }

  // / Perform application update, when required.
  if ($PerformAppUpdate) {
    list($UpdateApplicationComplete) = updateApplication();
    if (!$UpdateApplicationComplete) processOutput('Cannot install application update!', TRUE, 5, TRUE, TRUE, TRUE); 
    else processOutput('Installed application update.', FALSE, 0, FALSE, FALSE, TRUE); } }
// / -----------------------------------------------------------------------------------