FileSystem Methods
----------------------------------------

## Purpose
The purpose of FileSystem is to provide a unified and secure method of
interacting with file systems. The methods supported at this time
are: FTP, FTPS, SFTP, Local. Descriptions of each of these methods
of file system interaction as well as the operations available
through these connections are detailed below.

## Classes
The classes which are of interest to the programmer are broken down into
two categories; configuration classes and driver classes. While this
document shows basic usage and information of these classes and of
their methods, for complete descriptions and behavior please refer to
the API documentation.

## Configuration Classes
Configuration classes provide configuration information to the driver classes. This
contains information such as: hostname, username, password, ...etc.
There is one configuration class per driver class. Before
constructing a driver a configuration object must be constructed.

    FileSystem_Configuration_Ftp($host = 'localhost', $user = "Anonymous", $pass = '', $dir = '', $port = 21, $timeout = 10, $ssl = false, $pasv = true)
    FileSystem_Configuration_Sftp($host = 'localhost', $user = 'Anonymous', $pass = '', $dir = './', $port = 22, $auth_type = "pass", $pub_key = "", $priv_key = "", $passphrase = "")
    FileSystem_Configuration_Local($dir = '')

## Driver Classes
Driver classes are the classes through which actual interaction with the
file system will occur. The only parameter when constructing a driver
is the configuration object. The local driver should be used only in
cases where Ftp or Sftp drivers can not. The local driver is in
essence a wrapper for php functions for local interaction (such as
fopen, fwrite, ...etc)

    FileSystem_Ftp(FileSystem_Configuration $configuration)
    FileSystem_Sftp(FileSystem_Configuration $configuration)
    FileSystem_Local(FileSystem_Configuration $configuration)


## Methods Available to Driver Classes

The following methods are available to all drivers.

    connect(); (Creates connection, must be called at least once before any other function)
    get($local, $remote); (gets a file on the remote file system and saves it locally, this function should be avoided as it undermines the purpose of this class)
    fget($remote); (same as get but returns a resource handle instead of saving the file locally)
    put($local, $remote); (take a local file and save it on the remote server, should be avoided as it undermines the purpose of this class)
    fput($stream, $remote); (takes a resource handle (stream) and saves it remotely, used in conjunction with fget)
    chmod($perm, $file); (change permission of file, $perm is in *nix format)
    ls($dir = ''); (return an array of the contents of the current working directory or of the directory specified)
    cd($dir = ''); (change the current working directory)
    cp($sourcepath, $destpath); (copy a remote file from one location to another)
    mv($sourcepath, $destpath); (move a remote file from one location to another)
    rm($sourcepath); (remove a remote file from the server)


## Error Handling in FileSystem

All errors which occur in a FileSystem driver are stored in an error object. The examples below show how the error handler is used. The following is a list of all errorHandler functions available in each driver:

    getLast($clear = false)
    count()
    getAll($clear = false)
    clearAll() 

## Examples

### Example 1 – Connect using FTP and get a directory listing

    [php]
    $conf = new FileSystem_Configuration_Ftp('localhost', 'user', 'pass');
    $ftp = new FileSystem_Ftp($conf);
    $ftp->connect();
    //Now is a good time to check for errors before continuing.
    if ($ftp->errorHandler->count() !== 0) {
    //There has been an error
    }
    //get the contents of the current working directory.
    $dir = $ftp->ls();
    //Print out the contents
    print_r($dir);


### Example 2 – Connect using FTP and copy a file from one place to another on the remote file system

    [php]
    $conf = new FileSystem_Configuration_Ftp('localhost', 'user', 'pass');
    $ftp = new FileSystem_Ftp($conf);
    $ftp->connect();
    //Now is a good time to check for errors before continuing.
    if ($ftp->errorHandler->count() !== 0) {
    //There has been an error
    }
    
    if (!$ftp->cp('file1.txt', 'file2.txt')) {
    //returned false, copy failed. We can now check errors.
    $errors = $ftp->errorHandler->getLast();
    }
    //copy ok, file1.txt has been copied to file2.txt


### Example 3 – Connect using FTP and SFTP and move a file from FTP to SFTP server

    [php]
    $conf = new FileSystem_Configuration_Ftp('localhost', 'user', 'pass');
    $ftp = new FileSystem_Ftp($conf);
    $ftp->connect();
    //Now is a good time to check for errors before continuing.
    if ($ftp->errorHandler->count() !== 0) {
    //There has been an error
    }
    $conf = new FileSystem_Configuration_Sftp('localhost', 'user', 'pass');
    $sftp = new FileSystem_Sftp($conf);
    $sftp->connect();
    //Now is a good time to check for errors before continuing.
    if ($sftp->errorHandler->count() !== 0) {
    //There has been an error
    }
    //get our file from ftp.
    if (!($file = $ftp->fget('file1.txt'))) {
    //we did not get the file, maybe it did not exist or we dont have permission, check errors.
    $errors = $ftp->errorHandler->getAll();
    //do something with errors
    return;
    }

    //put our file to sftp.
    if(!($sftp->fput($file, 'file1.txt'))) {
    //file could not be saved, maybe a permission error?
    $errors = $ftp->errorHandler->getAll();
    //do something with errors
    return;
    }
    //File was saved to the sftp, there now exists the file “file1.txt” on both the ftp and sftp file systems.
    //we can now remove the old one from ftp if we wish.
    $ftp->rm('file1.txt');
    //file should have been removed, let us check all logged errors and see if anything went wrong.
    $errors = $ftp->errorHandler->getAll();
    if ($errors !== 0) {
    //errors exist
    }
    return;
