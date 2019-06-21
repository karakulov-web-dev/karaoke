<?php
/**
 * @file
 * Demonstrates how to execute an interactive process using proc_open while
 * also grabbing STDERR for your own use.
 *
 * I did not like the other options that redirected output to a file on disk, or
 * used some other more complex way to grab STDERR.
 *
 * I would like to eliminate the loop in favor of a stream capture of some sort,
 * but came up short.
 */
// Prevent defining the function multiple times.
if (!function_exists('passthru_with_errors')) {
  /**
   * Executes an interactive command while capturing STDERR and the return code.
   *
   * @param string $command
   *   The command that will be executed.
   *
   * @param int &$return_var
   *   If the return_var argument is present, the return status of the Unix
   *   command will be placed here.
   *
   * @param array &$stderr_ouput
   *  If the stderr_ouput argument is present, the output sent to STDERR.
   *
   * @return
   *   No value is returned.
   */
  function passthru_with_errors($command, &$return_var = null, array &$stderr_ouput = null) {
    $return_var = null;
    $stderr_ouput = array();
    $descriptorspec = array(
      // Must use php://stdin(out) in order to allow display of command output
      // and the user to interact with the process.
      0 => array('file', 'php://stdin', 'r'),
      1 => array('file', 'php://stdout', 'w'),
      2 => array('pipe', 'w'),
    );
    $pipes = array();
    $process = @proc_open($command, $descriptorspec, $pipes);
    if (is_resource($process)) {
      // Loop on process until it exits normally.
      do {
        $status = proc_get_status($process);
        // If our stderr pipe has data, grab it for use later.
        if (!feof($pipes[2])) {
          // We're acting like passthru would and displaying errors as they come in.
          $error_line = fgets($pipes[2]);
          echo $error_line;
          $stderr_ouput[] = $error_line; 
        }
      } while ($status['running']);
      // According to documentation, the exit code is only valid the first call
      // after a process is finished. We can't rely on the return value of
      // proc_close because proc_get_status will read the exit code first.
      $return_var = $status['exitcode'];
      proc_close($process);
    }
  }
}