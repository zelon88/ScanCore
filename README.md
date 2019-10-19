-----------------------------------------------------------------------------------
APPLICATION INFORMATION ...
  HR-AV, Copyright on 10/2/2019 by Justin Grimes, www.github.com/zelon88 
  This file is a heavily modified version of PHP-AV maintained by Justin Grimes.
  This file was designed to function as part of the HR-AV anti-virus application.
  This file may not work properly outside of it's intended environment or use-case.
  This file should be used outside it's intended application only during development.
  Serious data loss or filesystem damage may result! Execute this file at your own risk!

LICENSE INFORMATION ...
  This project is protected by the GNU GPLv3 Open-Source license.

DEPENDENCY REQUIREMENTS ... 
  This application requires Windows 7 (or later) with PHP 7.0 (or later).
// /
VALID SWITCHES / ARGUMENTS / USAGE ...
  Quick Start Example:
   C:\Path-To-PHP-Binary.exe C:\Path-To-ScanCore.php C:\Path-To-Scan\ -m [integer] -c [integer] -v -d

  Start by opening a command-prompt.
  Type the absolute path to a portable PHP 7.0+ binary. Don't press enter just yet.
  Now type the absolute path to this PHP file as the only argument for the PHP binary.
  Everything after the path to this script will be passed to this file as an argument.
  The first Argument Must be a valid absolute path to the file or folder being scanned.
  Optional arguments can be specified after the scan path. Separate them with spaces.
  
  Optional Arguments Include:
    Specify memory limit (in bytes):        -memorylimit ####
                                            -m ####

    Specify chunk size (in bytes);          -chunksize ####
                                            -c ####

    Enable "debug" mode (more logging):     -debug
                                            -d

    Enable "verbose" mode (more console):   -verbose
                                            -v             

-----------------------------------------------------------------------------------
