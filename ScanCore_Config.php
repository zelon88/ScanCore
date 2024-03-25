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
// / General Information ...

// /   Allow application updates. Requires git. Will replace ScanCore_Config.php & rename the original.
// /   Valid options are TRUE or FALSE.
// /   Default is TRUE.
$ApplicationUpdates = TRUE;
// /   The URL of a Git repository containing application updates.
// /   Valid options are a URL to a ScanCore source code Git repository, formatted as a string.
// /   Default is 'https://github.com/zelon88/ScanCore'.
$ApplicationUpdateURL = 'https://github.com/zelon88/ScanCore';
// /   The name of the repository containing the application updates to use.
// /   Valid options are the name of the repository, formatted as a string.
// /   Default is 'ScanCore'.
$ApplicationRepositoryName = 'ScanCore';
// /   Allow virus definition updates. Requires git.
// /   Valid options are TRUE or FALSE.
// /   Default is TRUE.
$DefinitionUpdates = TRUE;
// /   The URL of a Git repository containing the definition updates to use.
// /   Valid options are a URL to a ScanCore source code Git repository, formatted as a string.
// /   Default is 'https://github.com/zelon88/ScanCore_Definitions'.
$DefinitionUpdateURL = 'https://github.com/zelon88/ScanCore_Definitions';
// /   The name of the repository containing the definition updates to use.
// /   Valid options are the name of the repository, formatted as a string.
// /   Default is 'ScanCore_Definitions'.
$DefinitionRepositoryName = 'ScanCore_Definitions';
// /   The type of definition updates to subscribe to.
// /   Must be formatted as an array.
// /   Valid options are 'Virus', 'Malware', 'Pup'.
// /   Default is 'Virus', 'Malware', 'PUP'.
$DefinitionsUpdateSubscriptions = array('Virus', 'Malware', 'PUP');
// /   Number of bytes to store in each logfile before splitting to a new one.
// /   Must be formatted as an integer, or an equation that evaluates to an integer.
// /   Default is 1024*32.
$DefaultMaxLogSize = 1024*32;
// /   Enable "debug" mode (more logging).
// /   Valid options are TRUE or FALSE.
// /   Default is FALSE.
$Debug = TRUE;
// /   Enable "verbose" mode (more console).
// /   Valid options are TRUE or FALSE.
// /   Default is FALSE.
$Verbose = TRUE;
// /   The maximum number of bytes of memory to allocate to file scan operations.
// /   Must be formatted as an integer, or an equation that evaluates to an integer.
// /   Default is 1024*512.
$DefaultMemoryLimit = 1024*512;
// /   When scanning large files the file will be scanned this many bytes at a time.
// /   Must be formatted as an integer, or an equation that evaluates to an integer.
// /   Default is 1024*128.
$DefaultChunkSize = 1024*128;
// /   The version of this file, used for internal version integrity checks.
// /   Must be formatted as a string. Must match the version of ScanCore.php file.
$ConfigVersion = 'v1.1';
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / Directory locations ...
// /   The default location to scan if run with no input scan path argument.
$ScanLoc = '';
// /   The absolute path where log files are stored.
$LogsDir = 'Logs';
// /   The absolute path where report files are stored.
$ReportDir = 'Logs';
// /   The filename for the ScanCore report file.
$ReportFileName = 'ScanCore_Report.txt';
// /   The filename for the ScanCore log file.
$LogFileName = 'ScanCore_Log.txt';
// /   The filename for the ScanCore virus definition file.
$DefsFileName = 'ScanCore_Combined_Definitions.def';
// /   The filename for the ScanCore virus definition file.
$InstallDir = realpath(dirname(__FILE__));
// /   The absolute path where virus definitions are found.
$DefsFile = $InstallDir.DIRECTORY_SEPARATOR.$DefsFileName;
// / -----------------------------------------------------------------------------------