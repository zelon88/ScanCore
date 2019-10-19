## APPLICATION INFORMATION ...

HR-AV, Copyright on 10/2/2019 by Justin Grimes, www.github.com/zelon88

This file is a heavily modified version of PHP-AV maintained by Justin Grimes.

This file was designed to function as part of the HR-AV anti-virus application.

This scanner was designed for high performance single threaded use. It was intended to be use via manual command-line interface, or run with a custom thread handler which runs this script multiple times on different targets. 

This scanner can detect files based on the following criteria:

1. MD5 Hash
2. SHA1 Hash
3. SHA256 Hash
4. Raw Data Match

The "ScanCore_Virus.def" file is a TSV (tab-separated file) with each line containing an separate infection UID, RAW-DATA, MD5, SHA256, SHA1. In that order. 

If a file is larger than the [memorylimit] argument it will be chopped into [chunsize] and each chunk will be scanned separately. 

-----------------------------------------------------------------------------------

## LICENSE INFORMATION ...

This project is protected by the GNU GPLv3 Open-Source license.

-----------------------------------------------------------------------------------

## DEPENDENCY REQUIREMENTS ... 

This application requires Windows 7 (or later) with PHP 7.0 (or later).
  
-----------------------------------------------------------------------------------

## VALID SWITCHES / ARGUMENTS / USAGE ...

Quick Start Example:

     C:\Path-To-PHP-Binary.exe C:\Path-To-ScanCore.php C:\Path-To-Scan\ -m [integer] -c [integer] -v -d
     C:\PHP\PHP.exe C:\scanCore\scanCore.php C:\Windows\Temp -memorylimit 4000000000 -chunksize 1000000000 -verbose -debug
     C:\PHP\PHP.exe C:\scanCore\scanCore.php C:\Windows\Temp -m 4000000000 -c 1000000000 -v -d

Start by opening a command-prompt.
1. Type the absolute path to a portable PHP 7.0+ binary. Don't press enter just yet.
2. Now type the absolute path to this PHP file as the only argument for the PHP binary.
3. Everything after the path to this script will be passed to this file as an argument.
4. The first Argument Must be a valid absolute path to the file or folder being scanned.
5. Optional arguments can be specified after the scan path. Separate them with spaces.
  
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
